# Internal Ports: BR-01 Partial Rules

**Version:** 1.1.0  
**Spec:** spec03 Employee Context  
**Scope:** **Internal** to Employee module application ports — not a public cross-module API surface beyond the port interfaces themselves

---

## Changelog

| Version | Note |
| ------- | ---- |
| **1.1.0** | **DOC-OPT (2026-07-12).** Syncs PendingRequest signature and production bindings to runtime: Null ActiveAllocation in Employee provider; live PendingRequest bridge in Integration composition root. Dual Null Wave 1A binding is historical only. |
| 1.0.0 | Wave 1A — dual Null stub narrative (historical). |

---

## Purpose

BR-01 requires checks against Allocation and Request state. Employee eligibility depends on **ports**. Production bindings are **not** both Null: ActiveAllocation remains Null until an authorized live Allocation adapter; PendingRequest is bound to a live Request bridge.

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

**Current adapter:** `Infrastructure\Adapters\NullActiveAllocationReadAdapter` — always returns `false`.

**Bound in:** `EmployeeServiceProvider`.

**Future:** authorized Spec07 / IRG path may replace the Null adapter with a live Allocation read adapter.

---

## `PendingRequestReadPort`

```php
namespace App\Modules\Employee\Application\Contracts\Ports;

interface PendingRequestReadPort
{
    public function hasPendingRequest(
        string $employeeId,
        ?string $excludingRequestId = null,
    ): bool;
}
```

**Current adapter:** `App\Integrations\Request\PendingRequestReadBridge` — live Request read (not Null).

**Bound in:** `IntegrationServiceProvider` (composition root).

**Historical:** Wave 1A draft described `NullPendingRequestReadAdapter` and `hasPendingRequest(EmployeeId)` — **not** the current production binding or signature. Do not reintroduce Null PendingRequest as the production bind under Spec03 DOC-OPT.

---

## Binding (current production)

```php
// EmployeeServiceProvider
$this->app->bind(ActiveAllocationReadPort::class, NullActiveAllocationReadAdapter::class);

// IntegrationServiceProvider (composition root)
$this->app->singleton(PendingRequestReadPort::class, PendingRequestReadBridge::class);
```

**Wave 1A dual-Null binding in EmployeeServiceProvider alone is obsolete** and must not be treated as the only production path.

---

## Test strategy

| Test | Approach |
|------|----------|
| Default production-like | Active employee + Null allocation + no pending → eligible |
| Allocation blocking | Bind mock `ActiveAllocationReadPort` returning `true` → `active_allocation_exists` |
| Request blocking | Bind mock `PendingRequestReadPort` returning `true` → `pending_request_exists` |

---

## Related

- [employee-eligibility-service.md](./employee-eligibility-service.md)
- Constitution BR-01
- CD-013
