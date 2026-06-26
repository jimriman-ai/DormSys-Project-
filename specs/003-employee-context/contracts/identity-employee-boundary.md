# Boundary Contract (pointer)

**Spec:** spec03 Employee Context — Wave 1A  
**Status:** Stub — normative text lives in spec02

---

The authoritative cross-context contract for CD-012 (Employee ↔ Identity) is maintained in spec02:

**[../../002-identity-access/contracts/identity-employee-boundary.md](../../002-identity-access/contracts/identity-employee-boundary.md)**

## spec03 responsibilities (summary)

When spec03 is specified, it MUST implement:

- `employees.identity_id` — immutable UUID reference, no FK
- Assignment once at Employee creation
- Boundary tests BT-01, BT-02, BT-03
- Consumption of `IdentityUserReadContract` (see [identity-read-service.md](../../002-identity-access/contracts/identity-read-service.md))

Do not duplicate or amend CD-012 here. Update spec02 contract + catalog decision if boundary changes (unfreeze required).
