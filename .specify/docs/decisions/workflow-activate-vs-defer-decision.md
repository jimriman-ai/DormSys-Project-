---
artifact: workflow_activate_vs_defer_decision
decision_type: WAVE_MEMBERSHIP_DECISION
status: DECISION_RECORDED
authority: GOVERNANCE_PLANNING
execution_authority: none
mutation_permission: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
workflow_decision: WORKFLOW_REMAINS_DEFERRED
recommended_next_gate: SPEC04_AUTH_RESIDUAL_POST_BINDING_STATUS_REFRESH
upstream_plan: .specify/docs/planning/core-completion-wave-plan.md
date: 2026-07-13
---

# Workflow Activate vs Defer Decision — Core Completion Wave

**Artifact type:** Governance / product / planning decision (non-authorizing)  
**Status:** `DECISION_RECORDED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Upstream:** `.specify/docs/planning/core-completion-wave-plan.md` (`RECOMMENDED_NEXT_GATE: WORKFLOW_ACTIVATE_VS_DEFER_DECISION`)

This artifact decides whether Workflow enters the Core Completion Wave planning path or remains deferred. It does **not** authorize Workflow implementation, contracts, engine design, UI work, Spec02 reopen, Spec03 reopen, Spec04 residual reopen, Spec06 implementation, role mapping, or OA-02-01.

---

## 1. Decision Baseline

| Topic | Settled posture |
| ----- | --------------- |
| Spec02 bounded packet | `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` |
| Overall Spec02 | **Frozen — Wave 1A Complete** |
| Spec03 | `SPEC03_CLOSED` |
| Spec04 Assignability / Check-in | CLOSED / RETIRED |
| Spec04 Auth remainder | OPEN only at UI / role-mapping / HTTP / broader auth surface |
| Core Completion Wave Plan | `WAVE_PLAN_COMPLETED` |
| CD-010 | Request owns approval **state**; Workflow (deferred) owns transition **rules** when activated |
| Catalog Workflow posture | Deferred until activation criteria are met |

Settled baseline is not reopened or reinterpreted here.

---

## 2. Workflow Decision Context

This decision controls **wave membership only**:

- whether Workflow is treated as an in-wave candidate for later readiness/contract/governance treatment, or  
- whether the wave proceeds without Workflow activation  

It does **not** control technical design, Implementation Authorization, or coding.

Why now: Core Completion Wave Plan placed `WORKFLOW_ACTIVATE_VS_DEFER_DECISION` as the first ordered gate so later Auth/Spec06/hygiene sequencing is not distorted by an unresolved Workflow membership assumption.

---

## 3. Activation vs Deferral Assessment

### Evaluation criteria

| Lens | Finding |
| ---- | ------- |
| **Product necessity** | No evidenced Core Completion Wave path currently requires a Workflow **module** to deliver next value. Immediate value paths are Spec04 Auth remainder disposition, Spec06 inclusion decision, hygiene, and (later) product-authorized UI — none require Workflow engine extraction now. |
| **Dependency reality** | Auth remainder, Spec06 regularization continuation, Spec01/Spec05 hygiene, and Dormitory UI product-auth readiness are **unaffected** by Workflow activation. Request approval **entities** already exist under Spec05 / CD-010 without Workflow module activation. |
| **Governance readiness** | Catalog activation criteria remain unmet as a recorded bar: ≥2 implemented multi-stage workflows; shared transition/state behavior across modules; reusable engine justified by concrete duplication. Activating without those criteria would invent wave scope. |
| **Sequencing impact** | Activation now adds premature complexity (engine/contract/IA path) without unblocking Auth refresh or Spec06 inclusion. Deferral keeps the wave coherent. |
| **Deferment safety** | Wave can progress valuably with Workflow deferred: Auth post-binding status refresh → Spec06 inclusion → hygiene → later stream selection. |

### Option A — `WORKFLOW_ACTIVATED_FOR_CORE_COMPLETION_WAVE`

| Pros | Cons |
| ---- | ---- |
| Signals future orchestration intent | Activation criteria not met |
| | No current stream blocked on Workflow |
| | Risks premature contract/engine work without product mandate |
| | Dilutes focus from Spec04 Auth remainder after Spec02 structure binding |

### Option B — `WORKFLOW_REMAINS_DEFERRED`

| Pros | Cons |
| ---- | ---- |
| Aligns with CD-010 + catalog deferral | Workflow UI/engine remain unavailable until later activation |
| Keeps wave focused on Auth remainder / Spec06 / hygiene | Must not be mistaken as permanent cancellation |
| Does not block coherent completion sequencing | Future multi-stage orchestration still needs a later activation decision when criteria are met |

**Assessment:** Option B is governance-valid now.

---

## 4. Dependency Impact

| Wave candidate | Effect of deferral | Effect if activated instead |
| -------------- | ------------------ | --------------------------- |
| Spec04 Auth post-binding status refresh | **Unaffected** — may proceed | Unaffected, but competes for attention |
| Spec06 Core Wave inclusion decision | **Unaffected** | Unaffected |
| Wave hygiene | **Unaffected** | Unaffected |
| Dormitory UI readiness | Still **blocked** on product auth (not Workflow) | Still blocked on product auth |
| Request Dependent live-path | Still conditional / IRG — not Workflow-gated | Same |
| Request approval mutation UI / Workflow UI | Remains deferred with Workflow | Would enter wave as later readiness candidate only (still no IA here) |
| Spec02 / Spec03 / closed Spec04 residuals | Must remain closed | Must remain closed |

---

## 5. Risks and Boundaries

### Activation risks (rejected path)

- Activating without catalog activation criteria  
- Creating ambiguous Workflow scope inside Core Completion Wave  
- Sliding into engine/contract/implementation without separate authority  
- Distracting from Spec04 Auth remainder after Spec02 structure PEP closeout  

### Deferment risks (accepted, mitigated)

- Multi-stage approval orchestration remains deferred — **acceptable** because Request retains approval state ownership (CD-010) and no current wave stream is blocked  
- Must not treat deferral as “Workflow cancelled forever” — reactivation requires later explicit decision when criteria/product need exist  

### Hard boundaries preserved

- No Workflow implementation / contracts / engine design  
- No Spec02 reopen; Spec02 remains Frozen — Wave 1A Complete  
- No Spec03 reopen  
- No Spec04 Assignability / Check-in reopen  
- No UI / role mapping / OA-02-01 / Spec06 implementation authorization  
- No claim of Workflow technical readiness  
- No claim of full RBAC / UI auth / role mapping completion  

---

## 6. Decision

```text
WORKFLOW_REMAINS_DEFERRED
```

**Answers to required questions:**

| Q | Answer |
| - | ------ |
| A. Current need | **No** — not needed now for Core Completion Wave |
| B. Dependency effect | Auth / Spec06 / hygiene unaffected; Workflow-dependent UI remains deferred with Workflow |
| C. Activation risk | Premature scope + unmet activation criteria + distraction from Auth remainder |
| D. Deferment risk | Orchestration stays deferred — safe given CD-010 Request state ownership and no current blocker |
| E. Recommended decision | `WORKFLOW_REMAINS_DEFERRED` |
| F. Next gate | `SPEC04_AUTH_RESIDUAL_POST_BINDING_STATUS_REFRESH` |

---

## 7. Next Gate

```text
SPEC04_AUTH_RESIDUAL_POST_BINDING_STATUS_REFRESH
```

**Why this follows immediately:** Workflow is not the true blocker. Spec04 Auth remainder (UI / role-mapping / HTTP / broader surface) remains the open Spec04 Product residual after Spec02 Application structure PEP binding closed. Spec06 inclusion decision remains in the wave sequence afterward (per Core Completion Wave Plan), but Auth residual status refresh is the highest-value immediate non-executing gate.

Wave Plan order listed Spec06 before Auth; this decision’s sequence-sensitivity note allows Auth refresh next when Workflow is deferred as non-blocker. Spec06 inclusion is **not cancelled** — it remains the subsequent membership decision after Auth refresh unless a later selection revises order.

---

## Required Final Decision Block

```text
WORKFLOW_ACTIVATE_VS_DEFER_DECISION

