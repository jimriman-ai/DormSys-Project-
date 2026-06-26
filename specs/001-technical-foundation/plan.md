# Implementation Plan: DormSys Technical Foundation (Spec01)

**Branch**: `001-technical-foundation` | **Date**: 2026-06-22 | **Spec**: [spec.md](./spec.md)

**Input**: Bootstrap Laravel 13modular monolith foundation with PostgreSQL 17, Redis, platform packages, Pest testing, Sail dev environment, and minimal CI.

## Summary

Spec01 establishes a greenfield Laravel 13application skeleton for DormSys — an internal government administrative accommodation management system. The deliverable is not business features but a constitution-compliant platform: modular monolith directory structure (10 bounded contexts), shared kernel abstractions, PostgreSQL/Redis wiring, platform package installation, Pest test harness with architecture enforcement, PHPStan/Pint quality gates, Sail-based local development, and GitHub Actions CI.

All decisions trace to Constitution v2.0.0, Spec01, and ADR-001. Unspecified versions are planning assumptions documented in [research.md](./research.md).

---

## Technical Context

| Dimension | Value |
|-----------|-------|
| **Language/Version** | PHP 8.4 *(planning assumption per ADR-001)*; Laravel **12** *(Constitution AP-01)* |
| **Primary Dependencies** | Livewire **3**, Tailwind CSS **4** *(assumption)*, Alpine.js **3** *(assumption)*, Spatie packages, `morilog/jalali` |
| **Storage** | PostgreSQL **17** (primary); Redis **7** (cache, queue, session) |
| **Testing** | Pest PHP **3** with architecture tests |
| **Target Platform** | Linux containers (Sail) for dev; Linux VPS for future production |
| **Project Type** | Enterprise modular monolith web application (server-first) |
| **Performance Goals** | Foundation only — no load targets. Constitution NFR-P01 applies to future features |
| **Constraints** | No auth/business logic in Spec01; UTC timestamps; Persian RTL UI prep; append-only audit principle |
| **Scale/Scope** | 10 module scaffolds, shared kernel, 1 foundation migration, CI pipeline, README |

---

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-checked after Phase 1 design.*

| Principle | Spec01 Compliance | Notes |
|-----------|-------------------|-------|
| AP-01 Technology Stack | ✅ PASS | Laravel 12, Livewire 3, PG 17, Redis, Tailwind, Alpine |
| AP-02 Modular Monolith | ✅ PASS | 10 modules per Spec01; Approval≡Workflow alias documented |
| AP-03 Clean Architecture | ✅ PASS | 4-layer structure per module; shared kernel |
| AP-04 Shared DB / Module Ownership | ✅ PASS | Migration organization per module; no cross-module FKs |
| AP-05 State Machines | ✅ PASS | Package installed; no entities yet |
| AP-06 Audit Everything | ✅ PASS | Activitylog installed; Audit module scaffolded |
| AP-07 Server-First | ✅ PASS | Livewire primary; Alpine micro-interactions only |
| AP-08 Queue-Based Processing | ✅ PASS | Redis queue driver; Horizon installed, no jobs |
| AP-09 Caching Strategy | ✅ PASS | Redis cache driver configured |
| NFR-04 Maintainability | ✅ PASS | Pest, PHPStan L8, Pint planned |
| NFR-07 Localization | ✅ PASS | `fa` locale, RTL Tailwind, Jalali package |
| DoD Quality Gates | ✅ PASS | CI runs phpstan, pint, tests |

**Post-design re-check**: No violations. Complexity Tracking table not required.

**Known naming divergence** (documented, not a violation):
- Spec01 uses `Approval` and `Voucher` modules; Constitution uses `Workflow` and lottery-owned `Voucher`. Scaffold follows Spec01; aliases recorded in [research.md](./research.md) R-03, R-04.

---

## 1. Technology Stack Configuration

### 1.1 Mandated Versions (Constitution / Spec01)

