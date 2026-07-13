---
artifact: spec06_core_wave_inclusion_decision
decision_type: WAVE_MEMBERSHIP_DECISION
status: DECISION_RECORDED
authority: GOVERNANCE_PLANNING
execution_authority: none
mutation_permission: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
spec06_wave_decision: SPEC06_REMAINS_DEFERRED
recommended_next_gate: CORE_COMPLETION_WAVE_HYGIENE_PASS
upstream_auth_refresh: .specify/docs/spec04/spec04-auth-residual-post-binding-status-refresh.md
upstream_workflow_decision: WORKFLOW_REMAINS_DEFERRED
upstream_wave_plan: .specify/docs/planning/core-completion-wave-plan.md
date: 2026-07-13
---

# Spec06 Core Completion Wave Inclusion Decision

**Artifact type:** Governance / planning inclusion decision (non-authorizing)  
**Status:** `DECISION_RECORDED`  
**Mutation permission:** `none`  
**Execution authority:** `none`

**Upstream:** Auth residual refresh recommended `SPEC06_CORE_WAVE_INCLUSION_DECISION`; Core Completion Wave Plan W3.

This artifact decides whether Spec06 enters the Core Completion Wave planning path or remains deferred. It does **not** authorize Lottery implementation, contracts, UI, Spec02 reopen, Spec03 reopen, Spec04 residual reopen, Workflow reactivation, role mapping, OA-02-01, or Dormitory UI work.

---

## 1. Decision Baseline

| Topic | Settled posture |
| ----- | --------------- |
| Spec02 bounded packet | `SPEC02_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_COMPLETED` — closed |
| Overall Spec02 | **Frozen — Wave 1A Complete** |
| Spec03 | `SPEC03_CLOSED` |
| Spec04 Assignability / Check-in | CLOSED / RETIRED |
| Spec04 Auth residual refresh | `COMPLETED` — Application PEP closed; role mapping / Presentation / HTTP remainder open; Dormitory UI product auth blocked |
| Workflow | `WORKFLOW_REMAINS_DEFERRED` |
| Core Completion Wave Plan | `WAVE_PLAN_COMPLETED` |
| Spec06 catalog | `IMPLEMENTATION_COMPLETE_GOVERNANCE_OPEN` — Documented Exception (Option B) |
| Spec06 regularization | `REGULARIZATION_COMPLETE` — mission closed; authority gap remains |
| Spec06 authority | `AUTHORITY_NOT_AVAILABLE` — **hold** new Lottery implementation until separate authorization |
| Spec07 / Spec08 | Fully Closed — already consumed historical Lottery delivery |

Settled baseline is not reopened or reinterpreted.

---

## 2. Spec06 Decision Context

This decision controls **wave membership only**:

- whether Spec06 is treated as an in-wave candidate for later non-executing governance/sequencing treatment, or  
- whether the Core Completion Wave proceeds without Spec06 as an active path member  

It does **not** invent product-core status, resolve the Domain Authority Gap, or imply implementation readiness.

Why now: Wave Plan W3 and Auth residual refresh named this gate so Spec06 is not left as an unresolved membership assumption before hygiene and stream selection.

---

## 3. Inclusion vs Deferral Assessment

### Evaluation criteria

| Lens | Finding |
| ---- | ------- |
| **Product-core necessity** | No recorded product assertion that Lottery is required for the **current** Core Completion Wave. Wave focus evidence centers on Spec02 structure Auth binding aftermath, Spec04 Auth remainder clarity, Workflow deferral, hygiene, and blocked Dormitory UI — not Lottery completion. Spec06 delivery evidence already exists; what remains open is **governance debt / authority gap**, not a missing core lottery engine for this wave. |
| **Dependency reality** | No in-wave candidate (Auth remainder disposition prep, hygiene, blocked UI readiness tracking, Workflow-deferred posture) is blocked on Spec06 inclusion. Spec07/Spec08 already closed against historical Lottery. |
| **Governance clarity** | Regularization is **complete**. Remaining Spec06 openness is `AUTHORITY_NOT_AVAILABLE` + Governance Open — not a clean, bounded core-wave completion packet. Including Spec06 without product “Lottery in Core Wave?” authority would create ambiguous scope (authority resolution vs new impl vs false closure). |
| **Readiness posture** | Inclusion would create **false execution pressure** toward Spec06 authority resolution or new Lottery work while Auth/UI product paths remain blocked and no product Lottery mandate is evidenced. |
| **Sequencing value** | Including Spec06 now distracts from foundational Core Completion Wave work (hygiene → stream selection around Auth remainder / product-auth-gated UI). |
| **Deferment safety** | Wave can progress coherently: hygiene pass → stream selection; Spec06 remains selectable later under separate product/authority decisions. |

### Option A — `SPEC06_INCLUDED_IN_CORE_COMPLETION_WAVE`

| Pros | Cons |
| ---- | ---- |
| Keeps Lottery governance debt visible in-wave | No evidenced product-core necessity for this wave |
| | Regularization already complete — “continuation” is not a clear packet |
| | Authority gap makes inclusion → later gates ambiguous |
| | Risks false readiness / execution pressure |

### Option B — `SPEC06_REMAINS_DEFERRED`

