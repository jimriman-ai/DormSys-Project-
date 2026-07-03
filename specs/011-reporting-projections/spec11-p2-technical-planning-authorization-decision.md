# spec11 P2 Technical Planning Authorization Decision Record

---

## 1. DECISION_HEADER

| Field | Value |
| ----- | ----- |
| **Spec identifier** | spec11 — Reporting & Audit Consumption Evolution (`reporting-projections`) |
| **Feature branch** | `011-reporting-projections` |
| **Decision type** | **P2_TECHNICAL_PLANNING_AUTHORIZATION_DECISION** |
| **Decision date** | 2026-07-03 |
| **Authority** | **Governance Review** — Product / Tech governance |
| **Request under review** | [`spec11-p2-technical-planning-authorization-request.md`](./spec11-p2-technical-planning-authorization-request.md) (`REQUESTED_NOT_APPROVED` at submission) |
| **Pre-decision canonical state** | **`DESIGN_APPROVED_WITH_CONDITIONS`** |
| **Predecessor** | spec10 — **CLOSED / FROZEN** (immutable) |
| **Design baseline** | spec11 Design Approval Decision Record (2026-07-03); `architecture-clarification.md`; `decision-log.md` DL-01–DL-03 |
| **Transition basis** | [`spec11-governance-transition-control.md`](./spec11-governance-transition-control.md) T-12 |

---

## 2. DECISION_OUTCOME

**APPROVED_WITH_CONDITIONS**

---

## 3. DECISION_BASIS

- **Design-stage prerequisite satisfied.** Design Approval granted `DESIGN_APPROVED_WITH_CONDITIONS` (2026-07-03). `plan.md` E-08 requires design approval before P2; prerequisite met subject to condition disposition.
- **Request is bounded and eligible.** The submitted P2 request seeks authorization for P-020–P-024 only; excludes implementation, execution, schema, runtime, and spec10 mutation — aligned with `spec.md` §4, `tasks.md` Phase P2, and transition control §7.
- **Architecture clarification sufficient for P2 planning.** P1 complete; `architecture-clarification.md` **CLARIFIED**; DL-01 Hybrid T0/T1/T2, DL-02 Layered B→A, DL-03 role-gated `includeArchived` resolved in `decision-log.md`.
- **Design-approval conditions C-01–C-03 disposition acceptable.** Request proposes: extend `audit.read` (C-01 / UD-11-01 default per `decision-log.md`); defer `SecurityAuditor` to Implementation Authorization (C-02 / UD-11-02); defer E-04 compliance KPI dimensions with P-024 vocabulary-only scope (C-03 / P-013 open). Baseline documents these as non-blocking for P2 entry when explicitly scoped.
- **spec10 preservation maintained.** Request and design baseline require consumption via frozen `AuditHistoryReadContract` only; no spec10 mutation requested (`architecture-clarification.md` §3.6, §6; Design Approval C-04).
- **No grounds for deferral or rejection.** P0/P1 complete; open items bounded; request is the documented next eligible governance step per `spec11-governance-transition-control.md` §7.
- **Conditions required for P2 work.** Design conditions C-04 and C-06 remain binding; P2 deliverables must remain planning documents only; P-033 verification required before PR merge — warrant **APPROVED_WITH_CONDITIONS** rather than unconditional approval.

---

## 4. AUTHORIZED_SCOPE

P2 technical planning is **authorized with conditions** for the following tasks only:

| Task ID | Authorized activity | Authorized deliverable |
| ------- | ------------------- | ---------------------- |
| **P-020** | Draft `data-model.md` for Reporting read models | Projection entities only — Reporting-owned T1/T2 shapes per DL-01; no `audit_logs` schema change |
| **P-021** | Draft `contracts/` for reporting read ports | Read-only ports; CD-017 compliant; no upstream write paths |
| **P-022** | Draft `research.md` on projection refresh patterns | Snapshot vs incremental; archive-aware tiers; DL-03-C evaluation if performance evidence warrants |
| **P-023** | Architecture boundary sketch | Reporting vs Audit vs upstream contexts per `architecture-clarification.md` §3.6, §7.3 |
| **P-024** | Map `AuditEventType` vocabulary to reporting dimensions | Read-only catalog; no producer rollout |

