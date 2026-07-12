---
artifact: spec04_allocation_assignability_impl_approval
spec: Spec04
status: AUTHORIZATION_DECIDED
mutation_permission: none
execution_authority: none
operating_mode: FEATURE_AND_SPEC_COMPLETION_MODE
authorization_decision: IMPLEMENTATION_AUTHORIZATION_GRANTED
basis_contract: .specify/docs/contracts/spec04-allocation-assignability-contract-definition.md
basis_prep: .specify/docs/authorization/spec04-allocation-assignability-impl-auth.md
date: 2026-07-12
---

# Spec04 Allocation Assignability — Implementation Authorization Approval

**Artifact type:** Implementation Authorization Approval (go / no-go gate)  
**Status:** `AUTHORIZATION_DECIDED`  
**Mutation permission:** `none`  
**Execution authority:** `none` (this artifact grants execution authority to a subsequent implementation step; it does not itself mutate code)

This decision evaluates the prepared authorization scope against the locked contract. It does **not** implement code, alter contracts, reopen ownership, or add UI scope.

---

## A. Fixed Inputs

The following are locked and **must not be reopened** by implementation:

| Input | Locked position |
| ----- | --------------- |
| Governance repair | Complete |
| Residual ownership (Allocation ↔ Dormitory) | Spec04 |
| Assignability / `RESERVED` ownership | Spec04 |
| Occupancy / check-in / resident-presence truth | Spec07 |
| Identity / auth | Spec02 |
| UI | Not a domain owner; out of scope |
| Physical inventory marker model | `VACANT` / `RESERVED` / `OCCUPIED` |
| Capability contract | `CONTRACT_DEFINED` |
| Implementation authorization preparation | `AUTHORIZATION_PREPARED` |

---

## B. Authorization Evaluation Table

| Area | Status | Reason | Blocker Required Before Execution? |
| ---- | ------ | ------ | ---------------------------------- |
| Migration | **APPROVED** | Additive Spec04-only alter on `dormitory_beds` (expand occupancy CHECK to include `reserved`; optional nullable `last_signal_reference_id`) is narrow, matches contract persistence capabilities, and does not touch Spec07 tables or introduce cross-module FK coupling. | No |
| Null Adapter Replacement | **APPROVED** | Locked contract requires a live Spec04 path replacing Null UUID-format assignability and no-op marker signals. Prep scopes live bridges under `app/Integrations/Allocation/` + `IntegrationServiceProvider`, with **binding-only** removal of Null bindings in `AllocationServiceProvider`. Existing `CreateAllocationAction` already consumes `DormitoryReadPort` / `PhysicalStateSignalPort`; no unresolved dependency blocks safe replacement. | No |
| Spec04↔Spec07 Boundary | **APPROVED** | Prep freezes CheckIn core writes and forbids Spec07 direct writes into Spec04 tables; Spec04 remains inventory-marker authority; Allocation consumes Spec04 via Application contracts / Integration only; Spec07 remains occupancy/check-in truth owner. No proposed direct Spec07→Spec04 DB write path. | No |
| Test Scope | **APPROVED** | Prepared behavioral coverage includes the three minimum scenarios (successful VACANT→RESERVED allocation, block on RESERVED/OCCUPIED, live provider resolves real Spec04 state vs Null). Supporting illegal-transition / release cases remain optional hardening, not authorization blockers. | No |

### Binding rule (resolves prep dual-binding ambiguity)

For implementation execution:

1. Register live `DormitoryReadPort` and `PhysicalStateSignalPort` implementations in `IntegrationServiceProvider` (Integration bridges → Spec04 Application contracts).  
2. Remove (do not retain) Null singleton bindings for those ports from `AllocationServiceProvider`.  
3. Do **not** dual-bind the same ports in both providers.

### Occupy-marker transition rule (narrow clarification, not redesign)

`occupy_marker` for this authorization is **RESERVED → OCCUPIED** only (Spec04 inventory marker). VACANT→OCCUPIED is **not** authorized in this residual.

### Test ID mapping note

Contract / prep numbering (T1 = block RESERVED/OCCUPIED; T2 = VACANT→RESERVED; T3 = live≠Null) and the approval-gate wording of the same three behaviors are **equivalent coverage**. Implementation must satisfy all three behaviors regardless of local test method naming.

---

## C. Final Authorization Decision

`IMPLEMENTATION_AUTHORIZATION_GRANTED`

All four evaluation areas are **APPROVED**.

---

## D. Approved Execution Scope

### May change (categories)

- Spec04 Dormitory Domain / Application / Infrastructure for assignability reads and inbound RESERVE / OCCUPY_MARKER / RELEASE marker application  
- Additive migration under `database/migrations/modules/dormitory/` for `reserved` CHECK (+ optional `last_signal_reference_id`)  
- New Integration bridges under `app/Integrations/Allocation/`  
- `app/Providers/IntegrationServiceProvider.php` live port registration  
- `app/Modules/Allocation/Infrastructure/Providers/AllocationServiceProvider.php` **binding-only** Null removal  
- Unit/Feature tests under Dormitory and Allocation covering the three required behaviors (and optional supporting transition cases)  
- Architecture registry updates **only if** required for documented Integration paths (no new undocumented exceptions)

### Remains frozen

- Spec02 Identity / auth models and policies  
- Spec07 CheckIn core occupancy write models and check-in domain rules  
- Allocation Domain / `CreateAllocationAction` rewrites (beyond live port consumption already present)  
- UI, routes, Blade/Livewire outside this residual  
- Spec06 / Spec11 reopen; catalog / ownership / contract redefinition  
- Cross-module Eloquent; Spec07 direct writes to Spec04 tables  
- Optional sync of `specs/004-accommodation-resource/contracts/allocation-physical-state-port.md` is **not** required for this code gate

### Guardrails

- This step does **not** implement code.  
- This step does **not** alter contracts.  
- This step does **not** reopen ownership.  
- This step does **not** add UI scope.  
- Authorization would have been denied or held if the prepared execution boundary were unclear; it is not.

---

## Required Authorization Decision Block

```text
SPEC04_ALLOCATION_ASSIGNABILITY_IMPLEMENTATION_AUTHORIZATION_APPROVAL

Authorization Decision:
IMPLEMENTATION_AUTHORIZATION_GRANTED

Next Required Step:
IMPLEMENTATION_EXECUTION

Approved Code Scope:
Spec04 Dormitory Domain/Application/Infrastructure (assignability + VACANT/RESERVED/OCCUPIED markers);
additive dormitory_beds migration (reserved CHECK ± last_signal_reference_id);
app/Integrations/Allocation live bridges; IntegrationServiceProvider bindings;
AllocationServiceProvider binding-only Null removal;
Dormitory/Allocation tests for block-on-RESERVED|OCCUPIED, VACANT→RESERVED success, and live Spec04 physical-state resolution
```

---

## No-Change Confirmation

`No application, test, migration, catalog, contract, or ownership files were modified by this approval step.`

Only this artifact was created:

- `.specify/docs/authorization/spec04-allocation-assignability-impl-approval.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`AUTHORIZATION_DECIDED`** / **`IMPLEMENTATION_AUTHORIZATION_GRANTED`**  
- Next: **`IMPLEMENTATION_EXECUTION`**  
- Owner: Governance / Implementation Authorization Approval  
- Last Updated: 2026-07-12
