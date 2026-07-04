# Architecture PR Review Checklist

**Status:** Active  
**Use when:** Reviewing any PR touching `app/Modules/`, `app/Integrations/`, `app/Providers/`, or `bootstrap/providers.php`  
**CI gate:** `php artisan test tests/Architecture/` must pass (670+ tests)

Copy this checklist into PR review comments or use as a self-review guide before requesting review.

---

## Quick reject criteria

Reject or request changes immediately if the PR:

- [ ] Adds `use App\Modules\{Foreign}\Domain\*` or `{Foreign}\Infrastructure\*` inside another module's Domain or Application layer
- [ ] Adds Eloquent, Facades, or Infrastructure imports to Domain
- [ ] Adds concrete Infrastructure classes to Application constructor injection
- [ ] Adds cross-module repository or Eloquent model access
- [ ] Binds a cross-module port in a module `*ServiceProvider` instead of `IntegrationServiceProvider`
- [ ] Introduces duplicate singleton binding for the same port interface
- [ ] Adds business logic to `app/Integrations/*`
- [ ] Fails architecture tests or PHPStan level 8

---

## 1. Domain purity check

**Scope:** `app/Modules/*/Domain/**/*.php`

| Check | Pass | Fail |
|-------|------|------|
| No `Illuminate\Database\Eloquent\*` imports | Pure PHP entities/VOs | Any Eloquent base class or trait in Domain |
| No `App\Modules\*\Infrastructure\*` imports | — | Domain importing persistence |
| No `Illuminate\Support\Facades\*` | — | `DB::`, `Cache::`, etc. in Domain |
| No foreign module imports | Only own-module + `App\Support` | `use App\Modules\Employee\...` in Request Domain |
| State classes don't reference Infrastructure models in PHPDoc | `RequestState` without `@extends State<Model>` | Generic bound to Eloquent model type |

**Spot-check commands:**

```bash
# Should return no matches for the touched module
rg "Infrastructure|Illuminate\\\\Database|Eloquent|Facades" app/Modules/{Module}/Domain/
rg "use App\\\\Modules\\\\(Employee|Request|Lottery|Audit|Allocation|Identity|Reporting|Voucher|Dormitory|Notification|Workflow)\\\\" app/Modules/{Module}/Domain/
```

---

## 2. Application contract usage check

**Scope:** `app/Modules/*/Application/**/*.php`

| Check | Pass | Fail |
|-------|------|------|
| Actions/services inject `*Contract`, `*Port` interfaces | `AllocationRepositoryContract` | `AllocationRepository` concrete class |
| No own-module Infrastructure imports | — | `use App\Modules\Request\Infrastructure\...` |
| Foreign access via Application contracts/DTOs only | `RequestReadContract`, `AuditEntryDto` | Foreign domain entity or repository |
| Cross-module writes go through defined command ports | `ProposedAllocationPort`, `AuditRecordingContract` | Direct mutation of foreign module state |
| Internal gateways use `Contracts/Internal/` namespace | `RequestEligibilityGatewayContract` | Public contract used for cross-module secret coupling |

**Good pattern:**

```php
public function __construct(
    private readonly ApprovedRequestReadPort $requests,
    private readonly AllocationRepositoryContract $allocations,
) {}
```

**Bad pattern:**

```php
public function __construct(
    private readonly RequestRepository $requests, // concrete Infrastructure
) {}
```

**Known gap to flag (not template):** `CheckIn/Application/Services/OperatorRoleGate.php` imports `Identity\Domain\ValueObjects\UserId` — reject **new** PRs that copy this; existing code is tracked for contract cleanup.

---

## 3. Cross-module dependency check

**Scope:** Any new `use App\Modules\{Other}*` statement

| Dependency type | Verdict | Action |
|-----------------|---------|--------|
| `{Other}\Application\Contracts\*` | ✅ Allowed | Verify port ownership (consumer vs supplier) |
| `{Other}\Application\DTOs\*` | ✅ Allowed | DTO must stay immutable/read-oriented |
| `{Other}\Domain\*` | ❌ Reject | Use contract with string/UUID/DTO instead |
| `{Other}\Infrastructure\*` | ❌ Reject | Use bridge + application contract |
| `{Other}\Presentation\*` | ❌ Reject | Never cross presentation boundaries |

**For new cross-module edges:**

- [ ] Port defined on **consumer** side
- [ ] Bridge in `app/Integrations/{Consumer}/` (unless trivial direct contract injection like Lottery→Request read)
- [ ] No new Infrastructure adapter in module A calling module B
- [ ] Context map relationship documented if new bounded-context edge (`.specify/docs/context-map.md`)

**Module-specific tests to verify exist or are updated:**