**Authorized path:** `specs/011-reporting-projections/` planning artifacts only.

**Maximum scope:** P-020 through P-024. No other task IDs authorized by this decision.

---

## 5. CONDITIONS_OR_BLOCKERS

### Binding conditions (APPROVED_WITH_CONDITIONS)

| ID | Condition | Disposition |
| -- | --------- | ----------- |
| **P2-C-01** | **C-01 / UD-11-01 closed for P2** — Permission model: **extend `audit.read`** for reporting read ports in P-021 unless a separate governance change request records otherwise | **Accepted** per request §5 |
| **P2-C-02** | **C-02 / UD-11-02 closed for P2** — **`SecurityAuditor` role deferred**; P-021 and P-023 document `Administrator` as primary security-reporting audience per `architecture-clarification.md` §4.3; role introduction deferred to Implementation Authorization | **Accepted** per request §5 |
| **P2-C-03** | **C-03 closed for P2** — **E-04 compliance KPI dimensions explicitly deferred** to post-P2 wave; P-024 maps existing `AuditEventType` vocabulary only; P-013 remains optional follow-up | **Accepted** per request §5 |
| **P2-C-04** | **C-04 ongoing** — DL-01, DL-02, DL-03 and `architecture-clarification.md` remain binding; all P2 deliverables must conform | **Required** |
| **P2-C-05** | **C-05 / DL-03-C** — Archive-tier projection (Option C) evaluated in P-020 and P-022 **only** if performance partition evidence requires; DL-03 visibility policy unchanged | **Required** |
| **P2-C-06** | **C-06** — **P-033** spec10 non-mutation checklist verification **required before any PR** merging P2 planning artifacts | **Required before PR** |
| **P2-C-07** | **No spec10 contract extension** — P2 artifacts must not extend `AuditHistoryQuery` or frozen `AuditHistoryReadContract`; correlation indexing via T1 projection design only per `architecture-clarification.md` §2.2 | **Required** |
| **P2-C-08** | **Planning-only boundary** — Authorized work produces planning documents only; no code, migrations, modules, jobs, or UI under this authorization | **Required** |

---

## 6. NON_AUTHORIZED_SCOPE

This decision explicitly does **NOT** authorize:

| Exclusion | Confirmed |
| --------- | --------- |
| Implementation (code, modules, migrations, jobs) | **Yes** — P4 HALT; `executable: false` unchanged |
| Execution (waves, checkpoints, runtime activation) | **Yes** — `execution_state: NONE` |
| Schema changes (including `audit_logs` or spec10 persistence) | **Yes** |
| Runtime changes (schedulers, bridge, producers) | **Yes** |
| spec10 mutation or reopening | **Yes** — remains CLOSED / FROZEN |
| Implementation Authorization (P-032) | **Yes** — separate record required |
| P4 tracks E-01–E-08 | **Yes** — remain HALT |
| Production rollout or operational execution | **Yes** |

---

## 7. STATE_EFFECT

| Field | Value |
| ----- | ----- |
| **Post-decision canonical state** | **`P2_AUTHORIZED`** |
| **Transition applied** | `DESIGN_APPROVED_WITH_CONDITIONS` → `P2_AUTHORIZED` via T-12 (`spec11-governance-transition-control.md`) |
| **Design Approval** | **Preserved** — `DESIGN_APPROVED_WITH_CONDITIONS` outcome not revoked |
| **P-020–P-024** | **Authorized** subject to §5 conditions |
| **`executable`** | **`false`** — unchanged |
| **`execution_state`** | **`NONE`** — unchanged |
| **Implementation Authorization** | **Not issued** |
| **spec10** | **CLOSED / FROZEN** — unchanged |
| **Request status** | `spec11-p2-technical-planning-authorization-request.md` superseded for active authority by this decision record |

---

## 8. NEXT_REQUIRED_PROJECT_STEP

**Execute P-020** — draft `data-model.md` for Reporting read models (projection entities only) under the authorized P2 scope and binding conditions P2-C-01 through P2-C-08.

---

**End of P2 Technical Planning Authorization Decision Record. Outcome: APPROVED_WITH_CONDITIONS. Canonical state: P2_AUTHORIZED. P-020–P-024 authorized for planning artifacts only.**
