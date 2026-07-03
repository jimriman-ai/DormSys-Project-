# spec11 Canonical Governance Transition Control

**Type**: Governance control normalization — spec11 only  
**Recorded**: 2026-07-03  
**Authority**: **NONE** — this artifact defines interpretation rules only; it grants no authorization  
**Scope**: Lifecycle state, transition authority, downstream eligibility for spec11  
**Predecessor (immutable)**: spec10 — **CLOSED / FROZEN**  
**Baseline inputs**: `spec.md`, `plan.md`, `tasks.md`, `architecture-clarification.md`, `decision-log.md`, `spec11-governance-nomination-draft.md`, `spec11-design-authorization-request.md`, spec11 Design Approval Decision Record (2026-07-03)

---

## 1. CANONICAL_STATE_MODEL

Canonical lifecycle state is a **single authoritative label** for spec11 governance position. Only transitions listed in §3 are valid. States not reached by an explicit transition do not exist for governance purposes.

### PLANNING_ONLY

| Field | Value |
| ----- | ----- |
| **Definition** | spec11 exists as a planning-only evolution charter with no architecture clarification completion and no governance review chain entered. |
| **State meaning** | Documentation and hypothesis formation only; no design review; no downstream eligibility. |
| **Entry condition** | P0 initialization complete (`spec.md` §7 Initialization); no P1 exit criteria satisfied. |
| **Exit condition** | P1 architecture clarification exit criteria met (`spec.md` §7 P1) **or** governance chain entered (nomination / design request). |
| **Allowed next states** | `ARCHITECTURE_CLARIFIED`, `DESIGN_AUTH_REQUESTED` (if clarification skipped — **invalid** per `plan.md` E-08; do not use) |

### ARCHITECTURE_CLARIFIED

| Field | Value |
| ----- | ----- |
| **Definition** | P1 complete; consumption-layer architecture documented; DL-01–DL-03 resolved; no Design Approval issued. |
| **State meaning** | Planning baseline frozen at clarification; eligible for governance nomination and Design Authorization Request only. |
| **Entry condition** | `architecture-clarification.md` status **CLARIFIED**; P-010, P-011, P-012, P-014 complete; `spec.md` §7 P1 criteria met. |
| **Exit condition** | Formal Design Authorization Request submitted **or** superseded by a state-bearing decision record. |
| **Allowed next states** | `NOMINATION_PREPARED`, `DESIGN_AUTH_REQUESTED`, `DEFERRED`, `REJECTED`, `TERMINATED` |

### NOMINATION_PREPARED

| Field | Value |
| ----- | ----- |
| **Definition** | Governance nomination evidence package exists; nomination review may proceed; no design decision issued. |
| **State meaning** | Review preparation complete; **not** nomination acceptance; **not** design approval. |
| **Entry condition** | `spec11-governance-nomination-draft.md` (or successor formal nomination record) exists with authority **NONE**. |
| **Exit condition** | Design Authorization Request submitted **or** governance terminates / defers program. |
| **Allowed next states** | `DESIGN_AUTH_REQUESTED`, `ARCHITECTURE_CLARIFIED` (nomination withdrawn — requires explicit governance note), `DEFERRED`, `TERMINATED` |

*Note: `NOMINATION_PREPARED` is **evidence-aligned**, not a mandatory gate. Design Authorization Request may proceed from `ARCHITECTURE_CLARIFIED` without entering this state if nomination draft exists in parallel.*

### DESIGN_AUTH_REQUESTED

| Field | Value |
| ----- | ----- |
| **Definition** | Formal Design Authorization Request submitted; outcome pending; request status **REQUESTED_NOT_APPROVED**. |
| **State meaning** | Awaiting Design Approval Decision Record; no approval implied. |
| **Entry condition** | `spec11-design-authorization-request.md` issued with `REQUESTED_NOT_APPROVED`. |
| **Exit condition** | State-bearing Design Approval Decision Record issued with exactly one outcome. |
| **Allowed next states** | `DESIGN_APPROVED`, `DESIGN_APPROVED_WITH_CONDITIONS`, `DESIGN_DEFERRED` → maps to `DEFERRED`, `DESIGN_REJECTED` → maps to `REJECTED` |

