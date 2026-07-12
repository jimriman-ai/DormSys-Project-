# Contract: Employee Eligibility Service (CD-013)

**Version:** 1.1.0  
**Spec:** spec03 Employee Context  
**Implements:** spec.md FR-007, Constitution BR-01 (partial)  
**Consumer:** spec05 Request (enforcement at submission)

---

## Changelog

| Version | Note |
| ------- | ---- |
| **1.1.0** | **DOC-OPT (2026-07-12).** Supersedes Wave 1A `EmployeeId`-only signature. Accepted consumer truth (US4 Batch 1b / review R1): `computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null)`. Documents production bindings: Null `ActiveAllocationReadPort` vs live `PendingRequestReadPort` bridge. |
| 1.0.0 | Wave 1A draft — `EmployeeId`-only method; dual Null stub narrative (historical). |

---

## Purpose

Defines the **supplier** API for accommodation request eligibility **computation**. Request module MUST call this contract before accepting submission; Request owns **enforcement**, Employee owns **logic** (CD-013).

---

## Interface

**Namespace:** `App\Modules\Employee\Application\Contracts\EmployeeEligibilityContract`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Contracts;

use App\Modules\Employee\Application\DTOs\EligibilityResultDTO;

interface EmployeeEligibilityContract
{
    /**
     * Computes whether the employee may submit an accommodation request (BR-01 subset).
     * Does NOT validate request date range — that is spec05 enforcement.
     *
     * @param  string  $employeeId  Employee UUID string
     * @param  string|null  $excludingRequestId  Optional request UUID to exclude from pending check
     */
    public function computeRequestEligibility(
        string $employeeId,
        ?string $excludingRequestId = null,
    ): EligibilityResultDTO;
}
```

**Supersession:** The Wave 1A draft signature `computeRequestEligibility(EmployeeId $employeeId)` is **not** the runtime API. Do not rewrite Request or PHP to restore that shape.

---

## Implementation rules

| Rule | Detail |
|------|--------|
| Implementation | `EmployeeEligibilityService` in `Application/Services/` |
| Domain logic | `EligibilityCalculator` in `Domain/Services/` |
| Internal ports | `ActiveAllocationReadPort`, `PendingRequestReadPort` (see Binding below) |
| Contract registration | Singleton `EmployeeEligibilityContract` → `EmployeeEligibilityService` in `EmployeeServiceProvider` |
| Consumer dependency | Inject `EmployeeEligibilityContract` only |
| Mutations | **None** — read/compute only |

---

## Binding (current production)

| Port | Production binding | Location |
| ---- | ------------------ | -------- |
| `ActiveAllocationReadPort` | `NullActiveAllocationReadAdapter` (always `false`) | `EmployeeServiceProvider` |
| `PendingRequestReadPort` | `PendingRequestReadBridge` (live Request read) | `IntegrationServiceProvider` |

**Not** dual Null stubs as the sole production path. Null PendingRequest adapter is **not** the bound production implementation.

Live Allocation replacement of the Null ActiveAllocation adapter remains out of Spec03 / deferred until authorized Spec07 / IRG work.

---

## `EligibilityResultDTO`

| Field | Type | Notes |
|-------|------|-------|
| `eligible` | `bool` | `true` only when all evaluated rules pass |
| `reasonCodes` | `list<string>` | Stable reason-code string values (from `EligibilityReasonCode`) |
| `evaluatedAt` | `DateTimeImmutable` | UTC |

When `eligible === true`, `reasonCodes` MUST be empty.

---

## `EligibilityReasonCode`

| Code | Condition |
|------|-----------|
| `employee_inactive` | `Employee.status !== Active` |
| `active_allocation_exists` | `ActiveAllocationReadPort::hasActiveAllocation` → true |
| `pending_request_exists` | `PendingRequestReadPort::hasPendingRequest` → true |

With current bindings: ActiveAllocation Null means allocation blocking is inactive in production until a live adapter is authorized; PendingRequest live bridge can return true and yield `pending_request_exists`.

**Not evaluated in spec03:** check-in/check-out date rules (spec05 submission validator).

---

## Error behavior

| Input | Behavior |
|-------|----------|
| Unknown employee UUID | Service resolves via repository; throws `EmployeeNotFoundException` when missing |
| Malformed UUID | `EmployeeId::fromString` inside the service throws before eligibility evaluation |

---

## Testing requirements

| Test | Layer |
|------|-------|
| Active employee → eligible (Null allocation; no pending) | Feature (`EmployeeEligibilityContractTest`) |
| Inactive employee → `employee_inactive` | Feature |
| Mock ActiveAllocation → `true` → `active_allocation_exists` | Feature |
| Mock PendingRequest → `true` → `pending_request_exists` | Feature |
| Request module depends only on interface | Architecture (consumer) |

---

## Related documents

- [employee-read-service.md](./employee-read-service.md) — optional summary read API
- [internal-read-ports.md](./internal-read-ports.md) — port shapes and bindings
- [../data-model.md](../data-model.md) — DTO fields
- spec02 [identity-read-service.md](../../002-identity-access/contracts/identity-read-service.md) — upstream Identity reads at Employee **create** only
