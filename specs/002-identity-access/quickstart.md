# Quickstart: Identity & Access (spec02)

**Date**: 2026-06-26 | **Plan**: [plan.md](./plan.md)

Validation scenarios for Wave 1A Identity module after implementation. Prerequisites assume spec01 Foundation is running.

---

## Prerequisites

- Docker Sail up: `./vendor/bin/sail up -d`
- Migrations including Identity module: `./vendor/bin/sail artisan migrate`
- Identity seed (roles): `./vendor/bin/sail artisan db:seed --class=IdentityRoleSeeder`

---

## Scenario 1 — User create issues UUID v7 (P1)

**Proves:** FR-002, HasUuid kernel, SC-001

1. Run `php artisan identity:user-create "Display Name" --email=user@example.com`
2. Capture returned user id
3. Assert UUID version 7 format (regex / Ramsey validate)
4. Query `identity_users` — single row, `status = active`

**Expected:** Immutable id assigned automatically; no manual UUID input.

---

## Scenario 2 — Disable user (P1)

**Proves:** FR-003/004, FR-005, SC-004

1. Disable user from Scenario 1
2. Assert `status = disabled` in DB
3. Assert audit/activity entry exists
4. Assert `UserDeactivated` event dispatched (test listener or log)

---

## Scenario 3 — Supplier read contract (P3)

**Proves:** FR-008, FR-011, SC-003

Using `IdentityUserReadContract` from tinker or feature test:

```php
$contract = app(\App\Modules\Identity\Application\Contracts\IdentityUserReadContract::class);

$contract->userExists($activeUserId);      // true
$contract->isUserActive($activeUserId);    // false after disable
$contract->findUserSummary($unknownId);    // null
```

**Forbidden check (manual/arch test):** No code outside Identity imports `UserModel`.

---

## Scenario 4 — Role assignment (P2)

**Proves:** FR-006/007, SC-002

1. Assign role via `AssignRoleToUserAction` or test suite (`RoleAssignmentTest`)
2. Assert `hasPermissionTo('identity.users.manage')` on `UserModel`
3. Revoke via `RevokeRoleFromUserAction` — permission check fails

---

## Scenario 5 — Architecture boundary (P3)

```bash
./vendor/bin/sail artisan test tests/Architecture
```

**Expected:** No new cross-module Infrastructure imports involving Identity.

---

## Scenario 6 — Downstream consumer (spec03, later)

Boundary tests BT-01–BT-03 per [identity-employee-boundary.md](./contracts/identity-employee-boundary.md) — **not** part of spec02 quickstart exit criteria.

---

## References

- [data-model.md](./data-model.md)
- [contracts/identity-read-service.md](./contracts/identity-read-service.md)
- [events.md](./events.md)
