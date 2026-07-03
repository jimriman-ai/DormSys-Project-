# CheckIn Module (spec07)

**Bounded context:** Operational occupancy transitions (`CheckedIn`, `CheckedOut`) for internal dormitories.  
**Spec:** `specs/007-allocation-checkin/` · **Wave 1A:** scaffold + foundational schema only (T006–T052).

## spec07 scope

CheckIn/CheckOut consumes **assignment facts** from Allocation and records operational stay transitions. It does **not** create or modify assignments. Operator role gates apply at operational implementation (Wave 1B).

## Boundary decisions

| ID | Rule |
| -- | ---- |
| **CD-014** | Assignment authority remains in Allocation — CheckIn reads assignment facts only |
| **CD-015** | CheckIn/CheckOut is a separate active boundary — not folded into Allocation or Dormitory |

## Integration relationships

| ID | Direction | Contract |
| -- | --------- | -------- |
| **R5** | Request → Allocation | Upstream to assignment — CheckIn does not consume Request directly |
| **R6** | Lottery → Allocation | Upstream to assignment — CheckIn does not consume Lottery directly |
| **R7** | Allocation → Dormitory → CheckIn/CheckOut | Assignment chain; CheckIn operational transitions follow physical-state coordination |

Cross-module integration uses Application contracts only — no cross-module Eloquent.

## Module status

Setup phase complete. Foundational schema and domain logic follow in Phase 2+.
