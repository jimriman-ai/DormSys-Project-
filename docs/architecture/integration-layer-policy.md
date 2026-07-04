# Integration Layer Policy

**Status:** Active  
**Location:** `app/Integrations/`  
**Composition root:** `app/Providers/IntegrationServiceProvider.php`  
**Registration order:** `IntegrationServiceProvider` must be **last** in `bootstrap/providers.php`

---

## Purpose

`app/Integrations/` is the **only approved place** for wiring one bounded context to another when:

- The **consumer module** defines a port (application contract), and
- The **supplier module** exposes a public application contract or internal query port, and
- The two modules must not reference each other's Infrastructure or Domain.

Integrations contain **no business rules**. They translate between contracts at the application boundary.

```
┌─────────────────────┐     port      ┌──────────────────────┐     contract    ┌─────────────────────┐
│ Consumer module     │ ────────────► │ app/Integrations/    │ ──────────────► │ Supplier module     │
│ (e.g. Employee)     │               │ *Bridge.php          │                 │ (e.g. Request)      │
└─────────────────────┘               └──────────────────────┘                 └─────────────────────┘
         ▲                                        │
         │         IntegrationServiceProvider      │
         └──────────────── binds port → bridge ────┘
```

---

## What is allowed inside integrations

| Allowed | Example in this repo |
|---------|---------------------|
| Implement a **consumer-owned port** | `PendingRequestReadBridge implements PendingRequestReadPort` |
| Inject **supplier application contracts** only | `RequestReadContract`, `AllocationReadContract`, `EmployeeEligibilityContract` |
| Inject **consumer internal query ports** (same bounded context as bridge namespace) | `PendingRequestQueryPort` in `Integrations\Request\` |
| Use supplier **application DTOs** | `RequestSummaryDTO`, `EligibilityResultDTO` |
| Simple delegation, filtering, mapping | `ApprovedRequestReadBridge` filters `status === 'approved'` |
| `final` classes, constructor injection, no static state | All current bridges |

### Correct bridge — CheckIn ↔ Allocation

```php
// app/Integrations/CheckIn/AllocationAssignmentReadBridge.php
final class AllocationAssignmentReadBridge implements AllocationAssignmentReadPort
{
    public function __construct(
        private readonly AllocationReadContract $allocations, // supplier Application contract
    ) {}

    public function hasActiveAllocation(string $allocationId): bool
    {
        $summary = $this->allocations->getAllocationSummary($allocationId);
        return $summary !== null && $summary['status'] === 'active';
    }
}
```

Uses `AllocationReadContract` (string IDs) — **not** `AllocationRepositoryContract` or `AllocationId` domain type.

### Correct bridge — Employee ↔ Request (read-only)

```php
// app/Integrations/Request/PendingRequestReadBridge.php
final class PendingRequestReadBridge implements PendingRequestReadPort
{
    public function __construct(
        private readonly PendingRequestQueryPort $queries, // Request-internal query port
    ) {}

