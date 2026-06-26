# Implementation Plan: Identity & Access (spec02)

**Branch**: `002-identity-access` | **Date**: 2026-06-26 | **Spec**: [spec.md](./spec.md)

**Input**: Wave 1A — Identity bounded context: User lifecycle, Role/Permission baseline, supplier read contract (FR-008), domain events. Authentication UX deferred (OA-02-01).

**Governance**: CD-012 supplier-only; normative boundary in [`contracts/identity-employee-boundary.md`](./contracts/identity-employee-boundary.md).

---

## Summary

Implement the **Identity** module as upstream supplier for platform user accounts and RBAC. Users receive **immutable UUID v7** primary keys via the spec01 kernel (`HasUuid` / `Ramsey\Uuid\Uuid::uuid7()`). Cross-context consumers (downstream bounded contexts) MUST use the **application-level read contract** [`contracts/identity-read-service.md`](./contracts/identity-read-service.md) — never direct queries against Identity persistence.

Wave 1A delivers: User CRUD (admin), disable lifecycle, Spatie roles/permissions seed, `UserCreated` / `UserDeactivated` events, audit hooks, architecture tests. **No** login/session flows.

---

## Technical Context

| Dimension | Value |
|-----------|-------|
| **Language/Version** | PHP 8.4; Laravel 13 (project baseline) |
| **Primary Dependencies** | `spatie/laravel-permission` (roles/permissions), spec01 `app/Support/` kernel (`BaseModel`, `HasUuid`, `RecordsActivity`) |
| **UUID strategy** | **UUID v7** via `HasUuid` trait → `Uuid::uuid7()` on `creating` (see §3.1) |
| **Storage** | PostgreSQL 17 — `identity` module migrations under `database/migrations/modules/identity/` |
| **Testing** | Pest PHP 4; unit (Domain/Application), feature (admin flows), architecture (module isolation) |
| **Target Platform** | Laravel Sail (dev); modular monolith |
| **Performance Goals** | Admin-scale user counts; no hot-path auth (deferred) |
| **Constraints** | No cross-module Eloquent; no FK to consumer tables; Persian RTL admin UI (later Livewire) |
| **Scale/Scope** | User + Role + Permission; 3 supplier read methods; 2 domain events |

---

## Constitution Check

*GATE: Must pass before implementation. Principles per Constitution v1.3.0. Re-checked after Phase 1 design.*

| Principle | Compliance | Notes |
|-----------|------------|-------|
| AP-01 Technology Stack | ✅ PASS | PHP 8.4, Laravel 13, PostgreSQL 17, `spatie/laravel-permission`, Pest; Livewire for admin (Phase E) |
| AP-01 Presentation (no SPA) | ✅ PASS | Livewire 3 admin UI; Alpine micro-interactions only; no React/Vue |
| AP-02 Modular Monolith | ✅ PASS | `app/Modules/Identity/` four layers |
| AP-03 Clean Architecture | ✅ PASS | Domain pure PHP; Eloquent in Infrastructure only |
| AP-04 Shared DB / Module Ownership | ✅ PASS | `identity_users` owned by Identity; consumers store UUID refs only; FR-008 via Application contract |
| AP-05 Explicit State Machines | ⬜ N/A | User lifecycle is `UserStatus` enum + `disable()` — not listed in AP-05 entities; no `spatie/laravel-model-states` for User in Wave 1A |
| AP-06 Audit Everything | ⚠️ CONDITIONAL PASS | `RecordsActivity` on `UserModel` for create/disable (FR-012); central `AuditService` integration deferred until Audit module implementation |
| AP-07 Background Processing | ⬜ N/A | Lifecycle events synchronous (R-07); no new Horizon jobs in spec02 |
| AP-08 Configuration Over Hardcoding | ⬜ N/A | Wave 1A role/permission names in `IdentityRoleSeeder` baseline — no lottery/scoring settings |
| 10.7 Localization | ⚠️ DEFERRED | Persian RTL Livewire admin in Phase E (optional tail); core MVP (T001–T021) testable without UI |
| 10.4 Maintainability / DoD | ✅ PASS | PHPStan L8, Pint, Pest — tasks T039–T040 |
| CD-012 / FR-009–011 | ✅ PASS | Supplier-only; read contract; no consumer refs in Identity |
| OA-02-01 Auth deferred | ✅ PASS | No Fortify/session/login in Wave 1A (plan §8.1) |

**Post-design re-check**: No constitution violations requiring ADR. Conditional items (AP-06, 10.7) documented; do not block MVP T001–T021.

---

## FR-008 → Application Read Contract (explicit mapping)

Cross-context reads are **forbidden** at persistence/repository level. FR-008 maps exclusively to `IdentityUserReadContract`:

