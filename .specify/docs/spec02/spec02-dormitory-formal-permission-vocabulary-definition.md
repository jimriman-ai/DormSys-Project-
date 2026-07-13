---
artifact: spec02_dormitory_formal_permission_vocabulary_definition
status: DEFINITION_RECORDED
mutation_permission: none
execution_authority: none
operating_mode: VOCABULARY_CONTRACT_ONLY
granularity_mode: COARSE
authority_source: HUMAN_APPROVED_DECISION
decision: FORMAL_VOCABULARY_DEFINED
date: 2026-07-13
---

# Spec02 Dormitory Formal Permission Vocabulary Definition

**Artifact type:** Vocabulary contract (definition only)  
**Controlling authority:** `.specify/docs/decisions/spec02-dormitory-permission-vocabulary-decision.md`  
**Status:** `DEFINITION_RECORDED`

This artifact formalizes the human-approved Dormitory permission keys, their finite action coverage, and exclusion boundaries. It does **not** authorize implementation, seeding, migrations, middleware, policies, Gates, role assignment, UI guards, or Spec02 unfreeze beyond this vocabulary contract.

---

## A. Current Context

| Prior step | Outcome |
| ---------- | ------- |
| Spec04 protected-action enumeration | `ACTIONS_ENUMERATED` — **21** actions |
| Spec02 vocabulary discovery | `VOCABULARY_PARTIALLY_DEFINED` — 14 require definition; 7 insufficient evidence |
| Spec02 naming/granularity proposal | `PROPOSAL_READY_FOR_HUMAN_DECISION` — COARSE `module.resource.verb` |
| Human decision | `AUTHORIZE_FORMAL_PERMISSION_VOCABULARY_DEFINITION` — keys locked; granularity **COARSE** |

**Statement:** This artifact defines a **vocabulary contract only**. It does not authorize implementation.

---

## B. Decision Authority

**Source:** `.specify/docs/decisions/spec02-dormitory-permission-vocabulary-decision.md`

| Binding decision | Value |
| ---------------- | ----- |
| Naming Strategy | `ACCEPT_MODULE_RESOURCE_VERB` |
| Granularity | `COARSE` |
| Vocabulary Ownership | `SPEC02_CONFIRMED` |
| Unresolved Actions | `KEEP_UNRESOLVED` |
| Next Authorization (this step) | `AUTHORIZE_FORMAL_PERMISSION_VOCABULARY_DEFINITION` |

These values are authoritative and are not altered by this definition.

---

## C. Approved Permission Vocabulary

Only the two human-approved keys are defined.

### C.1 `dormitory.structure.view`

