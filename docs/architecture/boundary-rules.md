# Module Boundary Rules

**Status:** Approved — reflects post-repair codebase (2026-07-04)  
**CI gate (mandatory):** `composer run arch`  
**CI gate (full suite):** `php artisan test`  
**Advisory debt visibility:** `composer run arch:advisory`  
**Inventory source:** `tests/Architecture/architecture.php`  
**Enforcement matrix:** [ci-enforcement-matrix.md](./ci-enforcement-matrix.md)  
**Known exceptions:** [known-exceptions-registry.md](./known-exceptions-registry.md)

Rules marked **ENFORCED** fail CI when violated. Rules marked **POLICY** are review expectations not fully automated.  
**PR governance:** [pr-review-checklist.md](./pr-review-checklist.md) · author attestation: `.github/pull_request_template.md`

---

## Module layout

```
app/Modules/{Module}/
├── Domain/           Pure PHP — no Laravel persistence, no foreign modules
├── Application/      Use cases, contracts (ports), DTOs
├── Infrastructure/   Eloquent, repositories, module service provider
└── Presentation/     HTTP, Livewire, console
```

Cross-module composition for approved edges lives in `app/Integrations/` and is wired by `app/Providers/IntegrationServiceProvider.php`.

---

## Module inventory

### Full architecture matrix (**ENFORCED**)

Defined in `tests/Architecture/architecture.php` → `architectureModuleNames()`:

| Module | Service provider | Matrix tests |
|--------|------------------|--------------|
| Identity | `IdentityServiceProvider` | `ModuleBoundaryTest` + `IdentitySupplierBoundaryTest` |
| Employee | `EmployeeServiceProvider` | `ModuleBoundaryTest` + `EmployeeSupplierBoundaryTest` |
| Request | `RequestServiceProvider` | `ModuleBoundaryTest` + `RequestConsumerBoundaryTest` |
| Workflow | `WorkflowServiceProvider` | `ModuleBoundaryTest` |
| Dormitory | `DormitoryServiceProvider` | `ModuleBoundaryTest` |
| Allocation | `AllocationServiceProvider` | `ModuleBoundaryTest` + `AllocationBoundaryTest` |
| CheckIn | `CheckInServiceProvider` | `ModuleBoundaryTest` + `CheckInBoundaryTest` |
| Lottery | `LotteryServiceProvider` | `ModuleBoundaryTest` + `LotterySupplierBoundaryTest` |
| Voucher | `VoucherServiceProvider` | `ModuleBoundaryTest` + `VoucherBoundaryTest` |
| Notification | `NotificationServiceProvider` | `ModuleBoundaryTest` + `NotificationBoundaryTest` |
| Audit | `AuditServiceProvider` | `ModuleBoundaryTest` + `AuditBoundaryTest` |
| Reporting | `ReportingServiceProvider` | `ModuleBoundaryTest` + `ReportingBoundaryTest` |

Provider registration and four-layer directories for these modules: **ENFORCED** by `tests/Architecture/ServiceProviderRegistrationTest.php`.

`architectureMatrixExcludedActiveModules()` is empty — any future temporary exclusion requires explicit architecture approval.

Shared kernel (any module may use): `App\Support`, `App/Shared` — **ENFORCED** by `LayerDependencyTest.php`.

---

## Allowed dependency directions

| From | To | Status |
|------|-----|--------|
| Presentation | Own Application, own Domain | Implicit Laravel layering |
| Infrastructure | Own Application, own Domain | Required for repository mapping |
| Application | Own Domain | Standard use-case orchestration |
| Application | Foreign **Application** contracts + DTOs | **ENFORCED** — `ModuleBoundaryTest` allows; blocks foreign Domain/Infra/Presentation |
| Domain | Own module only + `App\Support` / `App/Shared` | **ENFORCED** |
| `app/Integrations/*` | Application contracts + DTOs from any module | **POLICY** — see [integration-layer-policy.md](./integration-layer-policy.md) |

### Layer inward flow (**ENFORCED** — `tests/Architecture/LayerDependencyTest.php`)

```
Presentation  ──┐
                ├──► Application ──► Domain
Infrastructure ─┘
```

Infrastructure and Presentation are siblings; neither imports the other (**ENFORCED**).

---

## Forbidden dependency directions

| Violation | Example | Enforcement |
|-----------|---------|-------------|
| Domain → Infrastructure | `use App\Modules\Request\Infrastructure\...` in Domain | **ENFORCED** — `LayerDependencyTest`: `domain layer does not depend on infrastructure` |
| Domain → Eloquent | `use Illuminate\Database\Eloquent\*` in Domain | **ENFORCED** — `domain layer does not depend on eloquent` |
| Domain → Facades | `use Illuminate\Support\Facades\*` in Domain | **ENFORCED** — `domain layer does not depend on laravel facades` |
| Domain → foreign module | Any `App\Modules\{Other}\*` in Domain | **ENFORCED** — per-pair rules in `ModuleBoundaryTest.php` |
| Application → own Infrastructure | Concrete repository in action constructor | **ENFORCED** — `application layer does not depend on infrastructure` |
| Application → foreign Domain | Any `App\Modules\{Other}\Domain\*` in Application | **ENFORCED** — `ModuleBoundaryTest` (12 matrix modules) |
| Application → foreign Infrastructure | `Lottery\Infrastructure\*` in Request Application | **ENFORCED** — `ModuleBoundaryTest` |
| Infrastructure → foreign module (any layer) | Cross-module Eloquent/repo in Infrastructure | **ENFORCED** for matrix modules — `ModuleBoundaryTest` |
| Cross-module Eloquent / FK | Foreign keys across module tables | **POLICY** — constitution; not Pest-arch scanned |
| Duplicate port bindings | Same port in module provider + `IntegrationServiceProvider` | **POLICY** — review + grep; was removed for `PendingRequestReadPort` |

