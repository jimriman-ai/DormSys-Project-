# spec11 P2 Technical Planning Authorization Request

---

## 1. P2_AUTHORIZATION_REQUEST_HEADER

| Field | Value |
| ----- | ----- |
| **Spec identifier** | spec11 — Reporting & Audit Consumption Evolution (`reporting-projections`) |
| **Feature branch** | `011-reporting-projections` |
| **Request type** | **P2_TECHNICAL_PLANNING_AUTHORIZATION_REQUEST** |
| **Request status** | **REQUESTED_NOT_APPROVED** |
| **Request date** | 2026-07-03 |
| **Authority status** | **NONE** — this document grants no authorization |
| **Current canonical state** | **`DESIGN_APPROVED_WITH_CONDITIONS`** |
| **Predecessor** | spec10 — **CLOSED / FROZEN** (immutable) |
| **Lifecycle context** | `execution_state: NONE` · `executable: false` · no Implementation Authorization · no execution authorization |
| **Governance chain position** | Per `spec.md` §5 rule 2 and `plan.md` E-08: Design Approval satisfied (with conditions) → **P2 technical planning authorization** (this request) → Implementation Authorization (not requested) |
| **Transition control basis** | [`spec11-governance-transition-control.md`](./spec11-governance-transition-control.md) §7 — next eligible step |
| **Planning artifacts in scope** | `spec.md`, `plan.md`, `tasks.md`, `architecture-clarification.md`, `decision-log.md`, `spec11-governance-nomination-draft.md`, `spec11-design-authorization-request.md`, spec11 Design Approval Decision Record (2026-07-03), `spec11-governance-transition-control.md` |

---

## 2. REQUEST_PURPOSE

This artifact is a **formal request for P2 Technical Planning Authorization only**.

It asks governance to evaluate whether spec11 may proceed with **Phase P2 technical planning tasks P-020 through P-024** — producing planning artifacts (`data-model.md`, `contracts/`, `research.md`, boundary sketch, read-only dimension catalog) under the approved design baseline.

This request does **not** seek Implementation Authorization, execution authority, schema change, runtime change, or spec10 mutation.

---

## 3. GOVERNANCE_BASIS

### Design approval basis

| Fact | Source |
| ---- | ------ |
| Design Approval granted **WITH CONDITIONS** (2026-07-03) | spec11 Design Approval Decision Record |
| `FINAL_DECISION: DESIGN_APPROVED_WITH_CONDITIONS` | Design Approval Decision Record |
| P2 prerequisite (design approval) satisfied **subject to conditions** | Design Approval Decision Record §5; `plan.md` E-08 P2 row |

### Transition control basis

| Fact | Source |
| ---- | ------ |
| Canonical state = `DESIGN_APPROVED_WITH_CONDITIONS` | `spec11-governance-transition-control.md` §6 |
| This request is the **next eligible governance-safe step** | `spec11-governance-transition-control.md` §7 |
| Valid transition upon approval: `DESIGN_APPROVED_WITH_CONDITIONS` → `P2_AUTHORIZED` via T-12 (requires separate P2 authorization **decision** record) | `spec11-governance-transition-control.md` §3 T-12 |
| No P2 authorization currently issued; P-020–P-024 unauthorized | `tasks.md` Phase P2 header |

### Design baseline (binding)

| Constraint | Source |
| ---------- | ------ |
| DL-01 Hybrid T0/T1/T2 projection strategy | `decision-log.md`; `architecture-clarification.md` §3 |
| DL-02 Layered B→A consumption (API/export first; explorer UI later) | `decision-log.md`; `architecture-clarification.md` §5.4 |
| DL-03 Role-gated `includeArchived` | `decision-log.md`; `architecture-clarification.md` §4 |
| CD-017 / R11 read-only Reporting; AP-06 / R10 inherited | `spec.md` §5; `plan.md` Inherited Constraints |
| Consumption via frozen `AuditHistoryReadContract` only | `architecture-clarification.md` §1, §3.6 |
| P2 planning artifacts only — no implementation commitment | `spec.md` §4; `plan.md` Out of Scope |

### spec10 frozen confirmation

