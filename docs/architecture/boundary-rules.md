# Module Boundary Rules

**Status:** Active — enforced in CI  
**Audience:** Developers, PR reviewers  
**Enforcement:** `tests/Architecture/` (670+ Pest architecture tests) + PHPStan level 8

This document describes **what the repository actually enforces today**, not an idealized future state.

---

## Module layout

Every bounded context lives under:

```
app/Modules/{ModuleName}/
├── Domain/           # Pure business rules — no Laravel, no Eloquent, no foreign modules
├── Application/      # Use cases, contracts (ports), DTOs, application services
├── Infrastructure/   # Eloquent models, repositories, module-local adapters, service provider
└── Presentation/     # HTTP, Livewire, console — depends inward only
```

**Registered modules** (full matrix in `tests/Architecture/architecture.php`):

`Identity`, `Employee`, `Request`, `Workflow`, `Dormitory`, `Allocation`, `Lottery`, `Voucher`, `Notification`, `Audit`, `Reporting`

**Active but partially enforced:** `CheckIn` — registered in `bootstrap/providers.php`, covered only by `tests/Architecture/CheckInBoundaryTest.php` (not the full module matrix). Treat CheckIn with the same rules manually until added to `architectureModuleNames()`.

**Shared kernel** (any module may use):

- `App\Support/*`
- `App/Shared/*`

---

## Allowed dependency directions

| From | To | Rule |
|------|-----|------|
| **Presentation** | Application, Domain (own module) | Controllers/Livewire call application services |
| **Infrastructure** | Application, Domain (own module) | Repositories implement application contracts |
| **Application** | Domain (own module) | Actions orchestrate domain entities/value objects |
| **Application** | **Application (foreign module)** | Cross-module reads/writes only via **public contracts** and DTOs |
| **Domain** | Nothing outside own module + shared kernel | Pure PHP only |
| **Integrations** (`app/Integrations/`) | Application contracts + DTOs from any module | Bridge layer — see [integration-layer-policy.md](./integration-layer-policy.md) |
| **Any module** | `App\Support`, `App/Shared` | Shared value objects, base types |

### Layer rules (all modules)

Enforced by `tests/Architecture/LayerDependencyTest.php`:

```
Presentation → Application → Domain
Infrastructure → Application → Domain
```

Infrastructure and Presentation are **siblings** — neither imports the other.

---

## Forbidden dependency directions

| Violation | Example | Enforced by |
|-----------|---------|-------------|
| Domain → Infrastructure | `use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel` in Domain | `LayerDependencyTest` |
| Domain → Eloquent | `use Illuminate\Database\Eloquent\Model` in Domain | `LayerDependencyTest` |
| Domain → Facades | `use Illuminate\Support\Facades\DB` in Domain | `LayerDependencyTest` |
| Application → Infrastructure (own module) | Action injecting `RequestRepository` concrete class | `LayerDependencyTest` |
| Application → Presentation | Application service importing Livewire component | `LayerDependencyTest` |
| Domain → foreign module | `use App\Modules\Employee\Domain\...` in Request Domain | `ModuleBoundaryTest` |
| Application → foreign **Domain** | `use App\Modules\Identity\Domain\ValueObjects\UserId` in CheckIn Application | `ModuleBoundaryTest` (when module in matrix) |
| Application → foreign **Infrastructure** | `use App\Modules\Lottery\Infrastructure\...` in Allocation Application | `ModuleBoundaryTest` |
| Infrastructure → foreign module | `use App\Modules\Audit\...` in Request Infrastructure | `ModuleBoundaryTest` |
| Cross-module Eloquent / FK | Foreign-key constraint to another module's table | Constitution + code review |
| Module provider binds foreign bridge | `PendingRequestReadPort` bound in `EmployeeServiceProvider` **and** `IntegrationServiceProvider` | Code review |

---

## Rules by layer

### Domain

**May contain:** entities, value objects, enums, domain events, domain exceptions, domain services, state machines (pure PHP).

**Must not contain:** Eloquent models (those belong in `Infrastructure/Persistence/Models/`), Laravel imports, imports from any `App\Modules\{Other}\*`.

**Good** — Request domain entity:

```php
// app/Modules/Request/Domain/Entities/Request.php
namespace App\Modules\Request\Domain\Entities;

use App\Modules\Request\Domain\ValueObjects\RequestId;
// own-module domain only
```

**Bad** — domain state tied to Eloquent (fixed pattern; do not reintroduce):

```php
// FORBIDDEN
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
/** @extends State<RequestModel> */
```

State classes use `spatie/laravel-model-states` without referencing Infrastructure model types in PHPDoc or imports.

---

### Application

**May contain:** actions, application services, **contracts** (ports), DTOs, internal gateway interfaces owned by the consuming module.

**Must inject:** abstractions (`*Contract`, `*Port`) — never concrete Infrastructure classes.

**Cross-module access:** only through foreign **Application** contracts/DTOs, never foreign Domain or Infrastructure.

**Good** — Allocation consumes lottery outcome via port:

```php
// app/Modules/Allocation/Application/Services/ProposedAllocationConsumer.php
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort; // foreign Application contract — OK
```

