# Feature Brief — employee-auth-ui

| Field       | Value                                     |
|-------------|-------------------------------------------|
| Phase       | F2                                        |
| Boundary    | employee-auth-ui                          |
| Guard       | auth:identity                             |
| Status      | L1 — Active                               |
| Auth record | product-authorization-employee-auth-ui.md |

## Goal

Session-based authentication UI for the employee boundary using the
`identity` guard.

## Identity Model

`App\Modules\Identity\Infrastructure\Persistence\Models\UserModel` (table: `identity_users`,
`$guard_name = ['web','identity']`)

## Known gaps (from readiness review — must be addressed in L3)

1. Two distinct User models exist (`App\Models\User` for `web`/`users`;
   UserModel for `identity`) — wiring risk.
2. No password broker for `identity` provider.
3. No Eloquent relationship UserModel ↔ Employee.
4. Default guard is `web` — all F2 routes must explicitly use
   `auth:identity`.
5. Dual `$guard_name` on UserModel — role checks must pin the guard.

## Deferred Execution

- BL-04: IdentityRoleGuard → Shared Kernel (L6, W-06)

## Out of Scope

- Password broker / reset flow (pending decision, W-03)
- BL-B1-01 remediation (remains Open/Deferred; reopen at Phase H or on
  consumer demand)
