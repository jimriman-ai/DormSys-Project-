# Contract: Employee Eligibility Service (CD-013)

**Version:** 1.0.0  
**Spec:** spec03 Employee Context  
**Implements:** spec.md FR-007, Constitution BR-01 (partial Wave 1A)  
**Consumer:** spec05 Request (enforcement at submission)

---

## Purpose

Defines the **supplier** API for accommodation request eligibility **computation**. Request module MUST call this contract before accepting submission; Request owns **enforcement**, Employee owns **logic** (CD-013).

---

## Interface

**Namespace (planned):** `App\Modules\Employee\Application\Contracts\EmployeeEligibilityContract`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Contracts;

use App\Modules\Employee\Application\DTOs\EligibilityResultDTO;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

interface EmployeeEligibilityContract
{
    /**
     * Computes whether the employee may submit an accommodation request (BR-01 subset).
     * Does NOT validate request date range — that is spec05 enforcement.
     */
    public function computeRequestEligibility(EmployeeId $employeeId): EligibilityResultDTO;
}
```

---

## Implementation rules

| Rule | Detail |
|------|--------|
| Implementation | `EmployeeEligibilityService` in `Application/Services/` |
| Domain logic | `EligibilityCalculator` in `Domain/Services/` |
| Internal ports | `ActiveAllocationReadPort`, `PendingRequestReadPort` (stub adapters Wave 1A) |
| Registration | Singleton in `EmployeeServiceProvider` |
| Consumer dependency | Inject `EmployeeEligibilityContract` only |
| Mutations | **None** — read/compute only |

---

## `EligibilityResultDTO`

| Field | Type | Notes |
|-------|------|-------|
| `eligible` | `bool` | `true` only when all evaluated rules pass |
| `reasonCodes` | `list<EligibilityReasonCode>` | Stable string-backed enum values |
| `evaluatedAt` | `DateTimeImmutable` | UTC |

When `eligible === true`, `reasonCodes` MUST be empty.

---

## `EligibilityReasonCode` (Wave 1A)

| Code | Condition |
|------|-----------|
| `employee_inactive` | `Employee.status !== Active` |
| `active_allocation_exists` | `ActiveAllocationReadPort::hasActiveAllocation` → true |
| `pending_request_exists` | `PendingRequestReadPort::hasPendingRequest` → true |

**Wave 1A stubs:** allocation and pending-request ports return `false` — only `employee_inactive` is live in production paths until spec05/spec07.

**Not evaluated in spec03:** check-in/check-out date rules (spec05 submission validator).

---

## Error behavior

| Input | Behavior |
|-------|----------|
| Unknown `EmployeeId` | Throw `EmployeeNotFoundException` (domain) |
| Malformed UUID | `EmployeeId::fromString` throws before service call |

---

## Testing requirements

| Test | Layer |
|------|-------|
| Active employee → eligible (stubs false) | Unit / Feature |
| Inactive employee → `employee_inactive` | Unit |
| Stub port returns true → ineligible with correct code | Unit with mock port |
| Request module depends only on interface | Architecture (spec05 later) |

---

## Related documents

- [employee-read-service.md](./employee-read-service.md) — optional summary read API
- [internal-read-ports.md](./internal-read-ports.md) — stub ports for BR-01 partial rules
- [../data-model.md](../data-model.md) — DTO fields
- spec02 [identity-read-service.md](../../002-identity-access/contracts/identity-read-service.md) — upstream Identity reads at Employee **create** only
