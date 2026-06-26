# Boundary Contract (pointer)

**Spec:** spec03 Employee Context — Wave 1A  
**Status:** **Active** — normative text lives in spec02 (frozen); spec03 implements consumer obligations

---

The authoritative cross-context contract for CD-012 (Employee ↔ Identity) is maintained in spec02:

**[../../002-identity-access/contracts/identity-employee-boundary.md](../../002-identity-access/contracts/identity-employee-boundary.md)**

## spec03 implementation obligations (Wave 1A MVP)

spec03 **MUST** implement:

| Obligation | Verification |
|------------|--------------|
| `employee_employees.identity_id` — immutable UUID, no FK to Identity | BT-01, BT-02; migration |
| Assignment exactly once at Employee creation | `CreateEmployeeAction` |
| Reject unknown `identity_id` | BT-03 via `IdentityUserReadContract::userExists` |
| Allow create when Identity user inactive (OA-03-02) | T021 |
| No Employee reaction when Identity deactivated post-create (BT-04) | T021a |
| No `App\Modules\Identity\Infrastructure\*` imports | BT-05 (T026a) |

**Forbidden:** `IdentityService::linkToEmployee`, FK to `identity_users`, mutable `identity_id`, direct Identity Eloquent/repository access.

Do not duplicate or amend CD-012 here. Update spec02 contract + catalog decision if boundary changes (unfreeze required).

**Supplier contract:** [identity-read-service.md](../../002-identity-access/contracts/identity-read-service.md)
