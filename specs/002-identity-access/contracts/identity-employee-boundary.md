# Contract: Identity ↔ Employee Boundary (CD-012)

**Version:** 1.0.0  
**Spec:** spec02 (Identity & Access) + spec03 (Employee Context) — Wave 1A  
**Authority:** [`catalog-decisions.md`](../../../.specify/docs/catalog-decisions.md) CD-012, [`context-map.md`](../../../.specify/docs/context-map.md) R1  
**Status:** Frozen boundary — implementation detail deferred to spec02/spec03 plans

---

## Purpose

Defines the cross-context contract for attaching an Employee record to an Identity (User) account without FK coupling, per Hard Freeze v1.0.0.

This contract is **normative** for Wave 1A. It does not amend CD-012.

---

## Ownership (verbatim governance)

| Context | spec | Owns (frozen) |
| ------- | ---- | ------------- |
| **Identity** | spec02 | User, Role, Permission |
| **Employee** | spec03 | Employee, Department, Dependent |

**CD-012 decision:**

> Employee attaches to Identity via an **immutable UUID reference** (`identity_id`), without FK or shared table.  
> **Domain invariant:** `identity_id` is assigned exactly once at Employee creation. Subsequent modification is prohibited.

**Coupling direction:** Employee → Identity (Customer–Supplier). Identity does **not** store employee references.

---

## What this contract is NOT

The following are **not** in frozen governance and must **not** be assumed in spec02:

- An aggregate root named `Identity` separate from `User`
- Identity-side states `Created → Linked → Disabled` where `Linked` means attachment to Employee
- `IdentityService::linkToEmployee()` or `linked_to` / `linked_at` on Identity tables
- `IdentityLinked` events with employee payload owned by Identity context
- FK constraints between `users` and `employees` tables

If a future need arises for Identity to track linkage, that requires a **new catalog decision** and unfreeze rationale — not spec02 inference.

---

## Reference model

```
┌─────────────────────┐         ┌─────────────────────┐
│  Identity (spec02)  │         │  Employee (spec03)  │
│  ─────────────────  │         │  ─────────────────  │
│  users.id (UUID v7) │◄────────│  employees.         │
│  roles, permissions │  read   │    identity_id      │
│                     │  only   │  (immutable, no FK) │
└─────────────────────┘         └─────────────────────┘
         │                                   │
         │ UserCreated / UserDeactivated     │ identity_id set once at create
         └─────────── events ────────────────┘ (Employee domain enforces)
```

---

## Identity context responsibilities (spec02)

### User aggregate

- Primary key: UUID v7 (aligned with `App\Support\Traits\HasUuid` / spec01 foundation)
- Lifecycle states (User account): e.g. `active` → `disabled` (auth account lifecycle only)
- Publishes domain events for account lifecycle (see `events.md`)

### Application services exposed to other contexts (read/query)

| Operation | Purpose | Notes |
| --------- | ------- | ----- |
| `userExists(userId: string): bool` | Validate reference before Employee create | No Employee data required |
| `isUserActive(userId: string): bool` | Optional gate at Employee create | CD-012: error handling when deactivated — **deferred** |
| `findUserSummary(userId: string): ?UserSummaryDTO` | Read-only projection for cross-context display | No Eloquent leak across modules |

### Out of scope for spec02

- Writing `employees.identity_id`
- Knowing Employee aggregate IDs in Identity persistence
- Voucher eligibility (OQ-07, spec08)
- Reporting projections (OQ-08, spec11)

---

## Employee context responsibilities (spec03)

### `identity_id` column

| Rule | Requirement |
| ---- | ------------- |
| Type | UUID string, same format as `users.id` |
| FK | **Prohibited** |
| Assignment | Exactly **once**, at Employee creation (or first linkage moment if create is split — still Employee-owned) |
| Mutation | **Prohibited** after assignment (domain + persistence guards) |
| Null | Allowed only before assignment; not allowed after Employee is considered "linked" to Identity |

### Employee domain API (examples)

```php
// spec03 — illustrative, not implementation mandate
Employee::createWithIdentity(UserId $identityId, ...); // sets identity_id once
$employee->getIdentityId(): UserId;                   // throws if unset
// NO $employee->reassignIdentityId()
```

### Cross-context access

- Employee **must not** query Identity via Eloquent across modules
- Employee **must** use Identity Application Service (or consume events) per constitution AP-04

---

## Event contract (Wave 1A)

### Published by Identity (spec02)

| Event | When | Consumers |
| ----- | ---- | --------- |
| `UserCreated` | User account persisted | Audit; optional Notification (later) |
| `UserDeactivated` | User disabled | Audit; spec03 **may** react (deferred policy per CD-012 open items) |

### Not published by Identity

| Event | Reason |
| ----- | ------ |
| `IdentityLinked` | Linkage is Employee-owned (`identity_id` assignment), not Identity-owned |

### Optional future (spec03)

| Event | When | Notes |
| ----- | ---- | ----- |
| `EmployeeIdentityAssigned` | `identity_id` first set | Emitted from **Employee** context if event-driven assignment is chosen |

---

## Boundary test matrix (Wave 1A)

| ID | Owner spec | Scenario | Expected |
| -- | ---------- | -------- | -------- |
| BT-01 | spec03 | Create Employee with valid `identity_id` | Success; `identity_id` immutable |
| BT-02 | spec03 | Attempt to change `identity_id` after assignment | Domain exception |
| BT-03 | spec03 | Create Employee with unknown `identity_id` | Rejected via Identity Application Service |
| BT-04 | spec02 | Disable User | User deactivated; Employee reaction **deferred** (CD-012 open) |
| BT-05 | arch | Employee Infrastructure imports Identity Eloquent model | Architecture test **fails** |

Tests BT-01–BT-03 are **implemented in spec03**; Identity fakes/mocks in spec02 unit tests only.

---

## Open assumptions (non-blocking, Hard Freeze)

| ID | Topic | Wave 1A handling |
| -- | ----- | ---------------- |
| OA-02-01 | Full authentication / login flows | **Deferred** — spec-catalog open question; spec02 defines User + RBAC baseline only |
| OA-02-02 | Behavior when User deactivated but Employee active | **Deferred** — CD-012 "What Was NOT Decided" |
| OA-02-03 | Caching Identity lookups from Employee | **Deferred** to spec03 plan |

---

## Compliance checklist

- [ ] `users.id` uses UUID v7 (HasUuid)
- [ ] `employees.identity_id` has no FK to `users`
- [ ] No `linkToEmployee` API on Identity module
- [ ] Cross-module reads via Application Service only
- [ ] Architecture tests enforce module isolation
- [ ] Boundary tests BT-01–BT-05 green before Wave 1A exit

---

## Traceability

| Source | Reference |
| ------ | --------- |
| CD-012 | `catalog-decisions.md` — Employee ↔ Identity Attachment Mechanism |
| R1 | `context-map.md` — Identity → Employee |
| spec02 scope | `spec-catalog.md` — Identity accounts, roles, permissions, access-control baseline |
| spec03 scope | `spec-catalog.md` — Employee profiles; CD-012 on Employee row |
| UUID kernel | `specs/001-technical-foundation/plan.md`, `App\Support\Traits\HasUuid` |
