# Contract: Identity User Read Service (FR-008)

**Version:** 1.0.0  
**Spec:** spec02 Identity & Access  
**Implements:** spec.md FR-008, FR-011  
**Boundary:** CD-012 supplier surface — read-only, no consumer mutation

---

## Purpose

Defines the **only** supported cross-module API for downstream bounded contexts to read Identity user state by immutable account identifier.

Consumers MUST NOT:

- Import `App\Modules\Identity\Infrastructure\*`
- Query `identity_users` (or any Identity table) via Eloquent/Query Builder
- Cache Identity rows without an documented strategy (OA-02-03 deferred)

---

## Interface

**Namespace (planned):** `App\Modules\Identity\Application\Contracts\IdentityUserReadContract`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Contracts;

use App\Modules\Identity\Application\DTOs\UserSummaryDTO;
use App\Modules\Identity\Domain\ValueObjects\UserId;

interface IdentityUserReadContract
{
    /**
     * Returns true if a user with the given id exists (any status).
     */
    public function userExists(UserId $id): bool;

    /**
     * Returns true if the user exists and status is Active.
     */
    public function isUserActive(UserId $id): bool;

    /**
     * Returns a minimal read projection, or null if not found.
     */
    public function findUserSummary(UserId $id): ?UserSummaryDTO;
}
```

---

## Implementation rules

| Rule | Detail |
|------|--------|
| Implementation class | `IdentityUserReadService` in `Application/Services/` |
| Internal dependency | Identity `UserRepository` only (inside module) |
| Registration | Singleton bind in `IdentityServiceProvider` |
| Consumer dependency | Inject `IdentityUserReadContract` only |
| Mutations | **None** — create/disable use separate admin actions not exposed as cross-module API in Wave 1A |

---

## FR-008 mapping

| Requirement | Method | Signature |
|-------------|--------|-----------|
| Verify user exists | `userExists` | `userExists(UserId $id): bool` |
| Verify user is active | `isUserActive` | `isUserActive(UserId $id): bool` |
| Minimal summary | `findUserSummary` | `findUserSummary(UserId $id): ?UserSummaryDTO` |

**Canonical interface** (identical in spec.md FR-008 and plan.md FR-008 table):

```php
public function userExists(UserId $id): bool;
public function isUserActive(UserId $id): bool;
public function findUserSummary(UserId $id): ?UserSummaryDTO;
```

### `UserSummaryDTO` (nullable contract return type)

| Field | Type | Notes |
|-------|------|-------|
| `id` | `string` | UUID |
| `status` | `string` | `active` \| `disabled` |
| `displayName` | `string` | Non-sensitive label |

`findUserSummary` returns `null` when user does not exist (not an exception).

---

## Error & validation behavior

| Input | Behavior |
|-------|----------|
| Malformed UUID string | `UserId::fromString` throws domain validation exception before service call |
| Unknown valid UUID | `userExists` → false; `isUserActive` → false; `findUserSummary` → null |
| Disabled user | `userExists` → true; `isUserActive` → false; summary includes `status: disabled` |

No internal exception details leak to consumers.

---

## Testing requirements

| Test | Layer |
|------|-------|
| Contract returns correct booleans for active/disabled/missing users | Feature (Identity) |
| Mock consumer class in `tests/Feature/Modules/Identity/` depends only on interface | Feature |
| No `use App\Modules\Identity\Infrastructure` in other modules | Architecture (existing pest-plugin-arch) |

---

## Related documents

- [identity-employee-boundary.md](./identity-employee-boundary.md) — CD-012; consumer-side `identity_id` rules in spec03
- [events.md](../events.md) — async alternatives deferred
- [plan.md](../plan.md) — § FR-008 mapping