| spec.md FR-008 capability | Contract method | Layer | Forbidden alternative |
|---------------------------|-----------------|-------|------------------------|
| Verify user exists by identifier | `userExists(UserId $id): bool` | Application | Consumer `User::query()->where(...)` |
| Verify user is active | `isUserActive(UserId $id): bool` | Application | Consumer JOIN on `users` |
| Minimal summary for display | `findUserSummary(UserId $id): ?UserSummaryDTO` | Application | Consumer `DB::table('users')` |

**Registration**: Interface in `App\Modules\Identity\Application\Contracts\IdentityUserReadContract`. Implementation in `Application\Services\IdentityUserReadService` (delegates to Identity repository **inside** module only). Bound in `IdentityServiceProvider`. Consumers depend on **interface only**.

See [`contracts/identity-read-service.md`](./contracts/identity-read-service.md).

---

## 1. UUID v7 from Kernel (explicit)

### 1.1 Rule

All Identity persistence models that use the platform primary key convention MUST:

1. Extend `App\Support\Models\BaseModel`
2. Use `HasUuid` (already assigns `Uuid::uuid7()->toString()` on Eloquent `creating`)
3. Expose domain identifier via `getId(): string` after UUID assignment (creation-time; see spec01 `plan.md` Implementation Alignment Notes)

### 1.2 Identity `User` persistence model

```text
App\Modules\Identity\Infrastructure\Persistence\Models\UserModel extends BaseModel
  - table: identity_users (or users with module prefix — see data-model.md)
  - $keyType = 'string', $incrementing = false (via HasUuid)
  - status: active | disabled
  - NO columns referencing downstream consumers
```

### 1.3 Value object

`App\Modules\Identity\Domain\ValueObjects\UserId` wraps validated UUID string; used in contract signatures and domain layer.

**Prohibited**: `Str::uuid()` (v4), manual ID assignment in controllers, consumer-side UUID generation for User root.

---

## 2. Module Structure (Identity)

```text
app/Modules/Identity/
├── Domain/
│   ├── Entities/User.php              # Pure PHP aggregate
│   ├── ValueObjects/UserId.php
│   ├── Enums/UserStatus.php           # Active, Disabled
│   ├── Events/UserCreated.php
│   ├── Events/UserDeactivated.php
│   └── Exceptions/...
├── Application/
│   ├── Contracts/
│   │   └── IdentityUserReadContract.php    # FR-008 — cross-module surface
│   ├── DTOs/UserSummaryDTO.php
│   ├── Services/
│   │   ├── IdentityUserReadService.php     # FR-008 implementation
│   │   ├── CreateUserAction.php
│   │   └── DeactivateUserAction.php
│   └── ...
├── Infrastructure/
│   ├── Persistence/Models/UserModel.php
│   ├── Repositories/UserRepository.php
│   └── Providers/IdentityServiceProvider.php
└── Presentation/
    └── Livewire/...                   # Admin UI (implementation phase)

database/migrations/modules/identity/
└── *_create_identity_users_table.php
└── *_create_permission_tables.php     # Spatie publish + module path

tests/
├── Unit/Modules/Identity/
├── Feature/Modules/Identity/
└── Architecture/                      # existing suite — no new consumer imports
```

---

## 3. Role & Permission Baseline

- Package: `spatie/laravel-permission` (constitution-aligned RBAC)
- Seed roles (Wave 1A minimum): `SystemAdministrator`, plus placeholders for operational roles defined in constitution permission matrix (expand in later specs)
- Permissions: namespace `identity.*` for user/role admin; domain permissions added as modules land
- User ↔ Role: Spatie pivot within Identity module tables only

---

## 4. User Lifecycle (Wave 1A)

| Transition | Trigger | Domain event | Audit |
|------------|---------|--------------|-------|
| → Active (create) | `CreateUserAction` | `UserCreated` | Yes |
| Active → Disabled | `DeactivateUserAction` | `UserDeactivated` | Yes |
| Disabled → Active | **Out of scope** | — | — |

State enforced in Domain entity + Infrastructure guards (no reactivation in Wave 1A per spec).

---

## 5. Implementation Phases

### Phase A — Domain & persistence (P1)

- Migration `identity_users` with UUID PK, status, timestamps, audit columns
- `User` domain entity + `UserModel` + repository
- Unit tests: UUID assigned on create, disable transition, immutability of id

### Phase B — RBAC (P2)

- Spatie migrations in `database/migrations/modules/identity/`
- Role/permission seeder
- Assign/revoke role actions + tests

### Phase C — Supplier read contract (P3)

- `IdentityUserReadContract` + `IdentityUserReadService`
- `UserSummaryDTO` (id, status, display label fields only)
- Feature tests with mock consumer calling contract (no Employee module required)
- Architecture test: Employee module must not import `UserModel`

### Phase D — Events & audit (P1/P3)

- Dispatch `UserCreated`, `UserDeactivated` (Laravel events implementing domain event conventions)
- Wire `RecordsActivity` on `UserModel`

### Phase E — Admin presentation (optional Wave 1A tail)

- Livewire: user list, create, disable, role assign — Persian RTL
- **No** login page (OA-02-01)

