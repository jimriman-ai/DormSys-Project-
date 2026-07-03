# spec11 Architecture Boundary Sketch (Planning)

**Task**: P-023  
**Spec**: spec11 — Reporting & Audit Consumption Evolution  
**Status**: **Planning-only** — no implementation authority  
**Baseline**: `architecture-clarification.md` §3.6, §7.3; P-021 contracts; `data-model.md`; P2 authorization decision (2026-07-03)

---

## 1. PURPOSE

Define the **planning-level architecture boundary map** for spec11 Reporting consumption over frozen spec10 audit history. This sketch records ownership, allowed dependency directions, forbidden zones, and future boundary verification notes — without runtime orchestration, module scaffolding, or implementation maps.

---

## 2. BOUNDED OWNERSHIP

### 2.1 spec10 owns (frozen)

| Asset | Responsibility |
| ----- | -------------- |
| `audit_logs` persistence | Append-only system of record (AP-06) |
| `AuditRecordingContract` | Sole audit write ingress |
| `AuditHistoryReadContract` | Authorized read port and `AuditHistoryQuery` |
| `AuditHistoryItem` DTOs | Line-item audit read shape |
| `audit.read` permission gate | Role enforcement at Audit Application |
| Retention and soft-archive jobs | Unchanged by spec11 |
| Audit Infrastructure layer | Models, repositories — **not** visible to Reporting |

**Lifecycle**: **CLOSED / FROZEN** — no spec11 planning artifact may mutate spec10.

### 2.2 Reporting / spec11 owns (future — planning only today)

| Asset | Responsibility |
| ----- | -------------- |
| T1 projection stores | `CorrelationProjectionEntry`, `AuditWindowAggregate`, caches, summaries, cursors |
| Reporting read contract surface | Consumer-facing read use-cases RU-01–RU-06 |
| Internal projection ports | PP-01–PP-09 (`projection_ports.md`) |
| T2 read model assembly | Ephemeral DTOs with provenance |
| Visibility policy enforcement | DL-03 `includeArchived` gate before T0/T1 |
| Export manifest metadata | `ComplianceExportSnapshot` planning shape |

**Constraint**: Reporting owns **derived** projection data only — not audit authority (CD-017).

### 2.3 Outside both boundaries

| Asset | Owner |
| ----- | ----- |
| Domain modules (Request, Allocation, Lottery, etc.) | Respective bounded contexts |
| Presentation (Explorer UI, dashboards) | Presentation layer — consumes Reporting read contract only |
| Identity roles and permissions (except `audit.read` usage) | Identity module |
| M4 audit producers (deferred) | Future per-context programs |
| E-04 compliance KPI metrics | Deferred per P2-C-03 |
| Analytics platform / BI tooling | Out of P2 scope |

---

## 3. SOURCE-TO-PROJECTION FLOW

### 3.1 Conceptual read flow

```text
┌──────────────────────────────────────────────────────────────────┐
│ Presentation / Export (future)                                  │
└────────────────────────────┬─────────────────────────────────────┘
                             │ Reporting Read Contract (T2 assembly)
┌────────────────────────────▼─────────────────────────────────────┐
│ Reporting Application                                           │
│  ┌─────────────────────┐    ┌─────────────────────────────────┐ │
│  │ T1 Query Ports      │    │ T0 Source Read Port (PP-01)     │ │
│  │ PP-03..PP-08        │    │ → AuditHistoryReadContract      │ │
│  └──────────┬──────────┘    └───────────────┬─────────────────┘ │
└─────────────┼────────────────────────────────┼───────────────────┘
              │                                │
┌─────────────▼──────────────┐    ┌────────────▼───────────────────┐
│ T1 Projection Store        │    │ spec10 Audit Application      │
│ (Reporting Infrastructure) │    │ (FROZEN)                       │
└─────────────▲──────────────┘    └────────────────────────────────┘
              │
┌─────────────┴──────────────┐
│ Projection Refresh Input   │
│ (PP-02) — reads T0 pages   │
└────────────────────────────┘
```

### 3.2 Tier responsibilities in flow

| Stage | Tier | Action |
| ----- | ---- | ------ |
| Ingest | T0 → T1 | Paginated contract read feeds projection refresh (planning) |
| Query hit | T1 | Correlation, aggregates, summaries served with provenance |
| Query miss / evidence | T0 | Entity timeline, drill-down, export line items |
| Response | T2 | Assemble read model + provenance envelope for consumer |

No runtime scheduler, job, or queue detail is defined in this sketch.

---

## 4. FORBIDDEN MUTATION ZONES