| Component | Version | Source |
|-----------|---------|--------|
| Laravel | 13.x | Constitution AP-01 |
| Livewire | 3.x | Constitution AP-01, Spec01 FR-007 |
| PostgreSQL | 17 | Constitution AP-01, Spec01 FR-002 |
| Redis | 7.x | ADR-001 *(assumption for minor version)* |
| Pest PHP | 3.x | ADR-001, Spec01 FR-009 |
| PHPStan | Level 8 | Constitution DoD |
| Tailwind CSS | 4.x | ADR-001 *(assumption)* |

### 1.2 Planning Assumptions

| Component | Proposed Baseline | Rationale |
|-----------|-------------------|-----------|
| PHP | 8.4 | ADR-001 |
| Node.js | 20 LTS | Vite 6 / Laravel 13frontend build |
| Alpine.js | 3.14+ | Bundled with Livewire |
| Larastan | ^3.0 | PHPStan Laravel integration |
| Laravel Pint | ^1.0 | Constitution DoD |
| Laravel Horizon | ^5.0 | Spec01 assumption; queue monitoring |
| Laravel Telescope | ^5.0 | Spec01 assumption; local-only |

### 1.3 Runtime Components

| Layer | Component | Purpose |
|-------|-----------|---------|
| Application | PHP-FPM 8.4 in Sail container | Request handling |
| Web | Nginx (via Sail) | HTTP termination |
| Database | PostgreSQL 17 | Primary persistence |
| Cache/Queue | Redis 7 | Cache, queue, session |
| Build | Node.js + Vite | Tailwind/Livewire assets |

### 1.4 Deferred (Not Spec01)

Fortify, Sanctum, Sentry, MinIO, Excel/DomPDF, Laravel Pulse — per Spec01 assumptions and [research.md](./research.md) R-10.

---

## 2. Modular Monolith Architecture

### 2.1 Core Modules (Spec01 FR-004)

| Module | Bounded Context | Constitutional Alias | Future Owner Entities |
|--------|-----------------|----------------------|----------------------|
| **Identity** | Authentication, users, RBAC | — | User, Role, Permission |
| **Employee** | Personnel records | — | Employee, Dependent, ExternalPerson |
| **Request** | Accommodation requests | — | Request, MissionRequest, FamilyRequest |
| **Approval** | Four-stage workflow | `Workflow` | RequestApproval, ApprovalStage |
| **Dormitory** | Facility inventory | — | Dormitory, Room, Bed |
| **Allocation** | Bed/room assignment | — | Allocation, AllocationItem |
| **Lottery** | Lottery programs & draws | — | LotteryProgram, Registration, Result |
| **Voucher** | External dormitory vouchers | Lottery sub-domain | VoucherCode, VoucherRecord |
| **Notification** | In-app notifications | — | Notification, Template |
| **Audit** | Immutable audit trail | — | AuditLog |

`CheckIn` and `Report` are constitution modules deferred to later specs.

### 2.2 Layer Structure (Per Module)

```text
app/Modules/{Module}/
├── Domain/
│   ├── Entities/          # Pure PHP domain entities (extend BaseEntity)
│   ├── ValueObjects/      # Immutable value types
│   ├── Events/            # Domain events (extend BaseDomainEvent)
│   ├── Services/          # Domain services (pure business rules)
│   └── Contracts/         # Repository interfaces
├── Application/
│   ├── Actions/           # Use cases (single-purpose commands)
│   ├── DTOs/              # Data transfer objects
│   └── Services/          # Application orchestration
├── Infrastructure/
│   ├── Models/            # Eloquent persistence adapters
│   ├── Repositories/      # BaseRepository implementations
│   └── Jobs/              # Queue jobs (future)
└── Presentation/
    ├── Livewire/          # Livewire components
    ├── Http/
    │   ├── Controllers/   # Thin controllers (if needed)
    │   └── Requests/      # Form request validation
    └── Views/             # Module-specific Blade views
```

**Dependency rule**: `Presentation → Application → Domain ← Infrastructure`

### 2.3 Shared Kernel