### DESIGN_APPROVED_WITH_CONDITIONS

| Field | Value |
| ----- | ----- |
| **Definition** | Design Approval granted with documented conditions; design baseline binding; conditions may remain open. |
| **State meaning** | P2 prerequisite (design approval) **satisfied subject to conditions**; P2, implementation, and execution remain unauthorized. |
| **Entry condition** | Design Approval Decision Record outcome = **DESIGN_APPROVED_WITH_CONDITIONS**. |
| **Exit condition** | All design-approval conditions satisfied **and** explicit transition to `DESIGN_APPROVED` recorded **or** program `DEFERRED` / `REJECTED` / `TERMINATED`. |
| **Allowed next states** | `DESIGN_APPROVED`, `P2_AUTHORIZATION_PENDING`, `DEFERRED`, `REJECTED`, `TERMINATED` |

### DESIGN_APPROVED

| Field | Value |
| ----- | ----- |
| **Definition** | Design Approval granted without outstanding design-stage conditions (or all conditions formally closed). |
| **State meaning** | P2 technical planning **prerequisite met**; P2 authorization still required separately. |
| **Entry condition** | Design Approval Decision Record outcome = **DESIGN_APPROVED** **or** transition from `DESIGN_APPROVED_WITH_CONDITIONS` after all conditions closed. |
| **Exit condition** | P2 Technical Planning Authorization Request submitted **or** program terminated. |
| **Allowed next states** | `P2_AUTHORIZATION_PENDING`, `DEFERRED`, `REJECTED`, `TERMINATED` |

### P2_AUTHORIZATION_PENDING

| Field | Value |
| ----- | ----- |
| **Definition** | Design Approval satisfied; P2 technical planning authorization not yet issued; P-020–P-024 remain unauthorized. |
| **State meaning** | Eligible for P2 authorization governance act only; no P2 artifact work authorized. |
| **Entry condition** | Canonical state is `DESIGN_APPROVED` **or** `DESIGN_APPROVED_WITH_CONDITIONS` with C-01–C-03 disposition rules met or explicitly scoped in pending P2 authorization. |
| **Exit condition** | Explicit P2 Technical Planning Authorization record issued **or** deferral / termination. |
| **Allowed next states** | `P2_AUTHORIZED`, `DEFERRED`, `REJECTED`, `TERMINATED` |

### P2_AUTHORIZED

| Field | Value |
| ----- | ----- |
| **Definition** | P2 technical planning explicitly authorized; P-020–P-024 may proceed as planning artifacts only. |
| **State meaning** | Technical planning (`data-model.md`, `contracts/`, `research.md`, boundary sketch) permitted; **not** implementation or execution. |
| **Entry condition** | Separate state-bearing **P2 Technical Planning Authorization** record issued (not defined in current baseline — must be created by future governance). |
| **Exit condition** | P2 tasks complete and Implementation Authorization Request eligible **or** program closure. |
| **Allowed next states** | Implementation-planning governance states (out of scope for this control artifact until separately defined), `TERMINATED` |

### DEFERRED

| Field | Value |
| ----- | ----- |
| **Definition** | Governance explicitly deferred further progression; documented conditions or evidence gaps block continuation. |
| **State meaning** | No downstream eligibility until deferral lifted by new decision record. |
| **Entry condition** | Design Approval Decision Record outcome = deferral **or** explicit deferral decision on P2 / program. |
| **Exit condition** | New decision record lifting deferral and restoring prior eligible state. |
| **Allowed next states** | Restored prior state per deferral record (typically `ARCHITECTURE_CLARIFIED` or `DESIGN_AUTH_REQUESTED`) |

### REJECTED

| Field | Value |
| ----- | ----- |
| **Definition** | Design or program rejected; no forward governance progression without new initiation. |
| **State meaning** | spec11 remains planning-only at best; no P2 or implementation eligibility. |
| **Entry condition** | Design Approval Decision Record outcome = **DESIGN_REJECTED** (or explicit program rejection). |
| **Exit condition** | New governance initiation (not implied). |
| **Allowed next states** | `TERMINATED` only without new initiation record |