| Assertion | Confirmed |
| --------- | --------- |
| spec10 remains **CLOSED / FROZEN** | `spec.md` predecessor; `tasks.md` metadata |
| No spec10 mutation, contract extension, or producer change requested | `architecture-clarification.md` §3.6, §6; Design Approval condition C-04 |
| Reporting consumes audit history via frozen port only | `spec.md` Depends on |

---

## 4. REQUESTED_AUTHORIZATION_SCOPE

Authorization is requested **only** for the following P2 technical planning activities:

| Task ID | Requested activity | Deliverable (planning only) |
| ------- | ------------------ | --------------------------- |
| **P-020** | Draft `data-model.md` for Reporting read models | Projection entities only — Reporting-owned T1/T2 shapes per DL-01; no `audit_logs` schema change |
| **P-021** | Draft `contracts/` for reporting read ports | Read-only ports; CD-017 compliant; no upstream write paths |
| **P-022** | Draft `research.md` on projection refresh patterns | Snapshot vs incremental; archive-aware tiers; DL-03-C evaluation if performance evidence warrants |
| **P-023** | Architecture boundary sketch | Reporting vs Audit vs upstream contexts; forbidden imports per `architecture-clarification.md` §3.6, §7.3 |
| **P-024** | Map `AuditEventType` vocabulary to reporting dimensions | Read-only catalog; no producer rollout |

### Scope boundary

| In scope | Out of scope (not requested) |
| -------- | ---------------------------- |
| P-020–P-024 planning artifact drafting under `specs/011-reporting-projections/` | P-030–P-033 (except P-033 verification when P2 artifacts are PR-ready per C-06) |
| Technical planning aligned to approved architecture clarification | P-032 Implementation authorization scope proposal |
| P2 research and boundary documentation | P4 implementation tracks E-01–E-08 (HALT) |
| Condition disposition within P2 artifacts (UD-11-01, UD-11-02, DL-03-C) | Code, migrations, modules, jobs, Livewire/Blade UI |
| | spec10 mutation or `AuditHistoryQuery` / contract extension |
| | Production rollout or operational execution |

**Maximum authorized scope if granted:** Phase P2 technical planning tasks **P-020 through P-024 only**.

---

## 5. PRECONDITION_STATUS

Conditions from the Design Approval Decision Record (2026-07-03). Status at request submission:

### Already satisfied

| Item | Evidence |
| ---- | -------- |
| Design Approval issued (`DESIGN_APPROVED_WITH_CONDITIONS`) | Design Approval Decision Record |
| P0 initialization complete | `spec.md` §7; `tasks.md` P-001–P-006 |
| P1 architecture clarification complete | `spec.md` §7; `tasks.md` P-010, P-011, P-012, P-014; `architecture-clarification.md` **CLARIFIED** |
| DL-01, DL-02, DL-03 resolved | `decision-log.md` |
| Design baseline binding (C-04) | Architecture clarification + decision log in effect |
| Canonical state permits P2 authorization request | `spec11-governance-transition-control.md` §6–§7 |
| spec10 frozen | `spec.md`; `tasks.md`; invariant across baseline |

### Must be resolved before authorization (or explicitly scoped in authorization decision)

| ID | Condition | Current status | Proposed disposition in this request |
| -- | --------- | -------------- | ------------------------------------ |
| **C-01** | UD-11-01 (`reporting.read` vs extend `audit.read`) recorded before P2 authorization | **OPEN** | **Scope into P2 authorization:** adopt planning default **extend `audit.read`** per `decision-log.md`; finalize in P-021 contracts unless governance records otherwise in authorization decision |
| **C-02** | UD-11-02 (`SecurityAuditor` role) before security-reporting contract scope finalized | **OPEN** | **Scope into P2 authorization:** **defer role introduction**; document `Administrator` as primary security-reporting audience in P-021/P-023 per `architecture-clarification.md` §4.3; revisit at Implementation Authorization |
| **C-03** | P-013 complete or E-04 compliance KPI scope explicitly deferred | **OPEN** (P-013 unchecked) | **Scope into P2 authorization:** **explicitly defer E-04 compliance KPI dimensions** to post-P2 wave; P-024 maps existing `AuditEventType` vocabulary only; P-013 remains optional follow-up |