    public function hasPendingRequest(string $employeeId, ?string $excludingRequestId = null): bool
    {
        return $this->queries->hasNonTerminalRequest($employeeId, $excludingRequestId);
    }
}
```

Enforced: `tests/Architecture/RequestConsumerBoundaryTest.php` — bridge public methods must match port exactly (OA-05-09 read-only boundary).

---

## What is forbidden inside integrations

| Forbidden | Why |
|-----------|-----|
| Domain logic (eligibility rules, state transitions, validation policies) | Belongs in consumer or supplier **Domain/Application** |
| Direct use of Eloquent models / repositories | Bypasses application contracts |
| `use App\Modules\{X}\Infrastructure\*` | Infrastructure must not cross modules |
| `use App\Modules\{X}\Domain\*` | Prefer application contracts with primitive/DTO types; foreign domain VOs are a last resort |
| Database queries, HTTP calls, queue dispatch | Infrastructure concern |
| Mutating supplier state unless port is explicitly a **command** port | Read ports stay read-only |
| More than one responsibility per bridge class | Split ports, split bridges |

### Incorrect — business logic in bridge

```php
// FORBIDDEN — eligibility rules belong in EmployeeEligibilityService
final class EmployeeEligibilityBridge implements RequestEligibilityGatewayContract
{
    public function computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null): EligibilityResultDTO
    {
        if ($this->employees->isInactive($employeeId)) {
            return EligibilityResultDTO::ineligible('inactive'); // ❌ policy in bridge
        }
        // ...
    }
}
```

**Actual implementation** (correct) — pure delegation:

```php
return $this->eligibility->computeRequestEligibility($employeeId, $excludingRequestId);
```

### Incorrect — infrastructure leak

```php
// FORBIDDEN
use App\Modules\Request\Infrastructure\Repositories\RequestRepository;
use App\Modules\Allocation\Infrastructure\Persistence\Models\AllocationModel;
```

### Incorrect — duplicate bridge in module Infrastructure

```php
// REMOVED — do not recreate
// app/Modules/CheckIn/Infrastructure/Adapters/AllocationAssignmentReadAdapter.php
// app/Modules/Allocation/Infrastructure/Adapters/RequestReadAdapter.php
```

---

## When a new bridge is justified

Create a new file under `app/Integrations/{ConsumerContext}/` when **all** of the following are true:

1. **Consumer module** defines a port interface in `Application/Contracts/` (or `Application/Contracts/Ports/`).
2. **Supplier capability** already exists as an application contract, DTO, or consumer-internal query port.
3. The dependency **crosses bounded contexts** (see `.specify/docs/context-map.md`).
4. Binding the implementation inside either module's `*ServiceProvider` would create a hidden cross-module edge or duplicate binding.

**Do not create a bridge when:**

- The dependency stays inside one module (use `Infrastructure/Adapters/` within that module).
- The consumer can inject the foreign **application contract** directly without an adapter class (acceptable for simple pass-through — see Lottery below).
- No port interface exists yet — **define the port first** in the consumer module (contract design is not an Integration concern).

---

## Registration rules

All cross-module port → implementation bindings for Integrations live in **`IntegrationServiceProvider::register()`**:

```php
// app/Providers/IntegrationServiceProvider.php — current bindings
ApprovedRequestReadPort::class          → ApprovedRequestReadBridge::class
AllocationAssignmentReadPort::class     → AllocationAssignmentReadBridge::class
RequestEligibilityGatewayContract::class → EmployeeEligibilityBridge::class
PendingRequestReadPort::class           → PendingRequestReadBridge::class
ProposedAllocationPort::class           → ProposedAllocationConsumer::class  // consumer service, not a bridge file
```

**Rules:**

- One binding per port — no duplicate registration in module providers.
- `IntegrationServiceProvider` registers **after** all module providers in `bootstrap/providers.php`.
- Module providers bind **only** own-module abstractions to own-module implementations (+ null stubs for undeferred suppliers).

**Anti-pattern (removed):**

```php
// EmployeeServiceProvider — DO NOT re-add
$this->app->singleton(PendingRequestReadPort::class, NullPendingRequestReadAdapter::class);
```

Null stubs may remain as **classes** for isolated tests but must not compete with Integration bindings in production bootstrap.

---

## Legacy patterns (do not extend)

These exist in the codebase, pass CI, but **new work must not follow them**:

| Pattern | Location | Preferred approach |
|---------|----------|-------------------|
| Cross-module adapter in Application layer | `Lottery/Application/Adapters/RequestReadAdapter.php` | Move to `app/Integrations/Lottery/` when touched |
| Cross-module adapter in Infrastructure | `Reporting/Infrastructure/Adapters/AuditHistorySourceReadAdapter.php` | New Reporting↔Audit edges → Integrations; existing file guarded by `ReportingBoundaryTest` |
| Foreign domain VO in bridge | `ApprovedRequestReadBridge` uses `RequestId::fromString()` | Acceptable until `RequestReadContract` accepts string IDs |

---

## Checklist for a new bridge PR

- [ ] Consumer port interface exists in consumer `Application/Contracts/`
- [ ] Bridge class lives under `app/Integrations/{Consumer}/`
- [ ] Bridge implements **only** the consumer port methods
- [ ] Dependencies are application contracts / DTOs only (no Infrastructure, no repositories)
- [ ] No business branching beyond mapping/filtering
- [ ] Binding added to `IntegrationServiceProvider::register()` only
- [ ] No duplicate binding in module service providers
- [ ] Architecture tests pass: `php artisan test tests/Architecture/`
- [ ] If read-only port (OA-05-09 pattern), add/update reflection test like `RequestConsumerBoundaryTest.php`

---

## Related documents

- [boundary-rules.md](./boundary-rules.md)
- [pr-review-checklist.md](./pr-review-checklist.md)
- [decision-record.md](./decision-record.md)