### TERMINATED

| Field | Value |
| ----- | ----- |
| **Definition** | spec11 program closed; no active forward scope. |
| **State meaning** | Historical artifacts preserved; no transitions except explicit program reopening by governance (not authorized here). |
| **Entry condition** | Explicit program closure record (not in current baseline). |
| **Exit condition** | None without new governance initiation. |
| **Allowed next states** | None |

---

## 2. STATE_BEARING_AUTHORITY_RULES

### Artifact class definitions

| Class | May change canonical lifecycle state? | Purpose |
| ----- | ------------------------------------- | ------- |
| **Evidence artifacts** | **No** | Document facts, constraints, hypotheses, task backlog |
| **Request artifacts** | **No** | Ask governance for a decision; status remains requested until decision |
| **Decision artifacts** | **Yes** | Render exactly one governance outcome that sets canonical state |
| **Control artifacts** | **No** (interpretation only) | Lock transition rules; resolve conflicts; do not auto-advance lifecycle |

### Existing artifact classification

| Artifact | Class | State-bearing? | Condition-bearing? | Evidence-only? | Notes |
| -------- | ----- | -------------- | ------------------ | -------------- | ----- |
| `spec.md` | Evidence | No | No | Yes | Charter, boundaries, exit criteria — not lifecycle authority |
| `plan.md` | Evidence | No | No | Yes | Planning phases; states P2 requires design approval |
| `tasks.md` | Evidence | No | No | Partial | Task checkboxes are evidence; header metadata is **non-authoritative** for canonical state (§5) |
| `architecture-clarification.md` | Evidence | No | No | Yes | CLARIFIED status supports entry to `ARCHITECTURE_CLARIFIED` only historically |
| `decision-log.md` | Evidence | No | Partial | Yes | DL-01–DL-03 resolved; open UD items are conditions for downstream, not lifecycle state |
| `spec11-governance-nomination-draft.md` | Evidence | No | No | Yes | Authority NONE; supports `NOMINATION_PREPARED` evidence only |
| `spec11-design-authorization-request.md` | Request | No | No | No | `REQUESTED_NOT_APPROVED` until superseded by decision; does not approve |
| **spec11 Design Approval Decision Record** (2026-07-03) | **Decision** | **Yes** | **Yes** | No | Sets canonical state to `DESIGN_APPROVED_WITH_CONDITIONS`; conditions C-01–C-06 |
| `spec11-governance-transition-control.md` (this file) | Control | No | No | No | Normalizes interpretation; does not grant approval or authorization |

### Transition authority rule

Only a **Decision artifact** with an explicit outcome field may set or change canonical lifecycle state. All other artifacts are evidentiary, conditional, or interpretive.

---

## 3. TRANSITION_MATRIX

Explicit valid transitions only. Format: `FROM_STATE → TO_STATE → REQUIRED_ARTIFACT / REQUIRED_CONDITION`

