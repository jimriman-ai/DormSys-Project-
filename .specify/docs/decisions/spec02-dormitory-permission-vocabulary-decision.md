---
artifact: spec02_dormitory_permission_vocabulary_decision
status: DECISION_RECORDED
mutation_permission: none
execution_authority: AUTHORIZE_FORMAL_PERMISSION_VOCABULARY_DEFINITION
operating_mode: HUMAN_ARCHITECTURAL_DECISION
date: 2026-07-12
---

# Spec02 Dormitory Permission Vocabulary — Human Decision Record

**Artifact type:** Human architectural decision (vocabulary constraints only)  
**Upstream proposal:** `.specify/docs/spec02/spec02-dormitory-permission-naming-proposal.md` (`PROPOSAL_READY_FOR_HUMAN_DECISION`)  
**Upstream vocabulary discovery:** `.specify/docs/spec02/spec02-dormitory-surface-permission-vocabulary-discovery.md` (`VOCABULARY_PARTIALLY_DEFINED`)  
**Upstream action enumeration:** `.specify/docs/spec04/spec04-dormitory-protected-action-enumeration-discovery.md` (`ACTIONS_ENUMERATED`, 21)

This record locks naming strategy, granularity, ownership, and scope for the upcoming **formal permission vocabulary definition**. It does **not** authorize seeding, migrations, policies, middleware, Gates, Livewire checks, Spec02 unfreeze beyond vocabulary-contract drafting, or definition of the 7 insufficiently evidenced actions.

---

## A. Decision Basis

Human review of `.specify/docs/spec02/spec02-dormitory-permission-naming-proposal.md` confirmed:

| Proposal element | Assessment |
| ---------------- | ---------- |
| Scope bound to 14 `REQUIRES_UNDEFINED_PERMISSION` catalog actions | Accepted |
| Exclusion of 7 `INSUFFICIENT_EVIDENCE` Integration/dual-path actions | Accepted |
| Identity-aligned `module.resource.verb` naming | Accepted as Spec02 catalog convention |
| COARSE grain: `dormitory.structure.view` + `dormitory.structure.manage` | Accepted — matches constitution Manage Rooms + seed `view`/`manage` split |
| Spec04 plan candidates (`dormitory.view`, `dormitory.manage_structure`, …) as non-binding | Accepted — must not create a second convention for these 14 |
| No policy / enforcement design in the proposal | Correct boundary for this checkpoint |

The proposal is **accepted without modification** as the constraint set for Formal Permission Vocabulary Definition.

---

## B. Selected Decisions

| Decision area | Selected value |
| ------------- | -------------- |
| Naming Strategy | `ACCEPT_MODULE_RESOURCE_VERB` |
| Granularity | `COARSE` |
| Vocabulary Ownership | `SPEC02_CONFIRMED` |
| Unresolved Actions | `KEEP_UNRESOLVED` |
| Next Authorization | `AUTHORIZE_FORMAL_PERMISSION_VOCABULARY_DEFINITION` |

**Canonical keys authorized for formal definition (vocabulary contract only):**

| Permission key | Covers |
| -------------- | ------ |
| `dormitory.structure.view` | Actions #12–#17 (catalog hierarchy reads) |
| `dormitory.structure.manage` | Actions #1–#8 (catalog hierarchy creates + status mutations) |

**Not authorized for definition in the next step:** permissions for actions #9–#11, #18–#21.

---

## C. Rationale

1. **Naming (`ACCEPT_MODULE_RESOURCE_VERB`)** — Seeded Identity permissions already use `{module}.{resource}.{verb}` (`identity.users.view` / `identity.users.manage`). Accepting that strategy keeps the platform catalog predictable and rejects action-based names (`view_room_inventory`) that would diverge from Spatie seed style.

2. **Granularity (`COARSE`)** — Constitution §12 **Manage Rooms** grants the same capability to Admin / Dormitory Manager / Dormitory Unit Staff without per-entity splits. Fourteen Application methods share one admin job (structure browse vs structure mutate). COARSE yields **two** keys, preserves the Identity read/mutation split, and avoids MEDIUM/FINE naming explosion and role-matrix churn. MEDIUM (e.g. per room/bed resource keys) was **not** selected: no repository evidence that Manage Rooms roles need different rights by hierarchy level for these 14 actions.

