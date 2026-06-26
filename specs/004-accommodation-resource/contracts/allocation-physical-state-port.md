# Port: Allocation Physical State (inbound)

**Version:** 1.0.0  
**Spec:** spec04 Accommodation Resource  
**Scope:** **Inbound** to Dormitory — Allocation (spec07) is primary producer; Wave 1 uses stub adapter

---

## Purpose

CD-014 / R7: Allocation **decides** assignment; Dormitory **applies** physical occupancy marker changes on beds. This port is the application boundary for inbound signals — no cross-module Eloquent.

**Not an assignment contract:** Port methods reference `bedId` + `signalReferenceId` only. No person/employee parameters.

---

## Interface

**Namespace (planned):** `App\Modules\Dormitory\Application\Contracts\Ports\AllocationPhysicalStatePort`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Contracts\Ports;

use App\Modules\Dormitory\Domain\ValueObjects\BedId;

interface AllocationPhysicalStatePort
{
    /**
     * Vacant → Reserved. Idempotent if already Reserved for same signalReferenceId.
     *
     * @throws BedNotOperableException if operability != InService
     * @throws InvalidOccupancyTransitionException if marker not Vacant
     */
    public function reserveBed(BedId $bedId, string $signalReferenceId): void;

    /**
     * Reserved → Occupied.
     *
     * @throws InvalidOccupancyTransitionException if marker not Reserved
     */
    public function occupyBed(BedId $bedId, string $signalReferenceId): void;

    /**
     * Reserved or Occupied → Vacant.
     *
     * @throws InvalidOccupancyTransitionException if marker already Vacant
     */
    public function releaseBed(BedId $bedId, string $signalReferenceId): void;
}
```

---

## Signal reference

| Field | Rule |
| ----- | ---- |
| `signalReferenceId` | UUID string; stored in `dormitory_beds.last_signal_reference_id` (no FK) |
| Semantics | Traceability only — typically Allocation aggregate/event id in spec07 |
| Sources (future) | Allocation (primary); reconciliation job; maintenance override (spec07 policy) |

---

## Implementation (Wave 1)

| Component | Role |
| --------- | ---- |
| `ApplyAllocationPhysicalStateAction` | Domain rules + repository persist |
| `NullAllocationPhysicalStateAdapter` | Test stub — optional no-op or in-memory |
| Feature tests | Bind port explicitly; drive marker transitions |

**Real adapter:** spec07 Allocation module implements producer; binds or calls `ApplyAllocationPhysicalStateAction` via application service — design in spec07 plan.

---

## Transition table

| Method | From marker | To marker | Operability guard |
| ------ | ----------- | --------- | ----------------- |
| `reserveBed` | `Vacant` | `Reserved` | `InService` |
| `occupyBed` | `Reserved` | `Occupied` | — |
| `releaseBed` | `Reserved` \| `Occupied` | `Vacant` | — |

### Full occupancy transition matrix

| Current | `reserveBed` | `occupyBed` | `releaseBed` |
| ------- | ------------ | ----------- | ------------ |
| `Vacant` | → `Reserved` (requires `InService`) | **reject** | **reject** |
| `Reserved` | idempotent (same signal) / else reject | → `Occupied` | → `Vacant` |
| `Occupied` | **reject** | **reject** | → `Vacant` |

See [research.md](../research.md#occupancy-transition-matrix-normative) R-08.

---

## Forbidden

| Anti-pattern | Reason |
| ------------ | ------ |
| `assignEmployeeToBed(employeeId, bedId)` on this port | Assignment ∈ Allocation |
| FK from `dormitory_beds` to `allocation_*` | CD-014 / AP-04 |
| Dormitory calling Allocation repositories | Boundary violation |

---

## Testing

| Test | Approach |
| ---- | -------- |
| Happy path | reserve → occupy → release → Vacant |
| INV-2 guard | reserve on `OutOfService` bed → rejected |
| Idempotency | Same `signalReferenceId` on repeat reserve — policy in action (document in tasks) |
| Stub Wave 1 | No Allocation module required |

---

## Related

- [dormitory-read-service.md](./dormitory-read-service.md)
- [data-model.md](../data-model.md) — `last_signal_reference_id`
- CD-014