| Pros | Cons |
| ---- | ---- |
| Aligns with hold on new Lottery impl + no product Lottery-in-wave mandate | Spec06 governance debt stays outside active wave path |
| Protects wave focus on Auth residual aftermath / hygiene / selection | Must not be read as Spec06 cancelled or Fully Closed |
| Safe: no current stream blocked | Future Lottery work still needs separate product + authority resolution |

**Assessment:** Option B is governance-valid now.

---

## 4. Dependency and Sequence Impact

| Path / candidate | Effect of Spec06 deferral | If included instead |
| ---------------- | ------------------------- | ------------------- |
| Spec04 Auth remainder (role mapping / Presentation / HTTP) | **Unaffected** | Unaffected but competes for attention |
| Dormitory UI readiness | Still **blocked** on product auth + Auth remainder | Still blocked — Spec06 does not unblock |
| Workflow | Remains deferred | Remains deferred |
| Wave hygiene (Spec01 / Spec05 / catalog) | **Unaffected** — may proceed | May be delayed |
| Core stream selection | Proceeds without Spec06 as active member | Would force Spec06 eligibility framing without product mandate |
| Spec06 new Lottery implementation | Remains **held** (`AUTHORITY_NOT_AVAILABLE`) | Still held — inclusion ≠ IA |
| Spec06 regularization | Already complete — no reopen | Must not reopen as incomplete |
| Spec02 / Spec03 / closed Spec04 residuals | Must remain closed | Must remain closed |

---

## 5. Risks and Boundaries

### Inclusion risks (rejected path)

- Treating governance-open Lottery as a core-wave deliverable without product mandate  
- Creating false readiness toward authority invention or new Lottery coding  
- Diluting focus from Spec04 Auth residual aftermath and wave hygiene  
- Ambiguous “continuation” after `REGULARIZATION_COMPLETE`  

### Deferment risks (accepted, mitigated)

- Spec06 Domain Authority Gap remains open outside the active wave — **acceptable** for this wave; tracked in catalog as Documented Exception  
- Must not claim Spec06 Fully Closed or cancel future Lottery authority resolution  

### Hard boundaries preserved

- No Spec06 / Lottery implementation, contracts, or UI  
- No Spec02 reopen; Spec02 remains Frozen — Wave 1A Complete  
- No Spec03 reopen  
- No Spec04 Assignability / Check-in reopen  
- Spec04 Auth residual stays clarified, not execution-authorized  
- Workflow remains deferred  
- No role mapping / OA-02-01 / Dormitory UI authorization  
- No claim of full authorization or Dormitory UI readiness  
- No product authority invented for “Lottery is core”  

---

## 6. Decision

```text
SPEC06_REMAINS_DEFERRED
```

**Answers to required questions:**

| Q | Answer |
| - | ------ |
| A. Current need | **No** — not needed now for Core Completion Wave |
| B. Dependency effect | Auth / UI / Workflow / hygiene unaffected; Spec06 new impl remains held either way |
| C. Inclusion risk | False execution pressure + ambiguous post-regularization scope without product mandate |
| D. Deferment risk | Authority gap stays out-of-wave — safe and already catalog-tracked |
| E. Governance validity | `SPEC06_REMAINS_DEFERRED` |
| F. Next gate | `CORE_COMPLETION_WAVE_HYGIENE_PASS` |

**Future Spec06 consideration:** Requires separate **product** decision (Lottery priority) **and** Domain Authority resolution / map-backed authorization before any execution prep. Inclusion here is **not** a substitute for those authorities.

---

## 7. Next Gate

```text
CORE_COMPLETION_WAVE_HYGIENE_PASS
```

**Why:** Membership decisions for Workflow (deferred) and Spec06 (deferred) plus Auth residual refresh are complete. Per Core Completion Wave Plan, the next non-executing gate is hygiene (Spec01 closure review / Spec05 closeout readiness / catalog mirror of wave decisions) before stream selection.

---

## Required Final Decision Block

```text
SPEC06_CORE_WAVE_INCLUSION_DECISION

Decision:
SPEC06_REMAINS_DEFERRED

Wave Membership:
Spec06 OUT of Core Completion Wave path

Spec06 Regularization:
ALREADY COMPLETE — not reopened

Spec06 Authority:
AUTHORITY_NOT_AVAILABLE — new impl remains held

Execution Authority:
NONE

Spec02 / Spec03 / Closed Spec04 Residuals / Workflow:
NOT REOPENED / WORKFLOW REMAINS DEFERRED

Recommended Next Gate:
CORE_COMPLETION_WAVE_HYGIENE_PASS
```

---

## Explicit Non-Authorization

This decision does **not** authorize:

- Spec06 / Lottery implementation, Feature Contracts, or technical design  
- application, test, contract, UI, lottery, or authorization implementation file changes  
- Spec02 unfreeze; role mapping; OA-02-01; Dormitory UI  
- Spec06 Domain Authority invention or Full Closure  
- Core Completion Wave stream selection or Implementation Authorization  

---

## No-Change Confirmation

`No application, test, contract, UI, lottery, or authorization implementation files were modified.`

Only this decision artifact was created:

- `.specify/docs/decisions/spec06-core-wave-inclusion-decision.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`DECISION_RECORDED`** / **`SPEC06_REMAINS_DEFERRED`**  
- Recommended next gate: **`CORE_COMPLETION_WAVE_HYGIENE_PASS`**  
- Last Updated: 2026-07-13  
- Checkpoint: `spec06-core-wave-inclusion-decision`
