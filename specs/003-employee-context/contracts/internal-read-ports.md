# Internal Ports: BR-01 Partial Rules (Wave 1A stubs)

**Version:** 1.0.0  
**Spec:** spec03 Employee Context  
**Scope:** **Internal** to Employee module — not public cross-module API

---

## Purpose

BR-01 requires checks against Allocation and Request state. Those modules are not implemented in Wave 1A. Employee eligibility calculator depends on **ports** with **stub adapters** until spec05/spec07 supply real implementations.

---

## `ActiveAllocationReadPort`

```php
namespace App\Modules\Employee\Application\Contracts\Ports;

use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

interface ActiveAllocationReadPort
{
    public function hasActiveAllocation(EmployeeId $employeeId): bool;
}
```

**Wave 1A adapter:** `Infrastructure\Adapters\NullActiveAllocationReadAdapter` — always returns `false`.

**Future:** spec07 Allocation module provides `AllocationReadContract` adapter implementing this port (or replaces binding).

---

## `PendingRequestReadPort`

```php
namespace App\Modules\Employee\Application\Contracts\Ports;

use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

interface PendingRequestReadPort
{
    public function hasPendingRequest(EmployeeId $employeeId): bool;
}
```

**Wave 1A adapter:** `Infrastructure\Adapters\NullPendingRequestReadAdapter` — always returns `false`.

**Future:** spec05 Request module provides real adapter.

---

## Binding (Wave 1A)

```php
// EmployeeServiceProvider
$this->app->bind(ActiveAllocationReadPort::class, NullActiveAllocationReadAdapter::class);
$this->app->bind(PendingRequestReadPort::class, NullPendingRequestReadAdapter::class);
```

---

## Test strategy

| Test | Approach |
|------|----------|
| Default stubs | Active employee → eligible |
| Allocation blocking | Bind mock port returning `true` → `active_allocation_exists` |
| Request blocking | Bind mock port returning `true` → `pending_request_exists` |

---

## Related

- [employee-eligibility-service.md](./employee-eligibility-service.md)
- Constitution BR-01
- CD-013
