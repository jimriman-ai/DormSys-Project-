# Contract: Allocation Read Service (supplier)

**Version:** 1.0.0  
**Spec:** spec07 Allocation & Occupancy  
**Direction:** Outbound supplier (R6 downstream, R11 Reporting)  
**Status:** Design — implementation not authorized

---

## Purpose

Read-only cross-module API for active-allocation and assignment queries. Replaces spec03 `ActiveAllocationReadPort` stub when live (UD-11).

Allocation is the **supplier**. Consumers must not import Allocation Infrastructure or query `allocation_*` tables directly.

---

## Interface

**Namespace:** `App\Modules\Allocation\Application\Contracts\AllocationReadContract`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts;

interface AllocationReadContract
{
    public function hasActiveAllocation(string $personId): bool;

    /**
     * @return list<array{
     *     allocationId: string,
     *     personId: string,
     *     bedId: string,
     *     status: string,
     *     dateRangeStart: string,
     *     dateRangeEnd: string
     * }>|null
     */
    public function getActiveAllocationsForPerson(string $personId): array;

    public function getAllocationSummary(string $allocationId): ?array;
}
```

---

## Rules

| Rule | Detail |
| ---- | ------ |
| Read-only | No lifecycle mutation via this contract |
| CD-014 | Returns assignment authority data only — not physical markers |
| CD-017 | Reporting may consume; no write authority |
| Implementation | `AllocationReadService` in Allocation module |

---

## Consumers

| Consumer | Spec | Use |
| -------- | ---- | --- |
| Employee eligibility | spec03 | `hasActiveAllocation` |
| Reporting | spec11 | Assignment projections (read-only) |
| CheckIn/CheckOut | spec07 | Assignment facts via dedicated adapter (see `allocation-assignment-read-port.md`) |