```text
app/Shared/
├── Domain/
│   ├── BaseEntity.php
│   ├── BaseValueObject.php
│   ├── BaseDomainEvent.php
│   └── Contracts/
│       └── BaseRepository.php
├── Application/
│   └── (cross-cutting application services — future)
└── Infrastructure/
    ├── ModuleServiceProvider.php
    └── SharedServiceProvider.php
```

| Concern | Location | Notes |
|---------|----------|-------|
| Base abstractions | `Shared/Domain/` | Spec01 FR-012 |
| Cross-module DTOs | `Shared/Application/` | Empty in Spec01 |
| Module provider base | `Shared/Infrastructure/` | Spec01 Key Entities |
| Global helpers | Avoid; use value objects | Constitution AP-03 |

### 2.4 Cross-Module Communication

- **Synchronous**: Application Services expose read/query methods; consumer modules inject via DI
- **Asynchronous**: Domain Events via Laravel event bus (Application layer dispatches)
- **Prohibited**: Cross-module Eloquent relationships, cross-module FK constraints, Domain→Infrastructure imports

See [contracts/architecture-rules.md](./contracts/architecture-rules.md).

---

## 3. Database Foundation Strategy

### 3.1 PostgreSQL Conventions

| Concern | Convention |
|---------|------------|
| Primary keys | `uuid` with `gen_random_uuid()` default (pgcrypto) |
| Timestamps | `timestamptz`; `created_at`, `updated_at`; always UTC |
| Soft deletes | `deleted_at timestamptz NULL` when needed |
| Audit metadata | `jsonb` columns for state snapshots |
| Naming | `snake_case`; plural table names |
| Cross-module refs | UUID column, no FK |
| Intra-module FKs | Encouraged |

### 3.2 Required Extensions (Spec01 FR-015)

| Extension | Purpose | Migration |
|-----------|---------|-----------|
| `uuid-ossp` | UUID generation (legacy compat) | `foundation` migration |
| `pgcrypto` | `gen_random_uuid()` | `foundation` migration |
| `btree_gist` | Exclusion constraints | Deferred to Allocation spec |

### 3.3 Migration Organization

```text
database/migrations/
├── foundation/
│   └── 0001_enable_postgresql_extensions.php
├── identity/          # (empty scaffold)
├── employee/
├── request/
├── approval/
├── dormitory/
├── allocation/
├── lottery/
├── voucher/
├── notification/
└── audit/
```

**Principles**:
- Foundation migrations run first (extensions, shared config tables later)
- Each module owns its subdirectory; loaded via `ModuleServiceProvider`
- Every migration MUST have `down()` rollback (Constitution DoD)
- No domain-specific tables in Spec01 beyond package stubs

---

## 4. Redis and Queue Foundation

### 4.1 Configuration Baseline

| Laravel Config | Value | Purpose |
|----------------|-------|---------|
| `CACHE_STORE` | `redis` | Application cache |
| `QUEUE_CONNECTION` | `redis` | Job queue |
| `SESSION_DRIVER` | `redis` | Session storage |

### 4.2 Redis Usage (Foundation Only)

| Use Case | Spec01 Status |
|----------|---------------|
| Cache connectivity test | Required (SC-008) |
| Queue driver wiring | Required (no jobs) |
| Session storage | Configured |
| Horizon dashboard | Installed, dev access |
| Cache invalidation via events | Deferred |

### 4.3 Queue Principles (Constitution AP-08)

Documented for future implementation:
- Jobs must be idempotent
- Failed job retry with exponential backoff
- Critical jobs monitored via Horizon

No job classes created in Spec01.

---

## 5. Development Environment

### 5.1 Laravel Sail Setup

| Service | Image | Port |
|---------|-------|------|
| `laravel.test` | Sail PHP 8.4 runtime | 80 |
| `pgsql` | `postgres:17` | 5432 |
| `redis` | `redis:7-alpine` | 6379 |

**Optional**: Mailpit (Sail default) for future notification dev — not required for Spec01 acceptance.

### 5.2 Environment Files

`.env.example` MUST include (Spec01 FR-016):

