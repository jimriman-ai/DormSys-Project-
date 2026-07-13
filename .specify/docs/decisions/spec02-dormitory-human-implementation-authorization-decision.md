---
artifact: spec02_dormitory_human_implementation_authorization_decision
decision_type: HUMAN_IMPLEMENTATION_AUTHORIZATION
status: FINAL_HUMAN_DECISION_RECORDED
authority: HUMAN
operating_mode: AUTHORIZATION_GATE
implementation_authority: HUMAN_GRANTED
human_decision: AUTHORIZE_IMPLEMENTATION
date: 2026-07-13
---

# Spec02 Dormitory Authorization — Human Implementation Authorization Decision

**Artifact type:** Final human authorization gate (non-executing)  
**Request under review:** `.specify/docs/decisions/spec02-dormitory-authorization-implementation-authorization-request.md` (`REQUEST_PREPARED`)  
**Vocabulary:** `.specify/docs/spec02/spec02-dormitory-formal-permission-vocabulary-definition.md` (`FORMAL_VOCABULARY_DEFINED`)  
**Contract review:** `.specify/docs/spec02/spec02-dormitory-permission-contract-review-record.md` (`CONTRACT_ACCEPTED`)  
**Readiness:** `.specify/docs/spec02/spec02-dormitory-authorization-implementation-readiness-review.md`

This artifact records whether bounded implementation authority is **granted** or **deferred**. It does **not** execute implementation, create code/seeds/migrations/policies/middleware, assign roles, expand vocabulary, or reopen Spec04 Assignability / Check-in residuals.

---

## A. Decision Basis

| Prerequisite | Status before this decision |
| ------------ | --------------------------- |
| Permission vocabulary formally defined | `FORMAL_VOCABULARY_DEFINED` — keys locked |
| Permission contract accepted | `CONTRACT_ACCEPTED` |
| Readiness review completed | Human decisions required; later satisfied |
| Human decisions recorded | Seed `APPROVE`; placement `APPLICATION_LAYER_PEP`; role grants `DEFER_ROLE_MAPPING`; unresolved `DENY_BY_DEFAULT_UNTIL_RESOLVED` |
| Implementation authorization request prepared | `REQUEST_PREPARED` |
| Implementation authority | **`NONE`** before this decision |

Closed vocabulary, contract, Spec04 residuals, and recorded human decisions are **not** reopened.

---

## B. Requested Scope Under Review

Exact bounded implementation scope considered for authorization (no expansion):

| Element | Bound |
| ------- | ----- |
| Permission keys | **Only** `dormitory.structure.view`, `dormitory.structure.manage` |
| Action coverage | Approved Dormitory structure actions only (`#1–#8` manage; `#12–#17` view) |
| Enforcement placement | `APPLICATION_LAYER_PEP` |
| Role mapping | `DEFERRED` |
| Unresolved actions `#9–#11`, `#18–#21` | `DENY_BY_DEFAULT_UNTIL_RESOLVED` |

---

## C. Human Authorization Decision

`AUTHORIZE_IMPLEMENTATION`

---

## D. Granted Implementation Authority Boundaries

**Implementation authority:** `HUMAN_GRANTED`  
**Authorized scope:** `BOUNDED_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_ONLY`

### Allowed Implementation Categories

Bounded implementation work **only** for:

1. Authorization binding for approved Dormitory structure actions (`#1–#8`, `#12–#17`).  
2. Use of already approved permission keys only (`dormitory.structure.view`, `dormitory.structure.manage`).  
3. Enforcement at the approved application-layer PEP location (`APPLICATION_LAYER_PEP`).  
4. Preservation of deny-by-default behavior for unresolved actions (`DENY_BY_DEFAULT_UNTIL_RESOLVED`).  
5. Spec02-owned registration/seed of **only** the two approved permission keys (per recorded seed authority `APPROVE`) — **without** role→permission attachment.

### Prohibited Even If Authorized

Still prohibited:

- new permission creation (beyond the two locked keys)  
- permission renaming  
- role-permission grant implementation  
- role mapping implementation  
- migrations unless separately authorized  
- seed expansion beyond the two approved keys  
- middleware expansion unless separately authorized  
- Check-in authorization work  
- Allocation authorization work  
- Spec04 residual reopening (Assignability / Check-in)  
- broad RBAC refactoring  
- UI / Presentation authorization expansion unless separately authorized  

### Required Validation Conditions

Authorized implementation **must**:

- remain boundary-preserving  
- not expand beyond the two approved permissions  
- keep unresolved actions blocked / deny-by-default (no coarse-key inheritance)  
- not reopen closed Spec04 assignability or Check-in residuals  
- remain minimal, reviewable, and reversible  

---

## E. Deferral Section

Not applicable — `AUTHORIZE_IMPLEMENTATION` was selected.

---

## F. Scope Integrity Confirmation

| Constraint | Status |
| ---------- | ------ |
| Approved vocabulary locked | **Confirmed** |
| Spec04 Assignability residuals closed | **Confirmed** — `NOT_REOPENED` |
| Check-in residuals closed | **Confirmed** — `CLOSED` |
| Unresolved actions remain unresolved (no vocabulary keys) | **Confirmed** |
| Role mapping authority | **Not granted** — remains `DEFERRED` |

---

## Required Final Decision Block

```text
SPEC02_DORMITORY_HUMAN_IMPLEMENTATION_AUTHORIZATION_DECISION

Vocabulary Status:
LOCKED

Approved Permissions:
dormitory.structure.view
dormitory.structure.manage

Enforcement Placement:
APPLICATION_LAYER_PEP

Role Mapping:
DEFERRED

Unresolved Actions:
DENY_BY_DEFAULT_UNTIL_RESOLVED

Human Decision:
AUTHORIZE_IMPLEMENTATION

Implementation Authority:
HUMAN_GRANTED

Authorized Scope:
BOUNDED_DORMITORY_STRUCTURE_AUTHORIZATION_BINDING_ONLY

Spec04 Boundary Status:
NOT_REOPENED

Check-in Residual Status:
CLOSED

Permission Expansion:
PROHIBITED

RBAC Expansion:
PROHIBITED

Selection Basis:
REQUEST_PREPARED_FROM_ACCEPTED_CONTRACT_AND_RECORDED_HUMAN_DECISIONS

Immediate Next Step:
IMPLEMENT_SPEC02_DORMITORY_AUTHORIZATION_BINDING

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## Explicit Non-Execution

This decision grants bounded authority for a **future** implementation step. It does **not** itself modify code, run seeds, create migrations, policies, middleware, or role grants.

---

## No-Change Confirmation

`No application, test, catalog, vocabulary, contract, readiness, request, or other files were modified.`

Only this decision artifact was created:

- `.specify/docs/decisions/spec02-dormitory-human-implementation-authorization-decision.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`FINAL_HUMAN_DECISION_RECORDED`** / **`AUTHORIZE_IMPLEMENTATION`**  
- Authority: Human  
- Last Updated: 2026-07-13
