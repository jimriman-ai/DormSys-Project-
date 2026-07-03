# spec11 Implementation Authorization Request

---

## 1. REQUEST_HEADER

| Field | Value |
| ----- | ----- |
| **Spec identifier** | spec11 — Reporting & Audit Consumption Evolution (`reporting-projections`) |
| **Feature branch** | `011-reporting-projections` |
| **Request type** | **IMPLEMENTATION_AUTHORIZATION_REQUEST** |
| **Request date** | 2026-07-03 |
| **Authority** | Product / Tech governance (review required) |
| **Request status** | **REQUESTED — NOT APPROVED** |
| **Predecessor** | spec10 — **CLOSED / FROZEN** (immutable) |

---

## 2. CURRENT_STATUS

| Field | Value |
| ----- | ----- |
| **spec11 lifecycle** | **PLANNING-ONLY** |
| **Design state** | **DESIGN_APPROVED_WITH_CONDITIONS** |
| **P2 technical planning** | **COMPLETE** |
| **Implementation** | **NOT authorized** |
| **Execution** | **NOT authorized** |
| **Rollout / deployment** | **NOT authorized** |

Submission of this request does not change authorization posture. No implementation or execution may begin until a separate **Implementation Authorization Decision** is issued.

---

## 3. REFERENCE_BASIS

| Artifact | Role |
| -------- | ---- |
| **spec11 Design Approval Decision Record** (2026-07-03) | Design baseline — `DESIGN_APPROVED_WITH_CONDITIONS`; DL-01–DL-03 binding |
| [`spec11-p2-technical-planning-authorization-decision.md`](./spec11-p2-technical-planning-authorization-decision.md) | P2 authorization — P-020–P-024; conditions P2-C-01–P2-C-08 |
| [`p2-completion-record.md`](./p2-completion-record.md) | P2 scope exhausted; planning artifacts complete |
| [`architecture-clarification.md`](./architecture-clarification.md) | Consumption architecture; boundary rules |
| [`decision-log.md`](./decision-log.md) | DL-01 Hybrid T0/T1/T2; DL-02 API/export before explorer UI; DL-03 archive visibility |
| [`spec.md`](./spec.md), [`plan.md`](./plan.md) | Charter, constraints, evolution tracks |

---

## 4. PLANNING_ARTIFACTS_COMPLETE

Authorized P2 planning deliverables are **complete** and form the implementation baseline:

| Artifact | Path | Status |
| -------- | ---- | ------ |
| Projection data model | [`p2/data-model.md`](./p2/data-model.md) | **COMPLETE** |
| Read contracts | [`p2/contracts/`](./p2/contracts/) — `audit_read_contract.md`, `reporting_read_contract.md`, `projection_ports.md` | **COMPLETE** |
| Refresh research | [`p2/research.md`](./p2/research.md) | **COMPLETE** |
| Boundary sketch | [`p2/boundary-sketch.md`](./p2/boundary-sketch.md) | **COMPLETE** |
| Dimension catalog | [`p2/dimension-catalog.md`](./p2/dimension-catalog.md) | **COMPLETE** |

---

## 5. REQUESTED_IMPLEMENTATION_SCOPE

Implementation authorization is requested for spec11 **Reporting module** work that realizes the P2 planning baseline — read-only audit consumption and Reporting-owned projections per CD-017.

### 5.1 Module and code paths

| Area | Requested work |
| ---- | -------------- |
| **Reporting bounded context** | `app/Modules/Reporting/` — Domain (minimal), Application, Infrastructure, Presentation (read API / export entry points only per DL-02) |
| **Migrations** | `database/migrations/modules/reporting/` — Reporting-owned T1 projection tables only |
| **Tests** | `tests/Unit/Modules/Reporting/`, `tests/Feature/Modules/Reporting/`, `tests/Architecture/ReportingBoundaryTest.php` |

### 5.2 T0 source read layer

| Item | Basis |
| ---- | ----- |
| `AuditHistorySourceReadPort` adapter (PP-01) | Consumes frozen `AuditHistoryReadContract` only — no Audit Infrastructure imports |
| Entity timeline and drill-down reads (RU-01, RU-06) | T0-authoritative investigative paths |
| Compliance export line-item resolution | T0-resolved evidence per `reporting_read_contract.md` EX-01 |

### 5.3 T1 projection layer

| Item | Basis |
| ---- | ----- |
| T1 stores per `data-model.md` | `CorrelationProjectionEntry`, `AuditWindowAggregate`, `ActorActivitySummary`, `ProjectionCursor`; optional `EntityTimelineCacheEntry`, `ComplianceExportSnapshot` metadata |
| Internal ports PP-02–PP-09 | Projection refresh input, correlation/window/actor/export/drill-down/cursor query ports |
| Incremental refresh (primary) | Per `research.md` — cursor-driven ingest from paginated T0 reads |
| Window snapshot refresh (compliance) | Bounded time partitions for aggregates and export manifests |
| Archive visibility tiering | DL-03 logical `archiveVisibilityTier`; separate cursors per tier when materialized |
| Correlation indexing | Reporting T1 only — no `correlationId` on frozen `AuditHistoryQuery` (P2-C-07) |