---

## 8. Scope Lock: Authentication vs Authorization vs Events

*Pre-tasks checkpoint — authoritative for `/speckit-tasks`. Do not expand scope without spec amendment.*

### 8.1 Authentication (OA-02-01) — **OUT of spec02**

| Out of scope | Notes |
|--------------|-------|
| Login / logout UI or API | Deferred to entry-flow spec |
| Session establishment, cookies, guards for web login | Deferred |
| Password storage, reset, MFA, SSO | Deferred |
| Laravel Fortify, Sanctum, Breeze, default `auth` routes | **Do not install** in Wave 1A |
| `routes/web.php` public login endpoints | **None** |

**Wave 1A admin operations** (create user, assign role, disable) run via:

- Application Actions / Artisan commands (dev & test), and/or
- Livewire admin components behind **permission checks only** (no session login flow yet)

Tests use direct action invocation or acting-as factory user **without** implementing real authentication gateway.

### 8.2 Authorization (RBAC) — **IN spec02**

| In scope | Notes |
|----------|-------|
| `spatie/laravel-permission` roles & permissions | Identity module tables |
| Seed: `SystemAdministrator` + `identity.*` permissions | See data-model.md |
| Assign / revoke role on User | FR-006, FR-007 |
| `$user->hasPermissionTo()` / policy hooks for admin actions | Authorization **baseline** for when auth arrives |

**Distinction:** spec02 builds **who can do what** (RBAC data + checks); not **how users sign in**.

### 8.3 Domain events — **IN spec02 (exactly two)**

| Event | When | Transport (Wave 1A) | Payload |
|-------|------|---------------------|---------|
| `UserCreated` | After User persisted | **Synchronous** Laravel event from `CreateUserAction` | `events.md` — `identity.user.created` v1.0 |
| `UserDeactivated` | After disable persisted | **Synchronous** Laravel event from `DeactivateUserAction` | `events.md` — `identity.user.deactivated` v1.0 |

**In scope supporting behavior:**

- Domain event classes under `Domain/Events/`
- Dispatch in Application layer after successful persist
- `RecordsActivity` on `UserModel` for audit (FR-012) — complementary to events, not a replacement
- Feature/unit tests assert event dispatched (`Event::fake` or listener)

**Explicitly OUT:**

| Excluded | Reason |
|----------|--------|
| `IdentityLinked` / linkage events on Identity | CD-012 — consumer owns reference |
| `EmployeeIdentityAssigned` | spec03 |
| Async queue / outbox table | R-07 deferred |
| Event persistence table `identity_events` | Not in spec; audit via activity log |
| Notification module listeners | spec09 |
| Consumer-side reactions to `UserDeactivated` | OA-02-02 deferred |

**Phase mapping:** Events ship in **Phase D** tied to US1 (create/disable); not a separate event-sourcing effort.

---

## 6. Dependencies

| Dependency | Status |
|------------|--------|
| spec01 Foundation | Approved — module scaffold, `HasUuid`, arch tests, CI |
| spatie/laravel-permission | Install in spec02 implementation |
| spec03 Employee | **Not required** for spec02 completion; boundary tests BT-01–03 live in spec03 |

---

## 7. Project Structure (documentation)

```text
specs/002-identity-access/
├── spec.md
├── plan.md                    # this file
├── research.md
├── data-model.md
├── events.md
├── quickstart.md
├── contracts/
│   ├── identity-employee-boundary.md
│   └── identity-read-service.md
└── tasks.md                   # /speckit-tasks (next)
```

---

## Complexity Tracking

> No constitution violations requiring justification.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| — | — | — |

---

## Generated Artifacts (this plan run)

| Artifact | Path | Status |
|----------|------|--------|
| Implementation plan | `specs/002-identity-access/plan.md` | ✅ Complete |
| Research | `specs/002-identity-access/research.md` | ✅ Complete |
| Data model | `specs/002-identity-access/data-model.md` | ✅ Complete |
| Read service contract | `specs/002-identity-access/contracts/identity-read-service.md` | ✅ Complete |
| Quickstart | `specs/002-identity-access/quickstart.md` | ✅ Complete |
| spec03 boundary stub | `specs/003-employee-context/contracts/identity-employee-boundary.md` | ✅ Pointer only |

---

## Freeze Record (Wave 1A Complete)

**Frozen:** 2026-06-26  
**Status:** Feature frozen — implementation complete for Wave 1A scope.

| Deliverable | Status |
|-------------|--------|
| US1 User lifecycle + events | ✅ |
| US2 RBAC baseline | ✅ |
| US3 `IdentityUserReadContract` (FR-008) | ✅ |
| PHPStan `app/Modules/Identity` | ✅ 0 errors |
| Tests (Feature + Architecture) | ✅ |
| Livewire admin (T035–T037) | ⏸ Deferred |

**Reopen policy:** Unfreeze requires catalog decision + amendment to frozen artifacts. OA-02-01 auth remains deferred.
