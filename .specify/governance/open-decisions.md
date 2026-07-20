# Open Decisions — DEPRECATED POINTER

> **DEPRECATED / NON-CANONICAL**
>
> This file is **not** a Decision Gate Register.
> **One decision boundary = one canonical record.**
>
> **Canonical register only:** [`docs/governance/open-decisions.md`](../../docs/governance/open-decisions.md)
>
> Do not duplicate Status rows, Metadata, or Changelog here.
> Do not use this file for stage transitions.

*(Historical archaeology removed 1405/04/24 — SGAP disposition control: pointer-only.)*

## Decision Record — 2026-07-20 (Lead)

- **Q-DBT-1-AUTH** → RESOLVED — Option B (Policy-based authorization).
- **Q-DASH-3-ROLE-SOURCE** → RESOLVED — Option A: add `ROLE_EMPLOYEE` constant to
  `app/Shared/Auth/IdentityRoleGuard.php`. Shared guard remains the single role SoT.
- **01-B** → REVIEWED (human gate passed).
- **DBT-1 (ledger sync)** → `listSites()` is DELIVERED; residual scope = UI wiring + authorization only.
- **OQ-DASH-04** → DASH-02 declared CLOSED / VERIFIED by Lead (satisfies WP-DASH-G04).
- **Roadmap protocol** → No new `roadmap-execution-protocol.md` will be created.
  DGAP-15 remains the sole Sprint C sequencing SoT. Its absence MUST NOT be re-raised
  as DECISION_REQUIRED.

## WP-DASH-G09 — CLOSED (2026-07-20)

- Employee role SoT unified into IdentityRoleGuard::ROLE_EMPLOYEE.
- DashboardIdentityRoles.php deprecated → verified zero references → deleted (G09-B).
- Evidence: DashboardNavTest 3/3 pass; Pint/PHPStan clean; pre-deletion grep = 1 hit (stub only).

## Q-EMP-DORM — RESOLVED

| Field | Value |
|---|---|
| **Decision date** | 1405/04/29 (2026-07-20) |
| **Decision** | Option B — Assignment-based |
| **Statement** | Employee access to Dormitories is restricted to explicit assignments. Relationship: `Employee 1—* DormitoryAssignment *—1 Dormitory`. An employee can only see Dormitories for which an active assignment record exists for that employee. Global (all-dormitories) access for the Employee role is rejected. |
| **Evidence** | ER sketch (`Employee 1—* Dormitory`) + DBT-1 residual |
| **Impact on G02** | Quarantined WP-DASH-G02 artifacts (DormitoryPolicy + tests) must be rewritten against the assignment model. Supersedes the hard-coded global-access assumption. |
| **Approved by** | Lead — 1405/04/29 |
| **Previous status** | Q-DBT-1-AUTH: decision approved, implementation deferred |
| **New status** | Q-EMP-DORM: RESOLVED → Option B |

### Addendum — WP-DASH-G02-R1 implementation constraints (Lead-approved, 1405/04/29)

1. **FK target:** `dormitory_assignments.user_id` references `identity_users.id` (NOT `users.id`), consistent with `dormitory_manager_assignments`, `dormitory_unit_manager_assignments`, and the `auth:identity` guard.
2. **Table independence:** `dormitory_assignments` is a new, standalone employee↔dormitory table. It does not extend, replace, or interact with `dormitory_manager_assignments` or `dormitory_unit_manager_assignments`.
3. **Lifecycle:** `dormitory_assignments` uses `revoked_at` for soft revocation. The two manager-assignment tables intentionally do not have `revoked_at`; this asymmetry is accepted and documented.