Decision:
WORKFLOW_REMAINS_DEFERRED

Wave Membership:
Workflow OUT of Core Completion Wave execution-preparation path

Catalog / CD-010 Alignment:
DEFERRED UNTIL ACTIVATION CRITERIA MET

Execution Authority:
NONE

Spec02 / Spec03 / Closed Spec04 Residuals:
NOT REOPENED

Recommended Next Gate:
SPEC04_AUTH_RESIDUAL_POST_BINDING_STATUS_REFRESH
```

---

## Explicit Non-Authorization

This decision does **not** authorize:

- Workflow module implementation, contracts, or engine design  
- application, test, contract, or authorization implementation file changes  
- UI / role mapping / OA-02-01 / Spec06 coding  
- Spec02 unfreeze or Spec03 / Spec04 closed residual reopen  

---

## No-Change Confirmation

`No application, test, contract, or authorization implementation files were modified.`

Only this decision artifact was created:

- `.specify/docs/decisions/workflow-activate-vs-defer-decision.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`DECISION_RECORDED`** / **`WORKFLOW_REMAINS_DEFERRED`**  
- Recommended next gate: **`SPEC04_AUTH_RESIDUAL_POST_BINDING_STATUS_REFRESH`**  
- Last Updated: 2026-07-13  
- Checkpoint: `workflow-activate-vs-defer-decision`