3. **Ownership (`SPEC02_CONFIRMED`)** — Ownership D3 and the Auth↔Dormitory dependency clarification assign RBAC vocabulary to Spec02. Spec04 continues to enumerate Dormitory actions only; it does not own permission string authority. Delegation to Spec04 is rejected to prevent dual catalogs.

4. **Unresolved actions (`KEEP_UNRESOLVED`)** — The seven Integration/dual-path actions lack Spec02 Auth mapping evidence. Defining them now would invent Integration permission semantics. They remain out of the formal definition wave; additional discovery is **not** required to proceed on the 14.

5. **Next authorization (`AUTHORIZE_FORMAL_PERMISSION_VOCABULARY_DEFINITION`)** — Discovery and naming proposal are complete for the 14. Human authority now permits a **vocabulary contract only** (names, coverage, ownership notes) — not code, seeds, or enforcement.

---

## D. Architectural Guardrails for Next Step

The Formal Permission Vocabulary Definition step **must**:

| Guardrail | Requirement |
| --------- | ----------- |
| No code | No application, test, seeder, migration, middleware, Policy, Gate, or Livewire changes |
| Vocabulary contract only | Define approved permission **names** and action coverage; do not implement checks |
| Unresolved stay unresolved | Do **not** name or map permissions for actions #9–#11, #18–#21 |
| Ownership | Spec02 remains sole vocabulary authority; Spec04 boundary **not** reopened |
| Closed residuals | Assignability and Check-in residuals stay closed / not reopened |
| Keys locked | Use only `dormitory.structure.view` and `dormitory.structure.manage` unless a later human decision revises this record |
| No catch-all | Do not introduce `dormitory.manage` or wildcard `dormitory.*` as vocabulary authority |
| No Spec04 candidate dual write | Do not promote `dormitory.view` / `dormitory.manage_structure` as parallel approved keys for these 14 |

---

## Required Final Decision Block

```text
HUMAN_PERMISSION_VOCABULARY_DECISION

Naming Strategy:
ACCEPT_MODULE_RESOURCE_VERB

Granularity:
COARSE

Vocabulary Ownership:
SPEC02_CONFIRMED

Unresolved Actions:
KEEP_UNRESOLVED

Next Authorization:
AUTHORIZE_FORMAL_PERMISSION_VOCABULARY_DEFINITION

Protected Actions Total:
21

Actions Requiring Vocabulary Definition:
14

Actions Remaining Insufficiently Evidenced:
7

Primary Authority:
HUMAN

Spec04 Boundary Status:
NOT_REOPENED

Selection Basis:
Accepted Spec02 naming proposal: Identity-aligned module.resource.verb at COARSE grain (dormitory.structure.view|manage) for 14 Manage Rooms catalog actions; Spec02 owns vocabulary; 7 Integration/dual-path actions stay undefined; authorize vocabulary-contract definition only.

Immediate Next Step:
FORMAL_PERMISSION_VOCABULARY_DEFINITION

Scope Integrity:
CLOSED_SPEC04_ASSIGNABILITY_AND_CHECKIN_RESIDUALS_NOT_REOPENED
```

---

## Explicit Non-Authorization

This decision does **not** authorize:

- `IdentityRoleSeeder` or Spatie permission creation  
- role→permission attachment (including `DormMgr`)  
- Dormitory Policy / Application enforcement wiring  
- Spec02 Wave unfreeze beyond drafting the vocabulary contract artifact  
- Integration-consumer Auth design  
- reopening Assignability or Check-in residuals  

---

## No-Change Confirmation

`No application, test, catalog, Spec04, Spec02 discovery/proposal, or seeder files were modified.`

Only this decision artifact was created:

- `.specify/docs/decisions/spec02-dormitory-permission-vocabulary-decision.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`DECISION_RECORDED`**  
- Authority: Human architectural decision  
- Last Updated: 2026-07-12