**Good** — Request eligibility via internal gateway (implementation in Integrations):

```php
// app/Modules/Request/Application/Services/CreatePersonalRequestAction.php
use App\Modules\Request\Application\Contracts\Internal\RequestEligibilityGatewayContract;
```

**Bad** — Application action using foreign domain type:

```php
// app/Modules/CheckIn/Application/Services/OperatorRoleGate.php — KNOWN GAP
use App\Modules\Identity\Domain\ValueObjects\UserId; // foreign Domain — reject in PR
```

**Bad** — Application importing Infrastructure:

```php
// FORBIDDEN
use App\Modules\Allocation\Infrastructure\Repositories\AllocationRepository;
```

---

### Infrastructure

**May contain:** Eloquent models, repositories (implementing Application contracts), module-local null stubs, queries, jobs, listeners, **module service provider**.

**Must not import:** foreign modules (any layer), Presentation.

**Own-module only:** Infrastructure → own Application + own Domain is required for repository mapping.

**Good** — repository implements contract:

```php
// app/Modules/Allocation/Infrastructure/Repositories/AllocationRepository.php
use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Domain\Models\Allocation;
```

**Bad** — deleted anti-pattern (do not recreate):

```php
// FORBIDDEN — was removed
// app/Modules/Allocation/Infrastructure/Adapters/RequestReadAdapter.php
use App\Modules\Request\Infrastructure\...;
```

**Legacy exception (allowed, do not copy for new work):** Reporting module contains `AuditHistorySourceReadAdapter` that calls `AuditHistoryReadContract`. This is guarded by `tests/Architecture/ReportingBoundaryTest.php` (single adapter file rule). New cross-module wiring belongs in `app/Integrations/`.

---

### Integrations (`app/Integrations/`)

Not a business module — a **composition-only bridge layer**. Full policy: [integration-layer-policy.md](./integration-layer-policy.md).

**Current bridges:**

| Bridge | Implements (consumer port) | Delegates to (supplier contract) |
|--------|---------------------------|-----------------------------------|
| `Integrations/Request/PendingRequestReadBridge` | `Employee\...\PendingRequestReadPort` | `Request\...\PendingRequestQueryPort` |
| `Integrations/Request/EmployeeEligibilityBridge` | `Request\...\RequestEligibilityGatewayContract` | `Employee\...\EmployeeEligibilityContract` |
| `Integrations/Allocation/ApprovedRequestReadBridge` | `Allocation\...\ApprovedRequestReadPort` | `Request\...\RequestReadContract` |
| `Integrations/CheckIn/AllocationAssignmentReadBridge` | `CheckIn\...\AllocationAssignmentReadPort` | `Allocation\...\AllocationReadContract` |

All registered in `app/Providers/IntegrationServiceProvider.php` (must remain **last** in `bootstrap/providers.php`).

---

## Cross-module communication patterns

### Allowed

1. **Consumer injects port** → **Integration bridge** → **Supplier application contract**
2. **Consumer application service** → **Foreign application contract** (no bridge file required if consumer already depends only on the contract interface — e.g. Lottery `RequestReadAdapter` → `RequestReadContract`)
3. **Downstream read-only projection** (Reporting) → **Foreign application contract/DTO** via dedicated infrastructure adapter (legacy; prefer Integrations for new edges)

### Forbidden

1. Infrastructure adapter in module A calling module B repository or Eloquent model
2. Domain entity in module A referencing domain type from module B
3. Binding a foreign port implementation inside the **supplier's** or **consumer's** module provider when the edge is cross-context (use `IntegrationServiceProvider`)
4. Duplicate competing bindings for the same port in two providers

---

## CI verification

Run before opening a PR:

```bash
# Architecture tests (layer + module matrix + module-specific boundary tests)
php artisan test tests/Architecture/

# Static analysis
composer run phpstan

# Formatting
composer run pint
```

**Canary:** `tests/Architecture/ArchTestCanaryTest.php` ensures architecture rules are not empty/vacuous.

**Provider registration:** `tests/Architecture/ServiceProviderRegistrationTest.php` verifies every module in `architectureModuleNames()` has a provider registered in `bootstrap/providers.php`.

---

## Adding a new module

1. Create four layer directories under `app/Modules/{Name}/`
2. Add `{Name}ServiceProvider` under `Infrastructure/Providers/`
3. Register provider in `bootstrap/providers.php` (before `IntegrationServiceProvider`)
4. Add `{Name}` to `architectureModuleNames()` in `tests/Architecture/architecture.php`
5. Add module-specific boundary tests if the module has known supplier/consumer edges (see `LotterySupplierBoundaryTest.php`, `AllocationBoundaryTest.php` as templates)
6. Run full architecture suite — expect new cross-module matrix tests to generate automatically from `ModuleBoundaryTest.php`

---

## Related documents

- [integration-layer-policy.md](./integration-layer-policy.md)
- [pr-review-checklist.md](./pr-review-checklist.md)
- [decision-record.md](./decision-record.md)
- Governance source: `.specify/memory/constitution.md`, `.specify/docs/context-map.md`
- ADR outline: `.specify/docs/ADR/002-module-boundary-enforcement.md`
