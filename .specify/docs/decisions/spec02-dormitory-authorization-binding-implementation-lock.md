---
artifact: spec02_dormitory_authorization_binding_implementation_lock
status: IMPLEMENTATION_LOCK_CREATED
authority: HUMAN_GRANTED
scope: BOUNDED_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_ONLY
mode: EXECUTION_BOUNDARY_DEFINITION
date: 2026-07-13
---

# Spec02 Dormitory Authorization Binding — Implementation Lock

**Artifact type:** Execution boundary definition (non-executing)  
**Human IA:** `.specify/docs/decisions/spec02-dormitory-human-implementation-authorization-decision.md` (`AUTHORIZE_IMPLEMENTATION` / `HUMAN_GRANTED`)  
**Request:** `.specify/docs/decisions/spec02-dormitory-authorization-implementation-authorization-request.md`  
**Vocabulary:** `.specify/docs/spec02/spec02-dormitory-formal-permission-vocabulary-definition.md`  
**Contract review:** `.specify/docs/spec02/spec02-dormitory-permission-contract-review-record.md` (`CONTRACT_ACCEPTED`)

This lock freezes **what may be implemented** under granted authority. It does **not** perform implementation, modify code, create policies/middleware, assign roles, expand vocabulary, or reopen Spec04 Assignability / Check-in residuals.

---

## A. Authorized Execution Scope

Implementation work is allowed **only** for:

| Element | Bound |
| ------- | ----- |
| Permission keys | Bind **only** `dormitory.structure.view` and `dormitory.structure.manage` |
| Action coverage | Approved Dormitory structure actions only — `#1–#8` (`manage`), `#12–#17` (`view`) |
| Enforcement location | `APPLICATION_LAYER_PEP` on those covered Application contract paths |
| Unresolved actions `#9–#11`, `#18–#21` | Remain undefined in vocabulary; behavior `DENY_BY_DEFAULT_UNTIL_RESOLVED` (must not inherit structure keys) |
| Role mapping | Remains `DEFERRED` — no grants in this execution |

No additional scope may be inferred from adjacency, Integration bridges, Presentation placeholders, or Spec04 plan candidates.

---

## B. Permitted Change Categories

| Category | Allowance |
| -------- | --------- |
| Application-layer authorization binding logic | **Allowed** — PEP checks for covered structure actions |
| Application-layer authorization enforcement | **Allowed** — deny when principal lacks required approved key |
| Seed/configuration for approved vocabulary only | **Allowed** — register/activate **only** `dormitory.structure.view` and `dormitory.structure.manage` in Spec02 permission registry |
| Minimal supporting tests | **Allowed** — cover allow/deny for covered actions; deny-by-default for unresolved where in scope of tests |
| Minimal configuration updates | **Allowed** — only if strictly required by the binding |

### Constraints on permitted changes

- No new permission seed entries beyond the two approved keys  
- No role-permission assignment seed changes  
- No user-role mapping seeds  
- No permission renaming or aliasing  

---

## C. Prohibited Change Categories

Explicitly prohibited under this lock:

- permission vocabulary changes (add/rename/remove keys; alter coverage)  
- role mapping implementation  
- role assignment implementation  
- RBAC redesign / broad Spatie refactor  
- Allocation authorization work  
- Check-in authorization work  
- UI / Presentation authorization expansion  
- unrelated refactoring  
- architectural restructuring  
- seed expansion beyond approved vocabulary activation  
- new permission creation  
- middleware creation (unless a later separate authorization exists — **not** granted here)  
- Laravel Policy classes outside the application-layer PEP binding pattern required for this packet  
- Spec04 Assignability residual reopen  
- migrations (unless separately authorized — **not** granted here)  

---

## D. Execution Target Boundary

### Allowed class of targets (conceptual — not concrete file list)

| Class | Boundary |
| ----- | -------- |
| Application-layer authorization boundary | Dormitory Application services/contracts that implement covered structure mutations/reads (`#1–#8`, `#12–#17`), plus Spec02-owned permission registry seed/config limited to the two approved keys |
| Supporting tests | Feature/unit tests that verify the binding without expanding product scope |
| Identity permission check consumption | Use of existing Spec02 check APIs (e.g. `IdentityUserReadContract::userHasPermission`) — no new platform Auth redesign |

