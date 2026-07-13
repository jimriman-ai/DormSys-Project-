---
artifact: spec02_dormitory_authorization_implementation_authorization_request
decision_type: IMPLEMENTATION_AUTHORIZATION_REQUEST
status: REQUEST_PREPARED
authority_required: HUMAN
implementation_authority: NONE
operating_mode: AUTHORIZATION_GATE
date: 2026-07-13
---

# Spec02 Dormitory Authorization — Implementation Authorization Request

**Artifact type:** Implementation authorization request (non-granting)  
**Vocabulary:** `.specify/docs/spec02/spec02-dormitory-formal-permission-vocabulary-definition.md` (`FORMAL_VOCABULARY_DEFINED`)  
**Contract review:** `.specify/docs/spec02/spec02-dormitory-permission-contract-review-record.md` (`CONTRACT_ACCEPTED`)  
**Readiness:** `.specify/docs/spec02/spec02-dormitory-authorization-implementation-readiness-review.md` (`NEEDS_HUMAN_DECISION_BEFORE_AUTHORIZATION`)  
**Human vocabulary decision:** `.specify/docs/decisions/spec02-dormitory-permission-vocabulary-decision.md`

This artifact **prepares** a bounded request for a later human implementation-authorization gate. It does **not** grant implementation authority, permit code/seed/migration/policy/middleware/role-mapping work, expand vocabulary, or reopen Spec04 Assignability / Check-in residuals.

---

## A. Authorization Basis

| Prerequisite | Status |
| ------------ | ------ |
| Permission vocabulary formally defined | `FORMAL_VOCABULARY_DEFINED` — keys `dormitory.structure.view`, `dormitory.structure.manage`; naming `ACCEPT_MODULE_RESOURCE_VERB`; granularity `COARSE` |
| Permission contract accepted | `CONTRACT_ACCEPTED` — finite include/exclude boundaries; Spec boundary safety Pass |
| Readiness review completed | Covered structure actions bindable at contract level; readiness required human decisions before IA request |
| Required human decisions recorded | Seed authority `APPROVE`; placement `APPLICATION_LAYER_PEP`; role grants `DEFER_ROLE_MAPPING`; unresolved `DENY_BY_DEFAULT_UNTIL_RESOLVED` |
| Implementation authority | **`NONE`** (unchanged by this request) |

Closed decisions are not reinterpreted. No new permission keys are derived.

---

## B. Requested Implementation Boundary

**If** a later human gate grants implementation authorization, the **requested** (not authorized) scope would be limited to:

| Element | Bound |
| ------- | ----- |
| Permission keys | **Only** `dormitory.structure.view` and `dormitory.structure.manage` |
| Action coverage | Covered Dormitory structure actions only — `#1–#8` (`manage`), `#12–#17` (`view`) per formal vocabulary definition |
| Enforcement placement | `APPLICATION_LAYER_PEP` on those covered Application contract methods |
| Unresolved actions `#9–#11`, `#18–#21` | Remain undefined in vocabulary; enforcement stance `DENY_BY_DEFAULT_UNTIL_RESOLVED` (must not inherit coarse structure permissions) |
| Seed catalog (if later authorized under seed authority `APPROVE`) | Create/register **only** the two approved keys in Spec02-owned permission storage — **no** role→permission attachment in that packet |
| Role mapping | Remains `DEFER_ROLE_MAPPING` — grants out of requested boundary |

This section describes **requested scope only**. It does **not** authorize implementation.

---

## C. Explicitly Excluded Scope

The following remain **out of scope** for any requested implementation authorization unless separately and explicitly approved later:

- any new permission key creation  
- any permission vocabulary changes  
- any role-permission grant implementation  
- any role mapping changes  
- any seed execution beyond separately authorized scope (and never beyond the two approved keys)  
- any migration work  
- any middleware creation unless separately authorized later  
- any policy expansion beyond approved Dormitory structure scope  
- any Check-in authorization work  
- any Allocation reopening  
- any Spec04 residual reopening (Assignability / Check-in)  
- any UI-surface / Presentation authorization expansion  
- any broad RBAC refactor  

---

## D. Pending Human Authorization Gate

**This artifact does not grant authorization.**

The next required step is a **separate human authorization decision** with exactly one of:

- `AUTHORIZE_IMPLEMENTATION`  
- `DEFER_IMPLEMENTATION_AUTHORIZATION`  

No outcome is preselected here.

---

## E. Requested Authorization Conditions

The next human authorization gate must evaluate whether:

1. Implementation may be allowed for the **approved permission keys only** (`dormitory.structure.view`, `dormitory.structure.manage`).  
2. Change scope is limited to the **approved enforcement location** (`APPLICATION_LAYER_PEP` on covered structure actions).  
3. **Role mapping remains deferred** (`DEFER_ROLE_MAPPING`) — no role→permission assignment in the authorized packet.  
4. **Unresolved actions remain denied by default** (`DENY_BY_DEFAULT_UNTIL_RESOLVED`) and are not covered by coarse structure keys.  
5. **Spec04 Assignability and Check-in residuals remain closed** / not reopened.  
6. Implementation changes remain **minimal and bounded** to the requested boundary in §B, with no hidden authorization expansion.

---

## Required Final Decision Block

```text
SPEC02_DORMITORY_AUTHORIZATION_IMPLEMENTATION_AUTHORIZATION_REQUEST

Request Status:
REQUEST_PREPARED

Permission Vocabulary:
LOCKED

Approved Permissions:
dormitory.structure.view
dormitory.structure.manage

Role Mapping:
DEFERRED

Enforcement Placement:
APPLICATION_LAYER_PEP

Unresolved Actions:
DENY_BY_DEFAULT_UNTIL_RESOLVED

Implementation Authority:
NONE

Next Required Human Gate:
AUTHORIZE_IMPLEMENTATION
or
DEFER_IMPLEMENTATION_AUTHORIZATION

Spec04 Boundary Status:
NOT_REOPENED

Check-in Residual Status:
CLOSED

Selection Basis:
CONTRACT_ACCEPTED + READINESS_COMPLETED + HUMAN_DECISIONS_RECORDED

Immediate Next Step:
HUMAN_IMPLEMENTATION_AUTHORIZATION_DECISION_REQUIRED

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## Explicit Non-Grant

This request does **not** authorize:

- coding, seeding, migrations, middleware, policies, Gates, UI guards  
- Spatie permission sync or role attachment  
- Spec02 unfreeze beyond what a later human IA may separately grant  
- unresolved-action permission definition  
- Spec04 Assignability or Check-in residual reopen  

---

## No-Change Confirmation

`No application, test, catalog, vocabulary, contract, readiness, or other decision files were modified.`

Only this request artifact was created:

- `.specify/docs/decisions/spec02-dormitory-authorization-implementation-authorization-request.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`REQUEST_PREPARED`**  
- Authority required: Human  
- Implementation authority: **NONE**  
- Last Updated: 2026-07-13