### 5.4 T2 read contract surface

| Use-case | Read model | Tier |
| -------- | ---------- | ---- |
| **RU-01** | `EntityAuditTimelineReadModel` | T0 (+ optional T1 cache) |
| **RU-02** | `CorrelationAuditBundleReadModel` | T1 |
| **RU-03** | `AuditWindowSummaryReadModel` | T1 |
| **RU-04** | `ComplianceExportReadModel` | Mixed T1 manifest + T0 line items |
| **RU-05** | `SecurityAuditEventReadModel` | T1 + T0 verification |
| **RU-06** | Aggregate drill-down | T1 → T0 via `AggregateDrillDownPort` |

All responses include provenance envelope (`sourceTier`, `refreshedAt`, `projectionVersion`, `filterHash` where applicable).

### 5.5 Cross-cutting implementation requirements

| Requirement | Basis |
| ----------- | ----- |
| `audit.read` permission gate before reads | P2-C-01; extend `audit.read` — no separate `reporting.read` unless governance decides otherwise at authorization |
| DL-03 `includeArchived` gate | Role-gated; default exclude archived |
| Dimension catalog alignment | `dimension-catalog.md` D-TIME through D-PRV |
| Architecture boundary tests | `boundary-sketch.md` BT-01–BT-07 |
| PHPStan level 8, Laravel Pint, Pest tests | Project Definition of Done |
| P-033 spec10 non-mutation checklist | Required before PR merge per P2-C-06 |

### 5.6 Suggested delivery sequence (for decision scoping only)

Not a governance wave model — practical ordering if authorization is granted:

1. Reporting module scaffold + T0 adapter + RU-01 / RU-06 (entity timeline, drill-down)
2. T1 schema + refresh + RU-02 / RU-03 / RU-05 (correlation, window summary, security review)
3. RU-04 compliance export assembly + boundary and integration tests

---

## 6. IN_SCOPE vs OUT_OF_SCOPE

### In scope (requested)

| Item |
| ---- |
| Reporting module implementation per §5 |
| Reporting-owned T1 migrations and projection refresh jobs (application-internal — not production rollout) |
| Read API / export façade per DL-02 (no Operator Explorer UI in initial scope) |
| Hybrid T0/T1/T2 assembly per DL-01 |
| M1 audit coverage only (Identity, Voucher `sourceContext` values) — aggregates tolerate absent M4 dimensions |
| `Administrator` as primary security-reporting audience (P2-C-02) |

### Out of scope (not requested)

| Item |
| ---- |
| Any spec10 mutation — `audit_logs`, Audit module code, `AuditHistoryReadContract`, `AuditHistoryQuery`, producers, retention jobs |
| `correlationId` filter on frozen `AuditHistoryQuery` |
| E-03 Operator Explorer UI (Livewire/Blade explorer) |
| E-04 compliance KPI dashboards and metrics (P2-C-03 deferred) |
| `SecurityAuditor` role introduction (deferred) |
| M4 audit producer rollout (Request, Lottery, Allocation, CheckIn, Notification) |
| Analytics platform / BI / OLAP expansion |
| Production rollout, deployment, or operational scheduling authorization |
| spec04 Dormitory or other upstream domain implementation |
| Bridge activation or spec10 reopening |

---

## 7. SPEC10_PRESERVATION

| Rule | Status |
| ---- | ------ |
| spec10 remains **CLOSED / FROZEN** | **Required** |
| Reporting consumes audit via `AuditHistoryReadContract` only | **Required** |
| No INSERT/UPDATE/DELETE against `audit_logs` from Reporting | **Required** (AP-06) |
| No import of `App\Modules\Audit\Infrastructure\*` | **Required** |
| T0 wins on audit disputes over T1 projections | **Required** |

---

## 8. AUTHORIZATION_POSTURE

| Statement | Value |
| --------- | ----- |
| This request authorizes implementation | **No** |
| This request authorizes execution or rollout | **No** |
| Implementation may begin | **Only after** `implementation-authorization-decision.md` with explicit approval |
| P2 planning artifacts remain binding baseline | **Yes** |
| Design conditions C-01–C-06 remain in effect unless closed in implementation decision | **Yes** |

---

## 9. NEXT_REQUIRED_ARTIFACT

Upon review of this request, the next required governance artifact is:

**[`implementation-authorization-decision.md`](./implementation-authorization-decision.md)**

Issuance of that decision — with outcome **APPROVED**, **APPROVED_WITH_CONDITIONS**, **DEFERRED**, or **REJECTED** — is a separate governance act. This request does not predetermine the outcome.

---

**End of Implementation Authorization Request. Status: REQUESTED — NOT APPROVED.**