### Ongoing / P2-internal (not blocking authorization if scoped as above)

| ID | Condition | Status | Treatment |
| -- | --------- | ------ | --------- |
| **C-04** | DL-01–DL-03 and `architecture-clarification.md` remain binding | **ONGOING** | All P2 deliverables must conform; no spec10 contract extension without separate change request |
| **C-05** | DL-03-C archive-tier projection deferred to P2 | **OPEN (deferred)** | Evaluate in P-020 `data-model.md` / P-022 `research.md` only if performance partition evidence requires — policy unchanged per DL-03 |
| **C-06** | P-033 spec10 non-mutation checklist before boundary-affecting PRs | **OPEN** | Required before any PR merging P2 artifacts; not a prerequisite to grant P2 authorization |

### Open planning items (evidence — not lifecycle blockers)

| ID | Status | Source |
| -- | ------ | ------ |
| UD-11-01 | OPEN (default: extend `audit.read`) | `decision-log.md` |
| UD-11-02 | OPEN | `decision-log.md` |
| P-013 | OPEN | `tasks.md` |
| DL-03-C | DEFERRED to P2 | `decision-log.md` DL-03 |

---

## 6. DOWNSTREAM_BLOCKS_PRESERVED

This request explicitly does **NOT** authorize:

| Exclusion | Preserved |
| --------- | --------- |
| Implementation (code, modules, migrations, jobs) | **Yes** — P4 HALT; `executable: false` |
| Execution (waves, checkpoints, runtime activation) | **Yes** — `execution_state: NONE` |
| Schema change (including `audit_logs` or spec10 persistence) | **Yes** — `spec.md` §4; `plan.md` Out of Scope |
| Runtime change (schedulers, bridge, producers) | **Yes** — `spec.md` §4 |
| spec10 mutation or reopening | **Yes** — frozen predecessor |
| Implementation planning beyond P2 scope (P-032) | **Yes** — not requested |
| Production rollout or operational execution | **Yes** — not requested |
| P2 task execution | **Yes** — remains blocked until separate **P2 Technical Planning Authorization decision** record issues approval |

P3 Implementation Authorization and P4 execution remain **blocked** regardless of outcome on this request until separate governance records are issued.

---

## 7. DECISION_REQUESTED

Governance is requested to render **exactly one** outcome on this P2 Technical Planning Authorization Request:

| Outcome | Effect if selected |
| ------- | ------------------ |
| **Approve P2 technical planning authorization** | Authorize P-020–P-024 planning artifact work only; issue separate P2 authorization **decision** record; canonical state may transition to `P2_AUTHORIZED` per `spec11-governance-transition-control.md` T-12 |
| **Reject** | P-020–P-024 remain unauthorized; canonical state remains `DESIGN_APPROVED_WITH_CONDITIONS` |
| **Defer** | Additional evidence or condition closure required; no P2 unlock |

### Reviewer disposition guidance (informational — not pre-decided)

- C-01–C-03 proposed scoping above is submitted for acceptance, modification, or rejection in the authorization decision.
- Approval must not be interpreted as Implementation Authorization or execution unlock.
- Rejection or deferral does not revoke Design Approval.

**No outcome is implied or pre-selected by this request.**

---

## 8. NON_AUTHORIZATION_STATEMENT

| Assertion | Confirmed |
| --------- | --------- |
| This document is a **request**, not an approval | **Yes** |
| `request_status` remains **REQUESTED_NOT_APPROVED** until a separate P2 authorization **decision** record is issued | **Yes** |
| No canonical lifecycle state transition occurs upon submission of this request | **Yes** — state remains `DESIGN_APPROVED_WITH_CONDITIONS` until decision record |
| No implementation authorization granted | **Yes** |
| No execution authorization granted | **Yes** |
| No schema or runtime change authorized | **Yes** |
| spec10 remains **CLOSED / FROZEN** | **Yes** |
| P-020–P-024 remain **unauthorized** until explicit approval | **Yes** |
| This request does not create a new governance layer, gate, or transition-control artifact | **Yes** |
| Authority status of this artifact | **NONE** |

---

**End of P2 Technical Planning Authorization Request. Request only. No P2 unlock. No implementation. No execution.**