```env
APP_NAME=DormSys
APP_ENV=local
APP_LOCALE=fa
APP_TIMEZONE=UTC
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=dormsys
DB_USERNAME=sail
DB_PASSWORD=password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 5.3 Developer Workflow

```bash
composer install
cp .env.example .env
php artisan key:generate
./vendor/bin/sail up -d
sail artisan migrate
sail artisan test
```

### 5.4 README (Spec01 FR-017)

Root `README.md` documents prerequisites, Sail commands, test/quality commands, and link to [quickstart.md](./quickstart.md).

---

## 6. Testing Infrastructure

### 6.1 Framework

- **Pest PHP 3** with `pest-plugin-laravel`
- `tests/Pest.php` configures case bindings
- `phpunit.xml` configures PostgreSQL test database

### 6.2 Test Organization

```text
tests/
├── Architecture/
│   ├── LayerDependencyTest.php
│   └── ModuleBoundaryTest.php
├── Unit/
│   ├── Shared/
│   │   └── BaseAbstractionsTest.php
│   └── Modules/
│       └── (per-module unit tests — future)
├── Feature/
│   └── Foundation/
│       ├── ApplicationBootTest.php
│       ├── DatabaseConnectionTest.php
│       ├── RedisConnectionTest.php
│       └── ModuleStructureTest.php
└── Integration/
    └── (future repository tests)
```

### 6.3 Foundation Test Scenarios

| Test | Validates |
|------|-----------|
| Application boot | Laravel starts without error |
| PostgreSQL connection | `DB::connection()->getPdo()` |
| Redis cache round-trip | `Cache::put/get` |
| Module structure | 10 modules × 4 layers |
| Base abstraction extension | Sample entity extends `BaseEntity` |
| Architecture rules | Layer and module boundaries |

### 6.4 Architecture Testing Strategy

Enforce Constitution AP-03 and AP-04 via Pest `arch()` tests — see [contracts/architecture-rules.md](./contracts/architecture-rules.md). Failures block CI.

**Coverage**: 80% domain/application target deferred to next feature per Spec01 assumptions.

---

## 7. Code Quality Foundation

### 7.1 Tools

| Tool | Config File | Level |
|------|-------------|-------|
| PHPStan + Larastan | `phpstan.neon` | Level 8 |
| Laravel Pint | `pint.json` (default) | Laravel preset |

### 7.2 Composer Scripts

```json
{
  "scripts": {
    "phpstan": "phpstan analyse",
    "pint": "pint"
  }
}
```

### 7.3 Local vs CI

| Check | Local Command | CI |
|-------|---------------|-----|
| Format | `sail composer run pint` | `composer run pint -- --test` |
| Static analysis | `sail composer run phpstan` | `composer run phpstan` |
| Tests | `sail artisan test` | `php artisan test` |

### 7.4 Git Hooks (Optional)

Pre-commit hook running `pint --test` — optional, not required for Spec01. CI is the enforcement gate.

---

## 8. Package and Service Provider Integration

### 8.1 Required Packages (Spec01)

| Package | Foundation Action |
|---------|-------------------|
| `livewire/livewire` | Install; create `resources/views/layouts/app.blade.php` stub |
| `spatie/laravel-permission` | Install; publish migration |
| `spatie/laravel-model-states` | Install; publish config |
| `spatie/laravel-activitylog` | Install; publish migration |
| `morilog/jalali` | Install; register facade |
| `laravel/horizon` | Install; `HorizonServiceProvider`; gate dev access |
| `laravel/telescope` | Install; `local` env only |

### 8.2 Dev Dependencies

| Package | Purpose |
|---------|---------|
| `pestphp/pest` | Test runner |
| `pestphp/pest-plugin-laravel` | Laravel integration |
| `pestphp/pest-plugin-arch` | Architecture tests |
| `phpstan/phpstan` | Static analysis |
| `nunomaduro/larastan` | Laravel PHPStan rules |
| `laravel/pint` | Code formatter |

### 8.3 Service Provider Organization

```text
bootstrap/providers.php
├── AppServiceProvider
├── SharedServiceProvider          # Shared kernel bindings
├── HorizonServiceProvider
├── TelescopeServiceProvider       # local only
├── IdentityServiceProvider
├── EmployeeServiceProvider
├── RequestServiceProvider
├── ApprovalServiceProvider
├── DormitoryServiceProvider
├── AllocationServiceProvider
├── LotteryServiceProvider
├── VoucherServiceProvider
├── NotificationServiceProvider
└── AuditServiceProvider
```

See [contracts/module-service-provider.md](./contracts/module-service-provider.md).

### 8.4 Logging (Spec01 FR-018)

`config/logging.php`: production channel uses `json` formatter with `request_id` context.

---

## 9. CI Foundation

GitHub Actions workflow `.github/workflows/ci.yml` per [contracts/ci-pipeline.md](./contracts/ci-pipeline.md).

**Pipeline summary**:
1. Checkout + PHP 8.4 setup
2. PostgreSQL 17 + Redis 7 service containers
3. `composer install`
4. `.env` + `key:generate`
5. `php artisan migrate --force`
6. `pint --test`
7. `phpstan`
8. `php artisan test`

**Target**: < 3 minutes (SC-007).

---

## 10. Implementation Sequence

Phases are ordered for `/tasks` conversion. Each phase has dependencies and acceptance checks.

```text
Phase 1 ──► Phase 2 ──► Phase 3 ──► Phase 4
                                      │
                    ┌─────────────────┼─────────────────┐
                    ▼                 ▼                 ▼
               Phase 5           Phase 6           Phase 7
                    │                 │                 │
                    └────────┬────────┴────────┬────────┘
                             ▼                 ▼
                        Phase 8           Phase 9
