# Employee Module (spec03)

**Bounded context:** Employee profiles, departments, dependents (CD-009).  
**Upstream:** Identity via `IdentityUserReadContract` only (CD-012) — **never** import `App\Modules\Identity\Infrastructure\*`.

## Prerequisites

Create an Identity user before linking an employee:

```bash
php artisan identity:user-create "Display Name" --email=user@example.com
php artisan employee:create {user-uuid} --code=EMP001 --first-name=... --last-name=... --national-code=... --hire-date=2024-01-01
```

## Wave 1A MVP scope

- `employee_employees.identity_id` — immutable UUID, no FK to `identity_users`
- Boundary tests BT-01–BT-05
- Stop at MVP Gate before US2 (Department CRUD)

## Contracts (downstream)

- Consumer of: `App\Modules\Identity\Application\Contracts\IdentityUserReadContract`
- Supplier (later waves): `EmployeeEligibilityContract`, `EmployeeReadContract`
