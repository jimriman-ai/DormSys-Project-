# Port: CheckIn Command (inbound to CheckIn/CheckOut)

**Version:** 1.0.0  
**Spec:** spec07 Allocation & Occupancy  
**Owner:** CheckIn/CheckOut (CD-015)  
**Status:** Design — implementation not authorized

---

## Purpose

Inbound command boundary for operational `CheckedIn` / `CheckedOut` transitions. Operator role required for internal dormitories.

CheckIn/CheckOut consumes assignment facts from Allocation; it does not own assignment authority.

---

## Interface

**Namespace:** `App\Modules\CheckIn\Application\Contracts\CheckInCommandPort`

```php
<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Contracts;

interface CheckInCommandPort
{
    public function checkIn(string $allocationId, string $operatorId): void;

    public function checkOut(string $allocationId, string $operatorId): void;
}
```

---

## Preconditions

| Command | Preconditions |
| ------- | ------------- |
| `checkIn` | Active allocation exists; no open check-in record; Operator role |
| `checkOut` | Open check-in record exists; Operator role |

---

## Rules

| Rule | Detail |
| ---- | ------ |
| CD-015 | Operational transitions only — no assignment decisions |
| Assignment read | Via `AllocationAssignmentReadPort` — not Allocation Infrastructure Eloquent |
| External dormitories | Out of scope — Voucher / external path per architecture spec |

---

## Domain events (names only — UD-01)

- `CheckedIn`
- `CheckedOut`