### Forbidden domains / bounded contexts

| Forbidden | Reason |
| --------- | ------ |
| Allocation module authorization / assignability reopen | Spec04 Assignability closed; unresolved `#18–#20` |
| CheckIn / CheckOut authorization | Check-in residual closed; Spec07 Operator pattern out of packet |
| Request Integration auth for `#21` | Unresolved; deny-by-default |
| Dormitory Presentation / Livewire / HTTP UI auth expansion | Not authorized in this packet |
| Lottery, Notification, Reporting, Employee (except incidental Identity read API use) | Out of authorized scope |
| Spec02 Wave reopen beyond registering the two keys + consumer PEP | No broad Identity unfreeze |

**Constraint:** This lock does **not** authorize a concrete file list. Actual file selection must be derived during implementation planning or execution preparation, and must stay inside the classes above.

---

## E. Validation Requirements

Mandatory validation expectations for any execution under this lock:

1. Approved permission keys remain unchanged (`dormitory.structure.view`, `dormitory.structure.manage`).  
2. Unresolved actions remain denied by default and are not granted via structure keys.  
3. Role mapping remains deferred (no role→permission attachment).  
4. Allocation scope remains untouched.  
5. Check-in scope remains untouched.  
6. Authorization behavior remains bounded to approved structure actions `#1–#8` / `#12–#17`.  
7. Enforcement remains at `APPLICATION_LAYER_PEP` (not UI-only, not middleware-first expansion).  
8. Spec04 Assignability and Check-in residuals remain closed / not reopened.  

---

## F. Acceptance Criteria

Implementation may be accepted **only if** all remain true:

| Criterion | Required |
| --------- | -------- |
| Permission vocabulary unchanged | Yes |
| Authorization scope unchanged | Yes — covered structure actions only |
| Enforcement location matches approved decision | `APPLICATION_LAYER_PEP` |
| No forbidden areas modified | Yes |
| No unauthorized permission expansion | Yes |
| Role mapping still deferred | Yes |
| Unresolved actions still deny-by-default | Yes |
| Scope integrity preserved | `CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED` |

---

## G. Execution Readiness Result

`READY_FOR_IMPLEMENTATION_EXECUTION`

**Rationale:** Human IA is `HUMAN_GRANTED` for `BOUNDED_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_ONLY`; vocabulary and contract are locked; placement, seed (keys only), role deferral, and unresolved deny-by-default are fixed; this lock freezes permitted/prohibited categories and validation/acceptance gates. No further governance unlock is required before bounded execution planning/implementation begins within these boundaries.

---

## Required Final Decision Block

```text
SPEC02_DORMITORY_AUTHORIZATION_BINDING_IMPLEMENTATION_LOCK

Implementation Authority:
HUMAN_GRANTED

Authorized Scope:
BOUNDED_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_ONLY

Permission Vocabulary:
LOCKED

Role Mapping:
DEFERRED

Enforcement Placement:
APPLICATION_LAYER_PEP

Unresolved Actions:
DENY_BY_DEFAULT_UNTIL_RESOLVED

Execution Boundary:
FROZEN

Execution Readiness:
READY_FOR_IMPLEMENTATION_EXECUTION

Allocation Scope:
CLOSED

Check-in Scope:
CLOSED

Spec04 Boundary:
NOT_REOPENED

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## Explicit Non-Execution

This lock does **not** modify code, run seeds, create migrations, policies, middleware, or role grants. It only freezes execution boundaries for a subsequent implementation step.

---

## No-Change Confirmation

`No application, test, catalog, vocabulary, contract, readiness, IA, or other files were modified.`

Only this lock artifact was created:

- `.specify/docs/decisions/spec02-dormitory-authorization-binding-implementation-lock.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`IMPLEMENTATION_LOCK_CREATED`** / **`READY_FOR_IMPLEMENTATION_EXECUTION`**  
- Last Updated: 2026-07-13