| # | Transition | Required artifact / condition |
| - | ---------- | ----------------------------- |
| T-01 | `PLANNING_ONLY` → `ARCHITECTURE_CLARIFIED` | P1 exit criteria met (`spec.md` §7); `architecture-clarification.md` **CLARIFIED**; P-010, P-011, P-012, P-014 complete |
| T-02 | `ARCHITECTURE_CLARIFIED` → `NOMINATION_PREPARED` | `spec11-governance-nomination-draft.md` exists (authority NONE) |
| T-03 | `ARCHITECTURE_CLARIFIED` → `DESIGN_AUTH_REQUESTED` | `spec11-design-authorization-request.md` issued; `request_status: REQUESTED_NOT_APPROVED` |
| T-04 | `NOMINATION_PREPARED` → `DESIGN_AUTH_REQUESTED` | Same as T-03 |
| T-05 | `DESIGN_AUTH_REQUESTED` → `DESIGN_APPROVED` | Design Approval Decision Record; `FINAL_DECISION: DESIGN_APPROVED` |
| T-06 | `DESIGN_AUTH_REQUESTED` → `DESIGN_APPROVED_WITH_CONDITIONS` | Design Approval Decision Record; `FINAL_DECISION: DESIGN_APPROVED_WITH_CONDITIONS` |
| T-07 | `DESIGN_AUTH_REQUESTED` → `DEFERRED` | Design Approval Decision Record; `FINAL_DECISION: DESIGN_DEFERRED` |
| T-08 | `DESIGN_AUTH_REQUESTED` → `REJECTED` | Design Approval Decision Record; `FINAL_DECISION: DESIGN_REJECTED` |
| T-09 | `DESIGN_APPROVED_WITH_CONDITIONS` → `DESIGN_APPROVED` | Condition-closure decision record; all design-approval conditions C-01–C-06 formally closed |
| T-10 | `DESIGN_APPROVED_WITH_CONDITIONS` → `P2_AUTHORIZATION_PENDING` | Design Approval Decision Record in effect; C-01–C-03 disposition recorded **or** explicitly scoped in pending P2 authorization |
| T-11 | `DESIGN_APPROVED` → `P2_AUTHORIZATION_PENDING` | Design Approval Decision Record in effect (unconditional approval path) |
| T-12 | `P2_AUTHORIZATION_PENDING` → `P2_AUTHORIZED` | **P2 Technical Planning Authorization** decision record (not in baseline — future governance) |
| T-13 | `P2_AUTHORIZED` → `TERMINATED` | Program closure decision record (future) |
| T-14 | Any active state → `DEFERRED` | Explicit deferral decision record naming source state |
| T-15 | Any active state → `REJECTED` | Explicit rejection decision record |
| T-16 | Any state → `TERMINATED` | Explicit program closure decision record |

**No other transitions are valid.**

---

## 4. INVALID_TRANSITION_RULES

The following are **explicitly prohibited**:

| Rule ID | Prohibition |
| ------- | ----------- |
| IV-01 | Deriving canonical lifecycle state from commentary, recommendations, or "readiness" narrative in nomination draft, chat analysis, or checklist notes |
| IV-02 | Treating P2 Governance Readiness Gate analysis (`NOT_READY_FOR_P2`) as P2 authorization or as denial of Design Approval |
| IV-03 | Treating Governance Exit Decision (`PROCEED_TO_DESIGN_AUTHORIZATION_REQUEST`) as Design Approval |
| IV-04 | Treating `spec11-design-authorization-request.md` as approval because it exists or is "complete" |
| IV-05 | Treating Design Approval (`DESIGN_APPROVED` / `DESIGN_APPROVED_WITH_CONDITIONS`) as P2 authorization |
| IV-06 | Treating Design Approval as Implementation Authorization or execution unlock |
| IV-07 | Treating design-approval conditions C-01–C-06 as automatically satisfied because they are documented |
| IV-08 | Treating `tasks.md` `READINESS_OUTPUT`, `NEXT_STATE`, or footer blocks as authoritative canonical state when they conflict with header metadata or decision records |
| IV-09 | Treating `tasks.md` header `lifecycle_state: ARCHITECTURE_CLARIFIED` as current state after a superseding Design Approval Decision Record |
| IV-10 | Checking P-020–P-024 or any P4 task as authorization to implement or execute |
| IV-11 | Using `plan.md` "Next Planning Steps" or `architecture-clarification.md` §7.1 wave hypotheses as authorized waves |
| IV-12 | Unlocking schema, runtime, code, or spec10 mutation from any planning or governance artifact except explicit future Implementation Authorization |

---

## 5. CONFLICT_RESOLUTION_RULE

When artifacts disagree on lifecycle position, apply rules in order:

| Priority | Rule |
| -------- | ---- |
| **CR-01** | **Decision artifacts override all other artifacts** for canonical lifecycle state |
| **CR-02** | Among decision artifacts, the **latest dated** explicit `FINAL_DECISION` wins |
| **CR-03** | Request artifacts never override decision artifacts; after decision issued, request status is historical (`REQUESTED_NOT_APPROVED` at submission time only) |
| **CR-04** | Control artifacts (this file) resolve interpretation but do not supersede decision outcomes |
| **CR-05** | **Draft artifacts** (`*-draft.md`, authority NONE) are evidence-only; never state-bearing |
| **CR-06** | **Stale `tasks.md` signals**: header metadata block reflects planning milestone evidence; `READINESS_OUTPUT` / `NEXT_STATE` footer is **stale/non-authoritative** when inconsistent with `spec.md` §7, decision records, or this control artifact — do not use footer for canonical state |
| **CR-07** | **"Approved with conditions" ≠ "authorized"**: Design Approval with conditions grants design baseline only; P2, implementation, and execution each require separate authorization records |
| **CR-08** | **Open conditions ≠ open lifecycle state**: UD-11-01, UD-11-02, P-013, DL-03-C may remain open under `DESIGN_APPROVED_WITH_CONDITIONS` without reverting to `ARCHITECTURE_CLARIFIED` |
| **CR-09** | **spec.md header status** is evidentiary summary; if stale relative to decision record, decision record wins for governance state |

### Applied resolution (current baseline)

| Conflict | Resolution |
| -------- | ------------ |
| `tasks.md` header `ARCHITECTURE_CLARIFIED` vs Design Approval Decision Record | Decision record wins → `DESIGN_APPROVED_WITH_CONDITIONS` |
| `tasks.md` footer `PLANNING_INITIALIZATION` / `ready_for_governance_review: no` | Stale per CR-06; non-authoritative |
| DAR `REQUESTED_NOT_APPROVED` vs Design Approval issued | Decision supersedes request pending status |
| P-031 unchecked vs Design Approval Decision Record | P-031 satisfied by Design Approval Decision Record per decision §5 downstream effect |

---

## 6. CURRENT_STATE_DETERMINATION

### Current canonical state

**`DESIGN_APPROVED_WITH_CONDITIONS`**

### Why this is the current state

| Evidence | Fact |
| -------- | ---- |
| Design Approval Decision Record (2026-07-03) | `FINAL_DECISION: DESIGN_APPROVED_WITH_CONDITIONS` — sole state-bearing outcome |
| CR-01 / CR-02 | Decision record overrides `tasks.md` header `ARCHITECTURE_CLARIFIED` and stale footer |
| DAR | Historical request only; superseded by decision |
| `plan.md` E-08 | P2 prerequisite (design approval) now satisfied **subject to conditions** |

### Open conditions (from Design Approval Decision Record)

| ID | Condition | Status |
| -- | --------- | ------ |
| **C-01** | UD-11-01 disposition before P2 technical planning authorization | **OPEN** |
| **C-02** | UD-11-02 disposition before security-reporting contract scope finalized in P2 | **OPEN** |
| **C-03** | P-013 complete or E-04 explicitly deferred in P2 authorization | **OPEN** |
| **C-04** | DL-01–DL-03 and `architecture-clarification.md` remain binding | **ONGOING** |
| **C-05** | DL-03-C deferred to P2 only | **OPEN** (deferred, not blocking design state) |
| **C-06** | P-033 verification before boundary-affecting PRs | **OPEN** |

### Open planning items (evidence — not lifecycle state)

| ID | Status | Source |
| -- | ------ | ------ |
| UD-11-01 | OPEN (default: extend `audit.read`) | `decision-log.md` |
| UD-11-02 | OPEN | `decision-log.md` |
| P-013 | OPEN (unchecked) | `tasks.md` |
| DL-03-C | DEFERRED to P2 | `decision-log.md` |
| P-020–P-024 | Not started; unauthorized | `tasks.md` |
| P-030 | Not complete | `tasks.md` |
| P-032–P-033 | Not started | `tasks.md` |
| P4 E-01–E-08 | HALT | `tasks.md` |

### Blocked