```

### Phase 1: Laravel Project Bootstrap

**Scope**: `composer create-project`, base `.env.example`, `.gitignore`, `APP_TIMEZONE=UTC`, `APP_LOCALE=fa`

**Depends on**: Nothing

**Acceptance**:
- [ ] `composer install` succeeds (SC-001)
- [ ] `php artisan --version` shows Laravel 12
- [ ] Application boots without error

---

### Phase 2: Sail & Infrastructure Services

**Scope**: Sail installation, PostgreSQL 17 + Redis 7 services, database/redis env wiring

**Depends on**: Phase 1

**Acceptance**:
- [ ] `sail up -d` starts all containers (SC-002)
- [ ] PostgreSQL connection succeeds
- [ ] Redis connection succeeds (SC-008)

---

### Phase 3: Database Foundation

**Scope**: Foundation migration (`uuid-ossp`, `pgcrypto`), migration directory structure, rollback tested

**Depends on**: Phase 2

**Acceptance**:
- [ ] `sail artisan migrate` succeeds (SC-003)
- [ ] Extensions verified via `psql`
- [ ] `sail artisan migrate:rollback` succeeds

---

### Phase 4: Modular Structure & Shared Kernel

**Scope**: 10 module directories with 4 layers, `app/Shared/` structure, base abstractions, `ModuleServiceProvider`, module service providers registered

**Depends on**: Phase 1

**Acceptance**:
- [ ] All 10 modules with Domain/Application/Infrastructure/Presentation (SC-005)
- [ ] Base classes compile and sample extension works (SC-006)
- [ ] Architecture tests pass

---

### Phase 5: Platform Package Integration

**Scope**: Install Spatie packages, Livewire, Jalali, Horizon, Telescope; publish configs/migrations; Tailwind RTL + Livewire layout stub

**Depends on**: Phases 1, 4

**Acceptance**:
- [ ] All Spec01 FR-007–FR-010 packages installed
- [ ] `sail artisan migrate` runs package migrations
- [ ] Welcome page renders with Livewire/Tailwind (SC-009)
- [ ] Telescope accessible in local only

---

### Phase 6: Testing Foundation

**Scope**: Pest setup, foundation feature tests, architecture tests, sample module unit test

**Depends on**: Phases 3, 4, 5

**Acceptance**:
- [ ] `sail artisan test` all green (SC-004)
- [ ] Architecture boundary tests enforced
- [ ] Test DB uses PostgreSQL (not SQLite)

---

### Phase 7: Code Quality Tooling

**Scope**: PHPStan/Larastan level 8 config, Pint, composer scripts

**Depends on**: Phase 4

**Acceptance**:
- [ ] `composer run phpstan` zero errors (SC-010)
- [ ] `composer run pint -- --test` passes

---

### Phase 8: CI Pipeline

**Scope**: `.github/workflows/ci.yml` per contract

**Depends on**: Phases 6, 7

**Acceptance**:
- [ ] Push triggers CI; all jobs green (SC-007)
- [ ] Pipeline completes < 3 minutes

---

### Phase 9: Documentation & Final Validation

**Scope**: `README.md`, health endpoint stub, quickstart validation

**Depends on**: All prior phases

**Acceptance**:
- [ ] README setup instructions complete (FR-017)
- [ ] [quickstart.md](./quickstart.md) checklist fully passable
- [ ] `/up` returns 200

---

## Project Structure

### Documentation (this feature)

```text
specs/001-technical-foundation/
├── plan.md              # This file
├── research.md          # Phase 0 decisions
├── data-model.md        # Foundation abstractions
├── quickstart.md        # Validation guide
├── contracts/           # Interface contracts
│   ├── architecture-rules.md
│   ├── ci-pipeline.md
│   ├── health-endpoints.md
│   ├── module-service-provider.md
│   └── repository-interface.md
└── tasks.md             # Phase 2 (/speckit-tasks — NOT created here)
```

### Source Code (repository root — target state)

```text
dormsys/
├── app/
│   ├── Modules/
│   │   ├── Identity/       {Domain,Application,Infrastructure,Presentation}
│   │   ├── Employee/
│   │   ├── Request/
│   │   ├── Approval/
│   │   ├── Dormitory/
│   │   ├── Allocation/
│   │   ├── Lottery/
│   │   ├── Voucher/
│   │   ├── Notification/
│   │   └── Audit/
│   └── Shared/
│       ├── Domain/
│       ├── Application/
│       └── Infrastructure/
├── bootstrap/
│   └── providers.php
├── config/
├── database/
│   └── migrations/
│       ├── foundation/
│       └── {module}/
├── resources/
│   ├── views/layouts/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Architecture/
│   ├── Feature/Foundation/
│   └── Unit/Shared/
├── .github/workflows/ci.yml
├── compose.yaml             # Sail
├── phpstan.neon
├── composer.json
└── README.md
```

**Structure Decision**: Single Laravel project (modular monolith). No separate frontend/backend repositories. All business code under `app/Modules/` with shared kernel in `app/Shared/`.

---

## Complexity Tracking

> No constitution violations requiring justification.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| — | — | — |

---

## Implementation Alignment Notes (Post–Hard Freeze)

These notes clarify **spec01 code** against frozen boundary decisions. They do not amend `catalog-decisions.md` CD entries.

### `BaseModel::getId()` ↔ CD-012 (OQ-01)

CD-012 assigns `identity_id` at **Employee creation** (creation-time / first-linkage), not at a post-persist-only moment.

`App\Support\Models\BaseModel::getId()` therefore:

- throws when the UUID primary key is **not yet assigned**;
- returns a valid UUID after the Eloquent `creating` event (`HasUuid`), even before `save()`;
- does **not** require `$model->exists`.

A persist-only `getId()` would conflict with CD-012 creation-time semantics and is prohibited.

---

## Generated Artifacts

| Artifact | Path | Status |
|----------|------|--------|
| Implementation plan | `specs/001-technical-foundation/plan.md` | ✅ Complete |
| Research decisions | `specs/001-technical-foundation/research.md` | ✅ Complete |
| Data model (foundation) | `specs/001-technical-foundation/data-model.md` | ✅ Complete |
| Quickstart validation | `specs/001-technical-foundation/quickstart.md` | ✅ Complete |
| Contracts | `specs/001-technical-foundation/contracts/` | ✅ Complete (5 files) |
| Tasks | `specs/001-technical-foundation/tasks.md` | ⏳ Next: `/speckit-tasks` |
