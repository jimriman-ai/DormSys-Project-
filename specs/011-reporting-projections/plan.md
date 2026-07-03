# Implementation Plan: Reporting & Audit Consumption Evolution (spec11)

**Branch**: `011-reporting-projections`  
**Created**: 2026-07-02  
**Status**: **Architecture clarified — planning-only** — **not** implementation plan

**Clarification baseline**: [`architecture-clarification.md`](./architecture-clarification.md)

**Predecessor baseline**: spec10 — CLOSED / FROZEN — [`spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md)

**Authority**: None — this plan does not authorize implementation, waves, or checkpoints.

---

## Planning Intent

spec11 explores how DormSys evolves **audit consumption and reporting** while preserving spec10 as the immutable append-only system of record. This document frames **evolution tracks**, **hypotheses**, and **planning phases** only.

---

## Inherited Constraints (non-negotiable)

| Constraint | Source | Planning rule |
| ---------- | ------ | ------------- |
| R10 downstream-only | context-map | Reporting reads audit via contracts — never upstream repos for audit facts |
| AP-06 append-only | Constitution | No reporting path may mutate `audit_logs` |
| CD-017 read-only Reporting | catalog-decisions | Projections are derived; Reporting owns no upstream lifecycle |
| spec10 frozen | final closure | No dependency on spec10 scope changes |
| Bridge off by default | spec10 baseline | Reporting plans must not require bridge activation |
| Soft-archive retention | spec10 baseline | Read models must account for `archived_at` visibility policy |

---

## Evolution Tracks (future possibilities)

### E-01 — Audit reporting surfaces

**Hypothesis**: Authorized audit consumption can evolve through a Reporting-owned query façade that wraps or projects `AuditHistoryReadContract` results — adding aggregations, saved views, and export intents without new write paths.

**Planning questions**:
- What aggregations do compliance stakeholders require (by entity, actor, event type, period)?
- Should exports be synchronous or job-based?
- How is `audit.read` permission propagated to reporting surfaces?

**Risk if skipped**: Ad-hoc queries bypass governance and R11 boundaries.

---

### E-02 — Read-model / projection architecture

**Hypothesis**: A dedicated Reporting read-model layer materializes audit (and optionally other context) projections refreshed on schedule or event — CD-017 compliant, no upstream writes.

**Planning questions**:
- Snapshot vs incremental projection refresh?
- Co-location with `audit_logs` vs separate projection tables owned by Reporting?
- Archive-tier projections: include/exclude soft-archived rows by default?

**Resolved (DL-01)**: Hybrid T0 contract-direct + T1 Reporting-owned materialized projections. See [`architecture-clarification.md` §3](./architecture-clarification.md).

---

### E-03 — Audit explorer (conceptual UI)

**Hypothesis**: Operator-facing audit explorer lives in Reporting/Presentation layer, consuming read contracts — not inside `app/Modules/Audit/`.

**Planning questions**:
- Minimum viable explorer: entity timeline vs global search?
- Jalali date presentation and RTL layout requirements?
- Relationship to OA-10-05 deferred UI from spec10?

**Explicit non-commitment**: No UI implementation in spec11 initialization.

---

### E-04 — Compliance & analytics views

**Hypothesis**: Role-gated dashboards (DormMgr, HRMgr, Administrator extensions) provide trend and exception views over projections — not live cross-module OLTP queries.

**Planning questions**:
- Which audit event categories matter for compliance KPIs?
- Data minimization for snapshots in projections?

---

### E-05 — Performance scaling strategy

**Hypothesis**: As audit volume grows, read-heavy workloads shift to projections and archive-aware indexes; operational `audit_logs` queries remain for low-volume authorized investigation.

**Planning questions**:
- Volume thresholds triggering projection recommendation?
- Read replica vs materialized view in PostgreSQL context?

---

### E-06 — Future producer onboarding (M4)

**Hypothesis**: Deferred spec10 producers (Request, Lottery, Allocation, CheckIn, Notification) are onboarded under **separate authorization**; spec11 reporting vocabulary anticipates event types but does not implement producers.

**Planning questions**:
- Reporting dimension model extensibility for new `AuditEventType` values?
- Coordination with per-context implementation programs?

---

### E-07 — Consumption layer decoupling

**Hypothesis**: Reporting module becomes the **only** cross-boundary read consumer (constitution-aligned), replacing any temptation for domain modules to query `audit_logs` directly.

**Planning questions**:
- Architecture test boundaries for Reporting similar to `AuditBoundaryTest`?
- Forbidden imports from Audit Infrastructure vs allowed Application contracts?

---

### E-08 — Governance model for ecosystem expansion

**Hypothesis**: Each expansion (explorer UI, exports, SIEM, advanced analytics) requires its own wave authorization — spec11 defines the taxonomy, not the execution schedule.

**Planning phases (governance-only)**:

| Phase | Intent | Executable? |
| ----- | ------ | ----------- |
| P0 | Initialization (this spec) | No |
| P1 | Clarify + decision log resolution | **Complete** — [`architecture-clarification.md`](./architecture-clarification.md); DL-01–DL-03 resolved |
| P2 | Technical planning (`data-model`, contracts) | No — requires design approval |
| P3 | Implementation authorization | No — separate record |
| P4+ | Waves (projections, UI, exports) | No — future |

---

## Planning Dependencies

| Dependency | Status | Notes |
| ---------- | ------ | ----- |
| spec10 Audit module | **FROZEN** | Consume `AuditHistoryReadContract` only |
| spec10 final closure | **CANONICAL** | [`spec10-final-closure.md`](../../.specify/docs/handoff/spec10-final-closure.md) |
| Identity `audit.read` | **Delivered** | Role gate for read surfaces |
| M4 audit producers | **Deferred** | Do not assume coverage |
| spec09 Notification | **CLOSED** | No coupling required for init |

---

## Out of Scope (plan level)

Same as spec.md §4 — no implementation, schema, runtime, producer, bridge, or retention changes.

---

## Architecture Clarification Summary (P1 complete)

| Deliverable | Location |
| ----------- | -------- |
| SPEC11_ARCHITECTURE_CLARIFICATION | [`architecture-clarification.md` §1](./architecture-clarification.md) |
| READ_MODEL_CONCEPTUAL_DESIGN | §2 |
| PROJECTION_BOUNDARY_MODEL | §3 |
| REPORTING_CONSUMER_FRAME | §4 |
| ANALYTICS_SEPARATION_MODEL | §5 |
| NON_SCOPE (strict) | §6 |
| FUTURE_WAVE_PREPARATION_NOTES | §7 |

## Next Planning Steps (not execution)

1. P-013 compliance stakeholder interviews (optional).
2. P2 technical planning — `data-model.md`, `contracts/`, `research.md` (not authorized yet).
3. Governance nomination for spec11 when ready.
4. Design approval + implementation authorization — **separate records**.

---

**End of plan. Planning-only.**
