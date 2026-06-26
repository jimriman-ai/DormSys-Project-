# Data Model: Identity & Access (spec02)

**Date**: 2026-06-26 | **Plan**: [plan.md](./plan.md)

---

## Bounded context

**Identity** — aggregate roots: **User** (platform account). **Role** and **Permission** managed via Spatie (see §3).

Identity does **not** model downstream consumer aggregates or linkage fields.

---

## 1. User (aggregate root)

### Domain entity: `User`

| Attribute | Type | Rules |
|-----------|------|-------|
| `id` | `UserId` (UUID v7) | Immutable after assignment; from `HasUuid` on persist |
| `status` | `UserStatus` | `Active` \| `Disabled` |
| `displayName` | string | Non-sensitive label for admin/summary DTO |
| `email` | string? | Optional admin contact; uniqueness enforced |
| `createdAt` | datetime (UTC) | |
| `updatedAt` | datetime (UTC) | |

### Invariants

1. `id` never changes after first persistence
2. Only `Active` users return `isUserActive() === true` from read contract
3. `Disabled` users cannot transition back to `Active` in Wave 1A
4. No attribute references downstream consumer identifiers

### State transitions

```text
[Active] ──disable()──► [Disabled]   (terminal in Wave 1A)
```

---

## 2. Persistence: `identity_users`

| Column | Type | Notes |
|--------|------|-------|
| `id` | `uuid` PK | UUID v7 via `HasUuid` |
| `status` | `string` | `active`, `disabled` |
| `display_name` | `string` | |
| `email` | `string` nullable | unique where not null |
| `created_at` | `timestamp` | UTC |
| `updated_at` | `timestamp` | UTC |
| `created_by` | `uuid` nullable | audit column (BaseModel) |
| `updated_by` | `uuid` nullable | |
| `deleted_at` | `timestamp` nullable | soft delete (BaseModel) |

**Module path:** `database/migrations/modules/identity/`

**FK constraints to other modules:** none

---

## 3. Role & Permission (Spatie)

Standard Spatie tables relocated/scoped to Identity migrations:

- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

`model_type` points to Identity `UserModel` class. Configuration in `config/permission.php` with table names unchanged or prefixed per migration decision in tasks.

### Wave 1A seed roles (minimum)

| Role | Purpose |
|------|---------|
| `SystemAdministrator` | User/role admin for Identity module |

Additional constitution roles (Department Manager, Operator, etc.) seeded as **permission placeholders** without full workflow wiring until later specs.

---

## 4. Value objects

### `UserId`

- Wraps RFC 4122 UUID string
- Factory: `fromString(string $raw): self` with validation
- Used in `IdentityUserReadContract` signatures

---

## 5. DTOs (application layer)

### `UserSummaryDTO` (read-only, FR-008)

| Field | Type | Exposed to consumers |
|-------|------|----------------------|
| `id` | string (UUID) | Yes |
| `status` | string | Yes |
| `displayName` | string | Yes |

No email/password/credential fields in cross-context summary (minimize leakage).

---

## 6. Explicit non-entities

| Rejected model | Reason |
|----------------|--------|
| `Identity` aggregate separate from User | Context name ≠ aggregate (spec.md) |
| `linked_to` / linkage table on Identity | CD-012: consumer owns reference |
| `IdentityLinked` event entity | Not Identity-owned |

---

## 7. Traceability

| spec.md | Model element |
|---------|---------------|
| FR-001, FR-002 | User + UUID v7 |
| FR-003, FR-004 | UserStatus |
| FR-006, FR-007 | Role, Permission (Spatie) |
| FR-008 | UserSummaryDTO + read contract |
| FR-009 | No consumer columns |