| Edge | Test file |
|------|-----------|
| Request ↔ Employee | `tests/Architecture/RequestConsumerBoundaryTest.php` |
| Allocation ↔ Request/Lottery/Dormitory/Employee | `tests/Architecture/AllocationBoundaryTest.php` |
| Lottery ↔ Request | `tests/Architecture/LotterySupplierBoundaryTest.php` |
| Reporting ↔ Audit | `tests/Architecture/ReportingBoundaryTest.php` |
| CheckIn ↔ Allocation | `tests/Architecture/CheckInBoundaryTest.php` |
| All modules (matrix) | `tests/Architecture/ModuleBoundaryTest.php` |

---

## 4. Provider binding correctness check

**Scope:** `*ServiceProvider.php`, `bootstrap/providers.php`, `IntegrationServiceProvider.php`

| Check | Pass | Fail |
|-------|------|------|
| Module provider binds own implementations only | `AllocationRepositoryContract → AllocationRepository` | `PendingRequestReadPort` in `EmployeeServiceProvider` |
| Cross-module bindings only in `IntegrationServiceProvider` | All `*Bridge` classes | Bridge binding in `RequestServiceProvider` |
| `IntegrationServiceProvider` is **last** in `bootstrap/providers.php` | Line 46 after all module providers | Integration registered before modules |
| Bindings in `register()`, not `boot()` | `IntegrationServiceProvider::register()` | Container bindings in `boot()` |
| New module provider added to `bootstrap/providers.php` | Registered and bootable | Orphan provider |
| New enforced module added to `architectureModuleNames()` | `tests/Architecture/architecture.php` | Module ships without matrix coverage |

**Current integration bindings (must not duplicate elsewhere):**

```
ApprovedRequestReadPort
AllocationAssignmentReadPort
RequestEligibilityGatewayContract
PendingRequestReadPort
ProposedAllocationPort
```

**Verify:**

```bash
rg "singleton\((ApprovedRequestReadPort|AllocationAssignmentReadPort|RequestEligibilityGatewayContract|PendingRequestReadPort|ProposedAllocationPort)" app/Modules/
# Should return no matches — all in IntegrationServiceProvider
```

---

## 5. Integration layer validation

**Scope:** `app/Integrations/**/*.php`

| Check | Pass | Fail |
|-------|------|------|
| Class is `final` | All bridges | Extensible bridge hierarchies |
| Implements exactly one consumer port | `implements PendingRequestReadPort` | Multiple interfaces or fat helper |
| Constructor deps are application contracts / internal query ports | `AllocationReadContract` | Repository, Model, HTTP client |
| No domain rules | Delegation + mapping only | Eligibility/scoring/approval logic |
| File namespace matches consumer context | `Integrations\Request\` for Employee-facing Request bridge | Random namespace |
| Registered in `IntegrationServiceProvider` | Binding present | Orphan bridge class |

**Read-only port check (OA-05-09):** If port is query-only, confirm bridge does not expose extra public methods — mirror `RequestConsumerBoundaryTest.php` pattern.

---

## 6. Infrastructure layer check

**Scope:** `app/Modules/*/Infrastructure/**/*.php`

| Check | Pass | Fail |
|-------|------|------|
| Repositories implement Application contracts | `implements AllocationRepositoryContract` | Standalone repository without interface |
| Eloquent models in `Infrastructure/Persistence/Models/` only | `AllocationModel.php` | Model in Domain |
| No foreign module imports | Own module + Laravel | `use App\Modules\Audit\...` in Request Infrastructure |
| Null/stub adapters for undeferred suppliers | `NullDormitoryReadAdapter` | Live cross-module repo in Infrastructure |

**Reporting exception:** `AuditHistorySourceReadAdapter` may reference `AuditHistoryReadContract` — must remain the **only** Reporting file referencing that contract (`ReportingBoundaryTest.php`).

---

## 7. Automated verification (reviewer)

Request author confirmation or re-run locally:

```bash
php artisan test tests/Architecture/
composer run phpstan
composer run pint
```

For PRs touching a single module's behavior:

```bash
php artisan test tests/Architecture/LayerDependencyTest.php
php artisan test tests/Architecture/ModuleBoundaryTest.php
php artisan test tests/Feature/Modules/{Module}/
```

---

## 8. Documentation & governance (when applicable)

- [ ] New bounded-context relationship reflected in `.specify/docs/context-map.md` (if new module edge)
- [ ] No unauthorized cross-spec implementation beyond active governance authorization records
- [ ] Migrations use module path `database/migrations/modules/{module}/` with rollback

---

## Review outcome template

```markdown
### Architecture review

- [ ] Domain purity — pass / fail: ___
- [ ] Application contracts — pass / fail: ___
- [ ] Cross-module deps — pass / fail: ___
- [ ] Provider bindings — pass / fail: ___
- [ ] Integration layer — pass / fail: ___
- [ ] CI architecture tests — pass / fail

**Notes:**
```

---

## Related documents

- [boundary-rules.md](./boundary-rules.md)
- [integration-layer-policy.md](./integration-layer-policy.md)
- [decision-record.md](./decision-record.md)
