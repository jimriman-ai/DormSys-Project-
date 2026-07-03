# spec11 Architecture Clarification

**Session**: `/speckit-clarify` — architecture definition layer  
**Recorded**: 2026-07-02  
**Status**: **CLARIFIED** — planning-only; **no execution authority**  
**Predecessor**: spec10 — CLOSED / FROZEN — [`spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md)  
**Governing decisions**: **CD-017**, **R11** (inherited **R10**, **AP-06**)

---

## 1. SPEC11_ARCHITECTURE_CLARIFICATION

### Purpose

Define the precise **consumption-layer architecture** for spec11 — Reporting & Read-Model Evolution — as a planning baseline on top of the frozen spec10 audit system of record. This document answers how DormSys **reads**, **shapes**, **projects**, and **presents** immutable audit history without altering spec10 behavior, schema, producers, or authorization.

### Architectural position

```text
┌─────────────────────────────────────────────────────────────────┐
│  Presentation (future)                                          │
│  Operator Explorer · Compliance dashboards · Export UIs         │
│  Persian RTL · Livewire — NOT authorized by this clarification  │
└────────────────────────────┬────────────────────────────────────┘
                             │ read-only DTOs / queries
┌────────────────────────────▼────────────────────────────────────┐
│  Reporting Module (spec11 — future bounded context)             │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Application: Reporting read ports, query abstraction,    │   │
│  │              consumption taxonomy (compliance / ops /    │   │
│  │              security), analytics boundary enforcement   │   │
│  └──────────────────────────┬───────────────────────────────┘   │
│  ┌──────────────────────────▼───────────────────────────────┐   │
│  │ Infrastructure (optional tier): Reporting-owned            │   │
│  │ materialized projections — derived, non-authoritative      │   │
│  └──────────────────────────┬───────────────────────────────┘   │
└────────────────────────────┼────────────────────────────────────┘
                             │ AuditHistoryReadContract ONLY
                             │ (frozen spec10 Application port)
┌────────────────────────────▼────────────────────────────────────┐
│  Audit Module (spec10 — FROZEN)                                 │
│  append-only audit_logs · audit.read gate · soft-archive        │
└─────────────────────────────────────────────────────────────────┘
```

### Resolved planning decisions

| ID | Decision | Resolution | Rationale |
| -- | -------- | ---------- | --------- |
| **DL-01** | Projection storage | **Hybrid (C)** — computed path + materialized tier | Investigative reads stay contract-direct; aggregates/dashboards use Reporting-owned projections |
| **DL-02** | First consumer surface | **Layered (B → A)** — read API/export façade first; explorer UI as presentation wave | Reduces coupling risk; preserves OA-10-05 separation; UI consumes Reporting ports only |
| **DL-03** | Archived visibility | **Role-gated `includeArchived` (B)** | Mirrors spec10 default (exclude archived); compliance roles may opt in without retention change |

### Core invariants (non-negotiable)

1. **Single audit ingress** remains spec10 `AuditRecordingContract` — Reporting never writes audit facts.
2. **Single authorized audit read port** at baseline remains frozen `AuditHistoryReadContract` — Reporting must not import Audit Infrastructure.
3. **Reporting-owned projections** are derived caches — not a second system of record; refresh never mutates `audit_logs`.
4. **Cross-context reads** for reporting purposes flow through Reporting (CD-017) — domain modules must not query `audit_logs` directly.
5. **Permission baseline** inherits `audit.read` (`Administrator`, `DormMgr`, `HRMgr`); future reporting permissions extend visibility taxonomy only — never bypass append-only rules.

### Clarification scope boundary

This session defines **architecture only**. It does not authorize implementation, schema, waves, checkpoints, or spec10 extension.

---

## 2. READ_MODEL_CONCEPTUAL_DESIGN

Reporting exposes **read shapes** — conceptual views over audit history — composed from frozen contract results and/or Reporting-owned projections. Read shapes are **not** new domain entities; they are presentation-oriented aggregates of immutable audit items.

### 2.1 Entity-centric view

**Purpose**: Operator and manager investigation — “what happened to this subject?”

| Aspect | Definition |
| ------ | ---------- |
| **Primary key** | `(entityType, entityId)` |
| **Ordering** | `occurredAt` descending (UTC storage; Jalali at presentation) |
| **Source tier** | **Tier 0 (computed)** — direct `AuditHistoryReadContract` with entity filters |
| **Typical filters** | `entityType`, `entityId`, optional `eventTypes`, `occurredFrom`/`occurredTo` |
| **Archive policy** | Default exclude archived; compliance role may set `includeArchived=true` |
| **Pagination** | Inherits spec10 contract pagination (max 200 per page) |
| **Use cases** | Operational reporting, entity timeline in explorer, exception drill-down |

**Conceptual DTO**: `EntityAuditTimelineReadModel` — ordered list of `AuditHistoryItem` projections plus summary metadata (count, first/last occurrence, event-type histogram optional at Tier 1).

### 2.2 Correlation-based view

**Purpose**: Security and cross-entity investigation — “what actions share this correlation?”

| Aspect | Definition |
| ------ | ---------- |
| **Primary key** | `correlationId` |
| **Constraint** | Frozen `AuditHistoryQuery` has **no** `correlationId` filter — correlation views are **Reporting-owned** |
| **Source tier** | **Tier 1 (materialized projection)** — Reporting indexes `correlationId` during projection refresh from contract-sourced audit items |
| **Fallback (low volume)** | Multi-query composition at Application layer — acceptable only for planning MVP threshold; not scaling path |
| **Use cases** | Security/audit reporting, incident reconstruction, compliance evidence bundles |

**Conceptual DTO**: `CorrelationAuditBundleReadModel` — grouped items by `correlationId`, cross-entity participant list, span (`min(occurredAt)`, `max(occurredAt)`).

**Architectural rule**: Correlation indexing lives in **Reporting Infrastructure projections** — never as a spec10 contract or schema change in this planning phase.

### 2.3 Time-window view

**Purpose**: Period-based operational and compliance reporting — “what happened in this interval?”

| Aspect | Definition |
| ------ | ---------- |
| **Primary key** | `(windowStart, windowEnd, granularity)` |
| **Granularity** | `hour`, `day`, `week`, `month` (planning vocabulary; Jalali calendar at presentation) |
| **Dimensions** | `eventType`, `sourceContext`, `actorType`, optional `entityType` |
| **Source tier** | **Tier 1 (materialized)** for aggregates; **Tier 0** for raw drill-down via contract |
| **Archive policy** | Active-window reports default exclude archived; compliance windows may include archived via role gate |
| **Use cases** | Operational dashboards, compliance period reports, trend inputs for analytics tier |

**Conceptual DTO**: `AuditWindowSummaryReadModel` — bucketed counts, distinct entity/actor counts, top-N event types; drill-down links resolve to entity-centric Tier 0 queries.

### Read-model ownership matrix

| Read shape | Tier 0 (computed via contract) | Tier 1 (Reporting projection) | Authoritative source |
| ---------- | ------------------------------ | ----------------------------- | -------------------- |
| Entity-centric timeline | ✅ Primary | Optional cache | `audit_logs` via contract |
| Correlation bundle | ❌ Not primary | ✅ Required at scale | Derived from contract-fed items |
| Time-window summary | Partial (raw only) | ✅ Primary for aggregates | Derived from contract-fed items |

---

## 3. PROJECTION_BOUNDARY_MODEL

### 3.1 Tier definitions

| Tier | Name | Mechanism (conceptual) | Owner | Mutability |
| ---- | ---- | ---------------------- | ----- | ---------- |
| **T0** | Contract-direct | `AuditHistoryReadContract::query()` | Audit Application (frozen) | Read-only |
| **T1** | Materialized projection | Reporting-owned tables/views refreshed on schedule or incremental cursor | Reporting Infrastructure | Derived write (projection store only) |
| **T2** | Ephemeral computed | In-memory/query-time assembly over T0/T1 for single request | Reporting Application | None persisted |

**Decision DL-01**: Hybrid — **T0** for investigative fidelity; **T1** for read-heavy aggregates and correlation indexing; **T2** for response shaping only.

### 3.2 Materialized vs computed (conceptual)

| Pattern | When | Boundary |
| ------- | ---- | -------- |
| **Computed (T0/T2)** | Low-volume entity/actor drill-down; ad-hoc filters; MVP investigative paths | Must call contract; no Audit Infrastructure import |
| **Materialized (T1)** | Time-window aggregates, correlation index, compliance snapshots, dashboard cards | Reporting schema only; labeled `projection_version` / `refreshed_at` |
| **Forbidden** | Reporting writing to `audit_logs`; domain modules materializing audit copies | Violates AP-06 / CD-017 |

### 3.3 Projection refresh strategy (conceptual — not implementation)

| Mode | Description | Planning preference |
| ---- | ----------- | ------------------- |
| **Incremental cursor** | Track `last_occurred_at` / `last_audit_log_id` per projection | Preferred for steady-state volume |
| **Window snapshot** | Rebuild bounded time partitions (e.g., monthly compliance slices) | Preferred for compliance exports |
| **Full rebuild** | Cold rebuild from paginated contract reads | Disaster/recovery only; governance-gated |

Refresh triggers (future): scheduled job, manual operator refresh (role-gated), post-archive-boundary reconciliation — **all Reporting-owned**; no spec10 job changes.

### 3.4 Caching boundaries (non-implementation)

| Layer | Cache allowed? | Scope | Invalidation concept |
| ----- | -------------- | ----- | -------------------- |
| Audit module | **No** (frozen) | — | spec10 does not add reporting cache |
| Reporting Application | **Yes** — response/request scoped | Single query results, short TTL | TTL + projection `refreshed_at` |
| Reporting Infrastructure (T1) | **Yes** — projection store | Aggregate/correlation indexes | Refresh cycle |
| Presentation | **Yes** — UI state | Client/session | Navigation event |
| CDN / HTTP edge | **Out of scope** | — | Future governance |

**Rule**: Cache keys must never serve stale data across archive visibility boundaries — `includeArchived` flag is part of cache key semantics.

### 3.5 Query abstraction layer design

Reporting Application exposes **Reporting Read Ports** — stable consumption API for Presentation and export jobs:

```text
ReportingReadPort (conceptual families)
├── EntityAuditTimelinePort      → T0 primary; optional T1 cache
├── CorrelationAuditBundlePort   → T1 primary
├── AuditWindowSummaryPort       → T1 primary; T0 drill-down
├── ComplianceExportPort         → T1 snapshot + T0 line items; role-gated archive
└── SecurityAuditEventPort       → T1 + T0; actor/event-type emphasis
```

**Abstraction rules**:

1. Ports return **read DTOs** — never Eloquent models from Audit Infrastructure.
2. Ports enforce **visibility policy** (§5) before delegating to T0/T1.
3. Ports **compose** contract calls — they do not extend `AuditHistoryReadContract` in spec10.
4. Cross-context reporting (non-audit facts) uses **separate upstream read contracts** per context map — Reporting remains aggregator, not writer.

### 3.6 Forbidden boundaries

| Action | Verdict |
| ------ | ------- |
| Reporting UPDATE/DELETE on `audit_logs` | **Forbidden** (AP-06) |
| Reporting import of `AuditLogModel` / repository | **Forbidden** (R10) |
| Domain module direct SQL on `audit_logs` | **Forbidden** (R11) |
| Projection treated as authoritative for audit disputes | **Forbidden** — T0 contract wins |
| spec10 contract change for reporting convenience | **Forbidden** in this phase |

---

## 4. REPORTING_CONSUMER_FRAME

Reporting consumption is categorized by **intent** — not by UI surface. All categories are read-only.

### 4.1 Compliance reporting

| Aspect | Definition |
| ------ | ---------- |
| **Audience** | Compliance stakeholders, `Administrator` with extended archive visibility |
| **Primary read shapes** | Time-window summaries, correlation bundles, export packages |
| **Archive policy** | `includeArchived=true` permitted for authorized compliance role (extends DL-03) |
| **Output** | Immutable export artifacts (PDF/CSV concept) with `generated_at`, filter manifest, projection version |
| **Latency** | Batch/snapshot acceptable |
| **Audit trail of reporting** | Future: Reporting actions on exports may themselves be audit-recorded by producers — not spec10 change |

### 4.2 Operational reporting

| Aspect | Definition |
| ------ | ---------- |
| **Audience** | `DormMgr`, `HRMgr`, `Administrator` |
| **Primary read shapes** | Entity-centric timelines, recent window summaries (7/30 day), actor activity lists |
| **Archive policy** | Default **exclude archived** — aligned with spec10 |
| **Output** | Interactive lists/dashboards; near-current operational truth |
| **Latency** | Low — T0 acceptable for drill-down; T1 for dashboard cards |
| **Scope** | Dormitory operations, allocation/voucher audit visibility as producers come online (M4) |

### 4.3 Security / audit reporting

| Aspect | Definition |
| ------ | ---------- |
| **Audience** | `Administrator` (primary); optional future `SecurityAuditor` role (governance decision) |
| **Primary read shapes** | Correlation bundles, actor-centric anomaly views, event-type concentration |
| **Archive policy** | Role-gated; security investigations may require archived rows |
| **Output** | Investigation workspace + export; emphasizes `correlationId`, `actorId`, `eventType` |
| **Latency** | T1 for search breadth; T0 for line-item verification |
| **Note** | Distinct from spec10 **Audit module** — this is Reporting consumption taxonomy |

### Consumption flow (conceptual)

```text
Consumer intent (compliance | operational | security)
        ↓
Reporting visibility policy (role + includeArchived)
        ↓
Reporting Read Port selection
        ↓
T1 projection hit ──miss──→ T0 AuditHistoryReadContract
        ↓
Read DTO assembly → Presentation / Export
```

---

## 5. ANALYTICS_SEPARATION_MODEL

### 5.1 Distinction: reporting vs analytics

| Dimension | **Reporting** | **Analytics** (spec11 planning tier) |
| --------- | ------------- | ------------------------------------ |
| **Purpose** | Answer governed operational/compliance questions with traceable filters | Identify longer-horizon trends, patterns, and aggregates |
| **Time horizon** | Near-current to defined compliance windows | Multi-month / multi-year (within retention) |
| **Grain** | Event-level drill-down available | Coarse buckets; may sacrifice line-item immediacy |
| **Authority** | Exportable evidence packages tie back to T0 line items | Projections labeled analytical — not evidentiary alone |
| **Refresh** | Snapshot + incremental | Scheduled batch; tolerant of lag |
| **Governance** | Role-gated per consumption frame | Separate authorization wave (E-08) |
| **Storage** | T0 + T1 as defined | **T1 analytics projections** only — still Reporting-owned |

**Rule**: Analytics modules/functions live under Reporting **read** boundary — they do not become a write path to any domain context.

### 5.2 Aggregation vs reporting

| Term | Meaning in spec11 |
| ---- | ----------------- |
| **Reporting** | Governed, filter-declared, role-scoped consumption with drill-down to immutable audit items |
| **Aggregation** | Mathematical summary (counts, rates, histograms) over audit dimensions — input to reporting dashboards and analytics |
| **Boundary** | Aggregations materialize in T1; reporting ports **present** aggregations with provenance metadata (`source_tier`, `refreshed_at`, `filter_hash`) |

### 5.3 Long-term trend analysis model (conceptual)

| Element | Definition |
| ------- | ---------- |
| **Inputs** | T1 window summaries partitioned by Jalali month/quarter |
| **Dimensions** | `eventType`, `sourceContext`, producer maturity (M1 vs M4) |
| **Archive handling** | Trend series may include archived partitions only when compliance role authorizes |
| **Producer gap** | Analytics must tolerate incomplete coverage until M4 producers onboard — document coverage metadata |
| **Presentation** | Future analytics dashboards — not authorized here |

### 5.4 Operator visibility model boundaries

| Surface | Layer | Consumes | Does NOT |
| ------- | ----- | -------- | -------- |
| **Audit Explorer** (conceptual) | Presentation | Reporting Read Ports | Call Audit Infrastructure; embed query SQL |
| **Compliance dashboard** | Presentation | `AuditWindowSummaryPort`, `ComplianceExportPort` | Mutate retention or archive |
| **Security investigation** | Presentation | `CorrelationAuditBundlePort`, `SecurityAuditEventPort` | Bypass `audit.read` / future gates |
| **Raw history API** (spec10) | Audit Application | `AuditHistoryReadContract` | Replace Reporting abstractions for cross-context use |

**DL-02 resolution**: First authorized implementation wave targets **Reporting read API / export façade (B)**; **Audit Explorer UI (A)** follows as Presentation wave consuming the same ports.

---

## 6. NON_SCOPE (strict)

The following remain **explicitly excluded** after architecture clarification:

| # | Exclusion |
| - | --------- |
| 1 | Any code, module scaffolding, migration, or job implementation |
| 2 | Any change to `app/Modules/Audit/`, `audit_logs` schema, or spec10 artifacts |
| 3 | Any change to audit producers, bridge, retention, or archive jobs |
| 4 | Any extension to `AuditHistoryReadContract` or `AuditHistoryQuery` in spec10 |
| 5 | Wave definitions, task execution, checkpoints, PHPStan/Pint gates |
| 6 | Implementation Authorization, Design Approval, or nomination records |
| 7 | Livewire/Blade/UI assets |
| 8 | SIEM, webhook, or real-time streaming integration |
| 9 | Hard-delete retention or purge policy |
| 10 | Cross-module Eloquent queries on `audit_logs` |
| 11 | Reporting write authority to any upstream domain |
| 12 | Assumption that M4 producers are already emitting audit events |
| 13 | Performance benchmarks or volume SLO commitments |
| 14 | `data-model.md`, `contracts/` technical artifacts (deferred to P2) |
| 15 | New Spatie permissions implementation (planning references only) |

**Clarification does not imply** any item above moves to in-scope without a separate governance authorization record.

---

## 7. FUTURE_WAVE_PREPARATION_NOTES

*Preparation notes only — not wave design, not execution schedule.*

### 7.1 Suggested authorization sequence (governance hypothesis)

| Order | Program | Depends on | Notes |
| ----- | ------- | ---------- | ----- |
| 1 | spec11 Design Approval | This clarification | Baseline for `data-model.md` + contracts |
| 2 | Wave A — Reporting core read ports + T0 façade | Design Approval | No T1 schema required for MVP |
| 3 | Wave B — T1 projection store + refresh | Wave A | Correlation + window summaries |
| 4 | Wave C — Export / compliance packages | Wave B | Role-gated `includeArchived` |
| 5 | Wave D — Operator Explorer UI | Wave A minimum | Presentation only |
| 6 | Wave E — Analytics dashboards | Wave B | Separate E-08 authorization |

### 7.2 P2 technical artifacts (next planning phase — not authorized)

| Artifact | Content hint |
| -------- | ------------ |
| `data-model.md` | Projection entities: `AuditCorrelationIndex`, `AuditWindowAggregate`, `ProjectionCursor` |
| `contracts/` | `EntityAuditTimelinePort`, `CorrelationAuditBundlePort`, `AuditWindowSummaryPort` |
| `research.md` | Incremental refresh vs PostgreSQL materialized view tradeoffs |

### 7.3 Architecture tests (future)

Mirror spec10 `AuditBoundaryTest` / spec07 pattern:

- Reporting must not import `App\Modules\Audit\Infrastructure\*`
- Reporting may import `App\Modules\Audit\Application\Contracts\*` only at T0 adapter
- No `UPDATE`/`DELETE` against `audit_logs` in Reporting module

### 7.4 Open planning items (unchanged by clarification)

| ID | Item | Status |
| -- | ---- | ------ |
| P-013 | Compliance stakeholder interview questions for E-04 KPIs | **OPEN** — requires stakeholder input |
| UD-11-01 | Future `reporting.read` vs reuse `audit.read` permission split | **OPEN** — default: extend `audit.read` first; split only if governance requires |
| UD-11-02 | `SecurityAuditor` role introduction | **OPEN** — governance decision at authorization |

### 7.5 spec10 preservation checkpoint

Before any future implementation wave:

- [ ] spec10 `lifecycle_state: CLOSED` unchanged
- [ ] No spec10 task reopened
- [ ] Consumption path verified against `AuditHistoryReadContract` only
- [ ] CD-017 / R11 architecture test plan included in Design Approval

---

**End of architecture clarification. Planning-only. No execution authorized.**