---

## Rules by layer (with real examples)

### Domain

**ENFORCED:** no Infrastructure, Eloquent, Facades, foreign modules.

**Good** — Request entity (own module only):

```php
// app/Modules/Request/Domain/Entities/Request.php
use App\Modules\Request\Domain\ValueObjects\RequestId;
```

**Good** — state without Infrastructure model binding (post-repair pattern):

```php
// app/Modules/Request/Domain/States/RequestState.php
use Spatie\ModelStates\State;  // no @extends State<RequestModel>
```

**Bad** — do not reintroduce:

```php
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
```

### Application

**ENFORCED:** inject contracts/ports; no own Infrastructure imports.

**Good** — foreign Application contract:

```php
// app/Modules/Allocation/Application/Services/ProposedAllocationConsumer.php
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;
```

**Good** — internal gateway (implementation in Integrations):

```php
// app/Modules/Request/Application/Services/SubmitRequestAction.php
use App\Modules\Request\Application\Contracts\Internal\RequestEligibilityGatewayContract;
```

**Good** — cross-module role check via Identity read contract (post–CheckIn closure):

```php
// app/Modules/CheckIn/Application/Services/OperatorRoleGate.php
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
```

**Bad** — foreign Domain (do not copy):

```php
use App\Modules\Identity\Domain\ValueObjects\UserId; // in another module's Application layer
```

### Infrastructure

**ENFORCED (matrix modules):** no foreign module imports.

**Good** — own contract implementation:

```php
// app/Modules/Allocation/Infrastructure/Repositories/AllocationRepository.php
implements AllocationRepositoryContract
```

**Legacy tolerated** — Reporting reads Audit via Application layer (not Infrastructure isolation for `Audit\Application\*`):

```php
// app/Modules/Reporting/Infrastructure/Adapters/AuditHistorySourceReadAdapter.php
use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
```

Guarded by **ENFORCED** custom rule in `ReportingBoundaryTest.php` (single adapter file for `AuditHistoryReadContract`).

**Removed anti-pattern** — do not recreate:

- `app/Modules/Allocation/Infrastructure/Adapters/RequestReadAdapter.php`
- `app/Modules/Request/Infrastructure/Adapters/PendingRequestReadAdapter.php`

---

## Approved cross-module edges (current)

| Consumer port | Bridge / adapter | Supplier contract | Binding location |
|---------------|------------------|-------------------|------------------|
| `Employee\...\PendingRequestReadPort` | `Integrations\Request\PendingRequestReadBridge` | `Request\...\PendingRequestQueryPort` | `IntegrationServiceProvider::register()` |
| `Request\...\RequestEligibilityGatewayContract` | `Integrations\Request\EmployeeEligibilityBridge` | `Employee\...\EmployeeEligibilityContract` | `IntegrationServiceProvider::register()` |
| `Allocation\...\ApprovedRequestReadPort` | `Integrations\Allocation\ApprovedRequestReadBridge` | `Request\...\RequestReadContract` | `IntegrationServiceProvider::register()` |
| `CheckIn\...\AllocationAssignmentReadPort` | `Integrations\CheckIn\AllocationAssignmentReadBridge` | `Allocation\...\AllocationReadContract` | `IntegrationServiceProvider::register()` |
| `Lottery\...\ProposedAllocationPort` | `Allocation\...\ProposedAllocationConsumer` (service, not bridge file) | — | `IntegrationServiceProvider::register()` |

---

## CI test map

| Rule | Test file | Test name pattern |
|------|-----------|-------------------|
| Layer inward dependencies | `LayerDependencyTest.php` | `domain/application/infrastructure layer does not depend on *` |
| Module pair isolation | `ModuleBoundaryTest.php` | `{module} domain/infrastructure/presentation/application is isolated from {foreign}` |
| Vacuous-rule guard | `ArchTestCanaryTest.php` | Ensures arch expectations are non-empty |
| Provider + layer dirs | `ServiceProviderRegistrationTest.php` | `each module has a service provider`, `exposes required layer directories` |
| Request ↔ Employee | `RequestConsumerBoundaryTest.php` | BT-R05 / BT-R09 rules + bridge method parity |
| Allocation suppliers | `AllocationBoundaryTest.php` | Blocks Request/Lottery/Dormitory/Employee Infrastructure |
| Lottery ↔ Request | `LotterySupplierBoundaryTest.php` | Blocks foreign Infrastructure; documents `RequestReadAdapter` |
| Reporting ↔ Audit | `ReportingBoundaryTest.php` | Blocks Audit Infrastructure; single `AuditHistoryReadContract` reference |
| CheckIn ↔ Allocation | `CheckInBoundaryTest.php` | Blocks Allocation Infrastructure persistence/repos; full matrix via `ModuleBoundaryTest` |

Run before merge:

```bash
php artisan test tests/Architecture/
composer run phpstan
```

---

## Adding a new matrix module

1. Create four layers under `app/Modules/{Name}/`
2. Add `{Name}ServiceProvider` under `Infrastructure/Providers/`
3. Register in `bootstrap/providers.php` **before** `IntegrationServiceProvider`
4. Add `{Name}` to `architectureModuleNames()` in `tests/Architecture/architecture.php`
5. Add module-specific boundary tests if the module has known supplier edges
6. Run full architecture suite — `ModuleBoundaryTest` generates pairwise rules automatically

---

## Related documents

- [integration-layer-policy.md](./integration-layer-policy.md)
- [pr-review-checklist.md](./pr-review-checklist.md)
- [decision-record.md](./decision-record.md)