| Item | Block reason |
| ---- | ------------ |
| P2 technical planning execution (P-020–P-024) | No P2 Technical Planning Authorization; state not `P2_AUTHORIZED` |
| Implementation planning (P-032) | No Implementation Authorization; P2 incomplete |
| Execution (P4, code, schema, runtime) | `executable: false`; no Implementation Authorization |
| spec10 mutation | Frozen predecessor — all paths |

### Not blocked

| Item | Basis |
| ---- | ----- |
| Governance record preparation for P2 Technical Planning Authorization | Design Approval prerequisite met (with conditions) |
| Condition disposition (C-01–C-03) | Required or scopeable in P2 authorization |
| Evidence artifact updates that do not imply authorization | Planning hygiene only — no implementation |
| P-030 formal nomination record filing | Optional governance hygiene; not a blocker per decision record |

---

## 7. NEXT_ELIGIBLE_PROJECT_STEP

### Exactly one next eligible step

**Prepare and submit a P2 Technical Planning Authorization governance record** to request explicit authorization of P-020–P-024 only.

### Required predecessor conditions

| # | Condition | Satisfied? |
| - | --------- | ---------- |
| 1 | Canonical state = `DESIGN_APPROVED_WITH_CONDITIONS` or `DESIGN_APPROVED` | **Yes** |
| 2 | Design Approval Decision Record exists | **Yes** |
| 3 | C-01–C-03 disposition **recorded in the P2 authorization request** or closed before submission | **Required at submission** — may defer E-04 via C-03 explicit scoping |
| 4 | spec10 remains frozen | **Yes** — invariant |

### Required artifact type for this step

**Request or Decision artifact** — P2 Technical Planning Authorization record (governance-only; not yet in baseline).

### What this next step does NOT authorize

| Exclusion | Confirmed |
| --------- | --------- |
| P2 task execution without authorization grant in that record | **Yes** |
| Implementation Authorization | **Yes** |
| Execution, code, schema, migrations, runtime | **Yes** |
| spec10 mutation or contract extension | **Yes** |
| Automatic satisfaction of C-01–C-06 | **Yes** — conditions remain until explicitly closed |
| Wave A–E implementation per `architecture-clarification.md` §7.1 | **Yes** — hypothesis only |

---

## 8. SYSTEM_LOCK_RULES

| Lock ID | Rule |
| ------- | ---- |
| SL-01 | No new governance layer (gate, chain, or authority type) may be introduced for spec11 unless **this control artifact is explicitly amended** by a future governance record |
| SL-02 | No transition to `P2_AUTHORIZED` without an explicit **P2 Technical Planning Authorization** decision record |
| SL-03 | No implementation planning progression from Design Approval alone — Implementation Authorization is a separate record per `spec.md` §5 rule 2 |
| SL-04 | No execution from any artifact in the current chain (`spec.md` through this control file) |
| SL-05 | spec10 remains **CLOSED / FROZEN** unless separately changed by governance outside spec11 |
| SL-06 | Canonical state may change only via transitions in §3 |
| SL-07 | Conditions bind until closed by decision record or explicit scoping in the next authorized governance record |
| SL-08 | This control artifact does not grant authority status beyond **NONE** |

---

## 9. NON_SCOPE

This canonical governance transition control artifact explicitly does **NOT**:

| Assertion | Confirmed |
| --------- | --------- |
| Authorize P2 technical planning | **Yes** — defines eligibility only |
| Authorize implementation | **Yes** |
| Authorize execution | **Yes** |
| Define schema | **Yes** |
| Define runtime behavior | **Yes** |
| Modify spec10 | **Yes** |
| Reopen planning already closed (P0, P1) | **Yes** |
| Create a new review cycle by itself | **Yes** |
| Issue Design Approval | **Yes** — records existing decision only |
| Close conditions C-01–C-06 automatically | **Yes** |
| Generalize to other specs | **Yes** — spec11 only |

---

**End of canonical governance transition control. Single artifact. Authority NONE. Canonical state: `DESIGN_APPROVED_WITH_CONDITIONS`. Next eligible step: P2 Technical Planning Authorization governance record.**
