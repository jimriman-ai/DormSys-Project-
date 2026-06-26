# Identity Module (spec02)

Upstream **supplier** bounded context for platform user accounts and RBAC baseline.

## Scope (Wave 1A)

| In scope | Out of scope (OA-02-01) |
|----------|-------------------------|
| User lifecycle (create, disable) | Login, session, password, MFA, SSO |
| Roles & permissions (Spatie) | Fortify / Sanctum |
| `IdentityUserReadContract` (FR-008) | Cross-module Eloquent access |
| `UserCreated` / `UserDeactivated` events | `IdentityLinked` events |

## Cross-module consumption (FR-008 / FR-011)

Downstream modules (e.g. Employee) MUST inject:

```php
App\Modules\Identity\Application\Contracts\IdentityUserReadContract
```

Canonical signatures — see [`specs/002-identity-access/contracts/identity-read-service.md`](../../../specs/002-identity-access/contracts/identity-read-service.md).

**Forbidden:** `App\Modules\Identity\Infrastructure\*`, direct queries on `identity_users`.

## Artisan commands (dev / quickstart)

```bash
php artisan identity:user-create "Display Name" --email=user@example.com
php artisan identity:user-deactivate {uuid}
php artisan db:seed --class=IdentityRoleSeeder
```

## Layer layout

```
Domain/       User entity, UserId, events, exceptions
Application/  Actions, IdentityUserReadContract, DTOs
Infrastructure/ UserModel, UserRepository, migrations
Presentation/   Console commands (no login routes)
```

## Governance

- CD-012 supplier-only — [`contracts/identity-employee-boundary.md`](../../../specs/002-identity-access/contracts/identity-employee-boundary.md)
- Audit: `RecordsActivity` on `UserModel` (central AuditService deferred)
