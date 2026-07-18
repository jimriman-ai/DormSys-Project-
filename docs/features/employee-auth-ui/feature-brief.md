# Feature Brief — employee-auth-ui

| Field       | Value                                     |
|-------------|-------------------------------------------|
| Phase       | F2                                        |
| Boundary    | employee-auth-ui                          |
| Guard       | auth:identity                             |
| Status      | **PARTIAL** — W-01…W-08 CLOSED (`work-breakdown.md:14`); F-W07-04 CARRIED FORWARD (`w07-security-review-report.md:19`). Prior “L1 — Active (W-07 report awaiting Lead)” superseded. (reconciled 2026-07-15, ref: DGAP-12) |
| Auth record | product-authorization-employee-auth-ui.md |

## Goal

Session-based authentication UI for the employee boundary using the
`identity` guard.

## Identity Model

`App\Modules\Identity\Infrastructure\Persistence\Models\UserModel` (table: `identity_users`,
`$guard_name = ['web','identity']`)

## Known gaps / disposition (synced 1405/04/24)

| # | Topic | Status |
|---|-------|--------|
| 1 | Two User models (`App\Models\User` web credentials vs `UserModel` identity) — wiring risk | Open (operational; documented in L3 + W-07) |
| 2 | Password broker for `identity` provider | **RESOLVED — NO ACTION** (W-03; L3 §2–§3) |
| 3 | Eloquent relationship UserModel ↔ Employee | **CLOSED** — DGAP-07 Decision A: `identity_id` UUID value-reference sufficient (W-02 CLOSED); see `docs/governance/open-decisions.md` |
| 4 | Default guard is `web` — F2 routes must pin `auth:identity` | Open (mitigated by explicit middleware / L3) |
| 5 | Dual `$guard_name` on UserModel — role checks must pin guard | Open (mitigated by `IdentityRoleGuard` / SEC-G-01) |

## Shared Kernel (BL-04)

- **BL-04:** IdentityRoleGuard → Shared Kernel — **DELIVERED** via W-06 (`app/Shared/Auth/IdentityRoleGuard.php`); DG-03 **CLOSED** (Option B, AUTH-013). See `docs/governance/risk-register.md` (BL-04 Mitigated/Delivered).

## Out of Scope

- Password broker / reset flow (W-03 NO ACTION)
- BL-B1-01 remediation — **RESOLVED** in commit `369a106` — assignment schema + dashboard scoping (RM-BL-B1).
- Spec04 Auth / DGAP-03/05/06/08 (parked behind Business Owner)