| Field | Value |
| ----- | ----- |
| Permission key | `dormitory.structure.view` |
| Naming basis | `ACCEPT_MODULE_RESOURCE_VERB` — `{module}.{resource}.{verb}` aligned with `identity.users.view` |
| Granularity basis | `COARSE` — one read key for catalog hierarchy browse (#12–#17) |
| Human approval source | `spec02-dormitory-permission-vocabulary-decision.md` §B |

### C.2 `dormitory.structure.manage`

| Field | Value |
| ----- | ----- |
| Permission key | `dormitory.structure.manage` |
| Naming basis | `ACCEPT_MODULE_RESOURCE_VERB` — aligned with `identity.users.manage` |
| Granularity basis | `COARSE` — one mutation key for catalog hierarchy create/status (#1–#8) |
| Human approval source | `spec02-dormitory-permission-vocabulary-decision.md` §B |

No additional Dormitory permission keys are defined or implied.

---

## D. Permission Boundary Definition

### Permission: `dormitory.structure.view`

**Included actions:**

| # | Action |
| - | ------ |
| 12 | List dormitories |
| 13 | Get dormitory detail |
| 14 | List buildings for dormitory |
| 15 | List floors for building |
| 16 | List rooms for floor |
| 17 | List beds for room |

**Explicitly excluded actions:**

| # | Action |
| - | ------ |
| 1–8 | All catalog structure mutations (covered only by `dormitory.structure.manage`) |
| 9 | Record bed occupancy start (Phase 3C local marker API) |
| 10 | Record bed occupancy end (Phase 3C local marker API) |
| 11 | Apply Allocation physical-state signal |
| 18 | Bed exists (assignability) |
| 19 | Is bed assignable |
| 20 | Get physical occupancy state |
| 21 | Site exists (Request dormitory existence) |

**Boundary rationale:**  
Actions #12–#17 are the finite Application read surface for browsing the Dormitory site→building→floor→room→bed catalog hierarchy (Manage Rooms browse). They share one human-admin read capability under COARSE grain. This permission is **not** “all Dormitory reads”; Integration assignability/existence reads (#18–#21) and all mutations are outside its included set.

**Future expansion prohibited unless:**  
A new governed human decision explicitly adds enumerated protected-action coverage to this key.

---

### Permission: `dormitory.structure.manage`

**Included actions:**

| # | Action |
| - | ------ |
| 1 | Create dormitory site |
| 2 | Create building |
| 3 | Create floor |
| 4 | Create room |
| 5 | Create bed |
| 6 | Change dormitory status |
| 7 | Change room status |
| 8 | Change bed operability / status |

**Explicitly excluded actions:**

| # | Action |
| - | ------ |
| 9 | Record bed occupancy start (Phase 3C local marker API) |
| 10 | Record bed occupancy end (Phase 3C local marker API) |
| 11 | Apply Allocation physical-state signal |
| 12–17 | All catalog structure reads (covered only by `dormitory.structure.view`) |
| 18 | Bed exists (assignability) |
| 19 | Is bed assignable |
| 20 | Get physical occupancy state |
| 21 | Site exists (Request dormitory existence) |

**Boundary rationale:**  
Actions #1–#8 are the finite Application mutation surface for creating hierarchy nodes and changing dormitory/room/bed operability status under Manage Rooms. Creates and status changes belong together under COARSE grain as one catalog-admin mutation capability. This permission is **not** “all remaining Dormitory mutations”; occupancy-marker APIs (#9–#10) and Allocation physical-state apply (#11) are outside its included set.

**Future expansion prohibited unless:**  
A new governed human decision explicitly adds enumerated protected-action coverage to this key.

---

## E. Action-to-Permission Coverage Table

| # | Action name | R/M | Coverage status | Permission key | Rationale |
| - | ----------- | --- | --------------- | -------------- | --------- |
| 1 | Create dormitory site | Mutation | `COVERED_BY_DORMITORY_STRUCTURE_MANAGE` | `dormitory.structure.manage` | Human-locked #1–#8 manage coverage |
| 2 | Create building | Mutation | `COVERED_BY_DORMITORY_STRUCTURE_MANAGE` | `dormitory.structure.manage` | Same |
| 3 | Create floor | Mutation | `COVERED_BY_DORMITORY_STRUCTURE_MANAGE` | `dormitory.structure.manage` | Same |
| 4 | Create room | Mutation | `COVERED_BY_DORMITORY_STRUCTURE_MANAGE` | `dormitory.structure.manage` | Same |
| 5 | Create bed | Mutation | `COVERED_BY_DORMITORY_STRUCTURE_MANAGE` | `dormitory.structure.manage` | Same |
| 6 | Change dormitory status | Mutation | `COVERED_BY_DORMITORY_STRUCTURE_MANAGE` | `dormitory.structure.manage` | Same |
| 7 | Change room status | Mutation | `COVERED_BY_DORMITORY_STRUCTURE_MANAGE` | `dormitory.structure.manage` | Same |
| 8 | Change bed operability / status | Mutation | `COVERED_BY_DORMITORY_STRUCTURE_MANAGE` | `dormitory.structure.manage` | Same |
| 9 | Record bed occupancy start (Phase 3C local marker API) | Mutation | `UNRESOLVED_NOT_FORMALLY_DEFINED` | — | `KEEP_UNRESOLVED` |
| 10 | Record bed occupancy end (Phase 3C local marker API) | Mutation | `UNRESOLVED_NOT_FORMALLY_DEFINED` | — | `KEEP_UNRESOLVED` |
| 11 | Apply Allocation physical-state signal | Mutation | `UNRESOLVED_NOT_FORMALLY_DEFINED` | — | `KEEP_UNRESOLVED` |
| 12 | List dormitories | Read | `COVERED_BY_DORMITORY_STRUCTURE_VIEW` | `dormitory.structure.view` | Human-locked #12–#17 view coverage |
| 13 | Get dormitory detail | Read | `COVERED_BY_DORMITORY_STRUCTURE_VIEW` | `dormitory.structure.view` | Same |
| 14 | List buildings for dormitory | Read | `COVERED_BY_DORMITORY_STRUCTURE_VIEW` | `dormitory.structure.view` | Same |
| 15 | List floors for building | Read | `COVERED_BY_DORMITORY_STRUCTURE_VIEW` | `dormitory.structure.view` | Same |
| 16 | List rooms for floor | Read | `COVERED_BY_DORMITORY_STRUCTURE_VIEW` | `dormitory.structure.view` | Same |
| 17 | List beds for room | Read | `COVERED_BY_DORMITORY_STRUCTURE_VIEW` | `dormitory.structure.view` | Same |
| 18 | Bed exists (assignability) | Read | `UNRESOLVED_NOT_FORMALLY_DEFINED` | — | `KEEP_UNRESOLVED` |
| 19 | Is bed assignable | Read | `UNRESOLVED_NOT_FORMALLY_DEFINED` | — | `KEEP_UNRESOLVED` |
| 20 | Get physical occupancy state | Read | `UNRESOLVED_NOT_FORMALLY_DEFINED` | — | `KEEP_UNRESOLVED` |
| 21 | Site exists (Request dormitory existence) | Read | `UNRESOLVED_NOT_FORMALLY_DEFINED` | — | `KEEP_UNRESOLVED` |

**Coverage counts:**

| Status | Count |
| ------ | ----- |
| Covered (`view` + `manage`) | **14** |
| `UNRESOLVED_NOT_FORMALLY_DEFINED` | **7** |
| `OUTSIDE_CURRENT_AUTHORIZED_VOCABULARY_SCOPE` | **0** |

All 21 enumerated actions are accounted for as either covered or unresolved. No action is classified outside the authorized vocabulary-scope accounting beyond the unresolved register.

---

## F. Unresolved Action Register

These actions are **intentionally excluded** from the current formal vocabulary contract per human decision `KEEP_UNRESOLVED`.

| # | Action name | Reason unresolved | Status | Definition rule |
| - | ----------- | ----------------- | ------ | --------------- |
| 9 | Record bed occupancy start (Phase 3C local marker API) | Dual human vs Allocation-driven path; insufficient Spec02 Auth mapping evidence | `KEEP_UNRESOLVED` | No permission vocabulary defined at this time |
| 10 | Record bed occupancy end (Phase 3C local marker API) | Same as #9 | `KEEP_UNRESOLVED` | No permission vocabulary defined at this time |
| 11 | Apply Allocation physical-state signal | System/Integration consumer; no Spec02 Integration permission vocabulary evidenced | `KEEP_UNRESOLVED` | No permission vocabulary defined at this time |
| 18 | Bed exists (assignability) | Allocation Integration consumer read | `KEEP_UNRESOLVED` | No permission vocabulary defined at this time |
| 19 | Is bed assignable | Allocation Integration consumer read | `KEEP_UNRESOLVED` | No permission vocabulary defined at this time |
| 20 | Get physical occupancy state | Allocation Integration consumer read | `KEEP_UNRESOLVED` | No permission vocabulary defined at this time |
| 21 | Site exists (Request dormitory existence) | Request Integration consumer read | `KEEP_UNRESOLVED` | No permission vocabulary defined at this time |

**Statement:** Unresolved actions are intentionally excluded from this formal vocabulary contract. Neither `dormitory.structure.view` nor `dormitory.structure.manage` covers them by implication.

---

## G. Vocabulary Integrity Constraints

1. Only human-approved keys (`dormitory.structure.view`, `dormitory.structure.manage`) are defined here.  
2. No additional Dormitory permission keys are authorized by implication (including Spec04 plan candidates such as `dormitory.view`, `dormitory.manage_structure`, `dormitory.manage_*`, or catch-all `dormitory.manage` / `dormitory.*`).  
3. Unresolved actions (#9–#11, #18–#21) remain undefined.  
4. Coarse permission keys must be interpreted **only** by their explicit included-action boundaries in §D — not as “all remaining Dormitory actions.”  
5. No implementation authority is granted by this artifact.  
6. Role assignment is out of scope.  
7. Policy enforcement design is out of scope.  
8. Code, migration, seed, middleware, Gate, Policy, and UI guard changes are out of scope.  
9. Spec04 Assignability and Check-in residuals remain closed / not reopened.  
10. Spec02 owns vocabulary; Spec04 owns action enumeration description only.

---

## H. Formal Definition Outcome

`FORMAL_VOCABULARY_DEFINED`

---

## Required Final Definition Block

```text
FORMAL_PERMISSION_VOCABULARY_DEFINITION

Decision:
FORMAL_VOCABULARY_DEFINED

Naming Strategy:
ACCEPT_MODULE_RESOURCE_VERB

Granularity:
COARSE

Vocabulary Ownership:
SPEC02_CONFIRMED

Approved Permission Keys:
2

Defined Keys:
dormitory.structure.view
dormitory.structure.manage

Protected Actions Total:
21

Covered Actions:
14

Unresolved Actions:
7

Excluded / Out-of-Scope Actions:
0

Implementation Authority:
NONE

Primary Owner:
SPEC02

Selection Basis:
Human-approved coarse-grained vocabulary contract with explicit boundary definition for authorized Dormitory structure permissions only.

Immediate Next Step:
Permission Contract Review

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## No-Change Confirmation

`No application, test, catalog, decision, discovery, proposal, or other Spec02/Spec04 files were modified.`

Only this artifact was created:

- `.specify/docs/spec02/spec02-dormitory-formal-permission-vocabulary-definition.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`DEFINITION_RECORDED`** / **`FORMAL_VOCABULARY_DEFINED`**  
- Owner: Spec02 (Identity RBAC vocabulary)  
- Last Updated: 2026-07-13