| Zone | Rule |
| ---- | ---- |
| `audit_logs` table | **No** Reporting INSERT/UPDATE/DELETE (AP-06) |
| spec10 Audit module code | **No** modification by spec11 program |
| `AuditHistoryReadContract` / `AuditHistoryQuery` | **No** extension for reporting convenience (P2-C-07) |
| Audit retention / archive jobs | **No** Reporting changes |
| Audit producer rollout | **No** spec11 authorization |
| Upstream domain lifecycle | **No** Reporting write authority to any context (CD-017) |
| T0 as projection write target | **No** — only T1 stores receive derived writes (future implementation) |

**spec10 immutability**: All zones above remain untouched for spec11 P2 and until separate governance changes spec10.

---

## 5. ALLOWED DEPENDENCY DIRECTIONS

### 5.1 Layer dependency matrix

| From | To | Allowed? | Mechanism |
| ---- | -- | -------- | --------- |
| Presentation | Reporting read contract | **Yes** | Read DTOs only |
| Reporting Application | `AuditHistoryReadContract` (Application) | **Yes** | PP-01 adapter only |
| Reporting Application | Reporting Infrastructure (T1) | **Yes** | Internal module boundary |
| Reporting Infrastructure | `AuditHistoryReadContract` | **Yes** | Via Application adapter — not direct Infrastructure import |
| Reporting (any) | Audit Infrastructure | **No** | Forbidden |
| Domain modules | `audit_logs` / Audit Infrastructure | **No** | Must use Reporting read layer (R11) |
| Domain modules | Reporting read contract | **Yes** (future) | Read-only consumption |
| Reporting | Upstream domain write APIs | **No** | Read-only consumer |

### 5.2 Source contract usage rules

| Rule | Detail |
| ---- | ------ |
| **AD-01** | Single audit read port: `AuditHistoryReadContract` |
| **AD-02** | Adapter lives in Reporting Application — not Presentation |
| **AD-03** | Pagination and filters must match frozen query capabilities |
| **AD-04** | `includeArchived` set only after Reporting visibility gate |
| **AD-05** | Correlation lookup uses T1 — not T0 `correlationId` filter |

### 5.3 Reporting internal boundaries

| Boundary | Separation |
| -------- | ------------ |
| Read contract vs ports | Contract composes ports; ports do not leak to Presentation |
| T0 adapter vs T1 query ports | Separate concerns; shared provenance rules |
| Refresh input vs query ports | Refresh writes T1; query ports read T1 — no consumer exposure of refresh |
| Cursor control vs query | PP-09 internal only |

---

## 6. FORBIDDEN DEPENDENCIES

| ID | Forbidden dependency | Verdict |
| -- | -------------------- | ------- |
| **FD-01** | `App\Modules\Audit\Infrastructure\*` imported by Reporting | **Forbidden** |
| **FD-02** | `AuditLogModel` or audit repository in Reporting | **Forbidden** |
| **FD-03** | Direct SQL on `audit_logs` from Reporting or domain modules | **Forbidden** |
| **FD-04** | Presentation calling `AuditHistoryReadContract` directly for cross-context reporting | **Forbidden** — use Reporting read contract |
| **FD-05** | Reporting logic embedded in Audit module | **Forbidden** |
| **FD-06** | T1 projection treated as dispute authority over T0 | **Forbidden** |
| **FD-07** | Merging non-audit upstream facts into audit projection tables | **Forbidden** (R-DM-16) |
| **FD-08** | Cross-module Eloquent joins on `audit_logs` | **Forbidden** |

**Ownership collapse**: Reporting must not become a second audit writer or audit store owner.

---

## 7. BOUNDARY TEST NOTES

Planning-level verification expectations for future implementation (mirror `architecture-clarification.md` §7.3):

| Test intent | Planning criterion |
| ----------- | ------------------- |
| **BT-01** | Reporting module does not import `App\Modules\Audit\Infrastructure\*` |
| **BT-02** | Reporting may import `App\Modules\Audit\Application\Contracts\*` only at T0 adapter |
| **BT-03** | No `UPDATE`/`DELETE` against `audit_logs` from Reporting code paths |
| **BT-04** | Domain modules do not reference `audit_logs` or Audit Infrastructure |
| **BT-05** | Presentation depends on Reporting read contract — not Audit contracts |
| **BT-06** | Export and compliance paths resolve line items via T0 |
| **BT-07** | Provenance fields present on all T1/mixed responses |

**Note**: Test code, PHPUnit classes, and CI wiring are **out of scope** for P-023. Detailed test plan deferred to implementation authorization.

---

## 8. NON-GOALS

This boundary sketch does **NOT** define:

| Exclusion |
| --------- |
| Framework or DI container patterns |
| `app/Modules/Reporting/` directory layout |
| Runtime process definitions (workers, schedulers) |
| Network or API topology |
| Database schema or migration files |
| Governance artifacts or new approval chains |
| Implementation Authorization |

---

**End of P-023 boundary sketch. Planning boundaries only. spec10 unchanged.**
