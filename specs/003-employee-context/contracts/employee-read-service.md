# Contract: Employee Read Service (supplier)

**Version:** 1.0.0  
**Spec:** spec03 Employee Context  
**Implements:** Downstream read needs for spec05 Request (display, validation context)  
**Status:** Wave 1A — minimal surface

---

## Purpose

Defines a **read-only** cross-module API for downstream contexts to load minimal Employee data without cross-module Eloquent queries (AP-04).

This is the Employee analogue of spec02 `IdentityUserReadContract`.

---

## Interface

**Namespace (planned):** `App\Modules\Employee\Application\Contracts\EmployeeReadContract`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Contracts;

use App\Modules\Employee\Application\DTOs\EmployeeSummaryDTO;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

interface EmployeeReadContract
{
    public function employeeExists(EmployeeId $id): bool;

    public function isEmployeeActive(EmployeeId $id): bool;

    public function findEmployeeSummary(EmployeeId $id): ?EmployeeSummaryDTO;
}
```

---

## `EmployeeSummaryDTO`

| Field | Type | Notes |
|-------|------|-------|
| `id` | `string` | UUID |
| `identityId` | `string` | Immutable Identity reference |
| `employeeCode` | `string` | |
| `fullName` | `string` | `firstName + lastName` |
| `departmentId` | `string?` | |
| `status` | `string` | `active` \| `inactive` |

No `nationalCode` in cross-context summary (PII minimization).

---

## Implementation rules

| Rule | Detail |
|------|--------|
| Implementation | `EmployeeReadService` |
| Internal dependency | `EmployeeRepository` inside module only |
| Forbidden | Consumers importing `EmployeeModel` or querying `employee_*` tables |

---

## Testing

- Feature tests in Employee module
- Architecture test: Request module (when added) must not import Employee Infrastructure

---

## Related

- [employee-eligibility-service.md](./employee-eligibility-service.md) — eligibility computation (separate concern)
