# Data Model: Spec01 Foundation Abstractions

**Branch**: `001-technical-foundation` | **Date**: 2026-06-22

This document defines **foundational abstractions only** — no domain-specific business entities, tables, or state machines. Business schemas are deferred to subsequent feature specifications.

---

## Scope

Spec01 establishes shared kernel types that all future module entities will extend or implement. No persistent tables are created for these abstractions in the foundation phase except infrastructure tables owned by installed packages (e.g., Spatie permission tables — migration stubs only, not seeded).

---

## Foundational Types

### BaseEntity

| Attribute | Type | Rules |
|-----------|------|-------|
| `id` | UUID (string) | Immutable after creation; non-null; primary identifier |
| `createdAt` | `DateTimeImmutable` | Set on creation; stored as UTC |
| `updatedAt` | `DateTimeImmutable` | Updated on mutation; stored as UTC |

**Behavior**:
- Abstract PHP class in `App\Shared\Domain\BaseEntity`
- No Eloquent dependency
- Subclasses define domain identity and invariants
- Equality by `id` when types match

**Future extension points** (not implemented in Spec01):
- `deletedAt` for soft-delete semantics (infrastructure concern)
- Audit metadata references

---

### BaseValueObject

| Concern | Rule |
|---------|------|
| Immutability | All properties `readonly`; no setters after construction |
| Equality | Structural equality via `equals(self $other): bool` |
| Validation | Invariants enforced in constructor; throw domain exception on violation |
| Serialization | `toArray(): array` for DTO/event payloads |

**Location**: `App\Shared\Domain\BaseValueObject`

**Examples in future modules** (documentation only, not implemented now):
- `DateRange`, `AllocationReason`, `VoucherCode`

---

### BaseDomainEvent

| Attribute | Type | Rules |
|-----------|------|-------|
| `eventId` | UUID | Unique per event occurrence |
| `occurredAt` | `DateTimeImmutable` | UTC timestamp of event creation |
| `aggregateId` | UUID | ID of originating aggregate |
| `payload` | `array` | Serializable event data |

**Behavior**:
- Abstract class in `App\Shared\Domain\BaseDomainEvent`
- Implements `JsonSerializable`
- Dispatched via Laravel event bus from Application layer only
- Domain layer defines event classes; never dispatches directly to infrastructure

---

### BaseRepository (Interface)

| Method | Signature | Contract |
|--------|-----------|----------|
| `save` | `save(object $entity): void` | Persist new or updated aggregate |
| `findById` | `findById(string $id): ?object` | Return entity or null |
| `delete` | `delete(object $entity): void` | Remove or soft-delete per module policy |

**Location**: `App\Shared\Domain\Contracts\BaseRepository`

**Rules**:
- Interface lives in Shared Domain; implementations in module Infrastructure
- Return types use module-specific entity types in implementations (generics documented via PHPDoc templates)
- No cross-module repository implementations

---

### ModuleServiceProvider (Base Class)

| Concern | Responsibility |
|---------|----------------|
| Registration | Bind module repository interfaces to infrastructure implementations |
| Boot | Load module routes, views, migrations (when present) |
| Migration path | `loadMigrationsFrom()` pointing to `database/migrations/{module}/` |

**Location**: `App\Shared\Infrastructure\ModuleServiceProvider`

**Subclasses**: One per core module (Identity, Employee, Request, Approval, Dormitory, Allocation, Lottery, Voucher, Notification, Audit)

---

## PostgreSQL Conventions (Foundation)

These conventions apply to **all future migrations**; foundation phase creates only extension-enablement migration.

| Convention | Standard |
|------------|----------|
| Primary keys | `uuid` column, default `gen_random_uuid()` |
| Timestamps | `timestamptz` columns `created_at`, `updated_at`; always UTC |
| Soft deletes | `deleted_at timestamptz NULL` where applicable |
| Naming | `snake_case` tables and columns; plural table names |
| Module prefix | Optional table prefix by module (e.g., `audit_logs`) — per-module decision in future specs |
| Cross-module refs | UUID columns **without FK** to other module tables (Constitution AP-04) |
| Intra-module FKs | Encouraged within same module boundary |
| JSON metadata | `jsonb` for audit payloads, settings, state snapshots |
| Indexes | `{table}_{column(s)}_index` naming |

---

## Package-Owned Schemas (Stubs Only)

The following package migrations are published but **not populated** in Spec01:

| Package | Tables | Notes |
|---------|--------|-------|
| `spatie/laravel-permission` | `roles`, `permissions`, pivots | RBAC foundation; no seed data |
| `spatie/laravel-activitylog` | `activity_log` | Distinct from custom `audit_logs` in Audit module (future) |

---

## Entity Relationship Diagram (Foundation)

```text
┌─────────────────────────────────────────────────────────┐
│                    Shared Kernel                         │
│  ┌─────────────┐  ┌──────────────────┐  ┌────────────┐ │
│  │ BaseEntity  │  │ BaseValueObject  │  │BaseDomain  │ │
│  │  (abstract) │  │    (abstract)    │  │Event       │ │
│  └──────┬──────┘  └──────────────────┘  └────────────┘ │
│         │ extends (future modules)                        │
│  ┌──────┴──────────────────────────────────────────┐   │
│  │ BaseRepository <<interface>>                     │   │
│  └───────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
         ▲ implemented by (future, per module)
┌────────┴────────┐
│ Module Repository│  (Infrastructure layer, not in Spec01)
└─────────────────┘
```

No inter-entity relationships exist at foundation level.

---

## Validation Rules (Foundation)

| Rule ID | Applies To | Validation |
|---------|------------|------------|
| VR-F01 | BaseEntity.id | Must be valid UUID format |
| VR-F02 | BaseDomainEvent.occurredAt | Must not be in the future beyond clock skew tolerance (1s) |
| VR-F03 | BaseValueObject | Constructor must validate all invariants before assignment |
| VR-F04 | Module directories | All 10 modules must have Domain/Application/Infrastructure/Presentation subdirectories |

---

## State Transitions

Not applicable at foundation level. State machines (`spatie/laravel-model-states`) are installed but no entity states are defined in Spec01.
