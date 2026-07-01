# Port: Allocation Assignment Read (CheckIn internal)

**Version:** 1.0.0  
**Spec:** spec07 Allocation & Occupancy  
**Direction:** CheckIn consumes Allocation facts (CD-015)  
**Status:** Design — implementation not authorized

---

## Purpose

Narrow read port for CheckIn/CheckOut to verify active assignment facts without importing Allocation Infrastructure Eloquent.

May delegate to `AllocationReadContract` or a dedicated internal adapter — relationship documented at implementation (N-08).

---

## Interface (CheckIn consumer)

**Namespace:** `App\Modules\CheckIn\Application\Contracts\AllocationAssignmentReadPort`

```php
<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Contracts;

interface AllocationAssignmentReadPort
{
    public function hasActiveAllocation(string $allocationId): bool;

    public function isAllocationActiveForBed(string $allocationId, string $bedId): bool;
}
```

---

## Adapter

**Implementation:** `AllocationAssignmentReadAdapter` in `app/Modules/CheckIn/Infrastructure/Adapters/`

**Upstream:** Allocation Application layer read surface — not `AllocationModel` direct access.

---

## Rules

| Rule | Detail |
| ---- | ------ |
| CD-015 | CheckIn reads assignment; does not create or release |
| No Eloquent | CheckIn must not import Allocation Infrastructure persistence |
