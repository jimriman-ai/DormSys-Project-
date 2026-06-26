# Contract: Dormitory Read Service (supplier)

**Version:** 1.0.0  
**Spec:** spec04 Accommodation Resource  
**Implements:** Downstream read needs for spec07 Allocation, spec06 Lottery (capacity), spec11 Reporting  
**Status:** Planning — contract defined; implementation not authorized

---

## Purpose

Defines a **read-only** cross-module API for downstream contexts to query physical accommodation structure and bed physical state without cross-module Eloquent queries (AP-04, CD-014).

Dormitory is the **supplier**; Allocation is **not** authoritative for physical operability or marker storage.

---

## AssignableBed predicate

Assignable capacity uses the **derived** definition from [data-model.md](../data-model.md) — not a stored `available` flag:

```text
operability_status == InService
AND occupancy_marker == Vacant
AND site Active AND site Internal
```

---

## Interface

**Namespace (planned):** `App\Modules\Dormitory\Application\Contracts\DormitoryReadContract`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Contracts;

use App\Modules\Dormitory\Application\DTOs\AssignableCapacityDTO;
use App\Modules\Dormitory\Application\DTOs\BedPhysicalStatusDTO;
use App\Modules\Dormitory\Application\DTOs\DormitorySiteSummaryDTO;
use App\Modules\Dormitory\Domain\ValueObjects\BedId;
use App\Modules\Dormitory\Domain\ValueObjects\BuildingId;
use App\Modules\Dormitory\Domain\ValueObjects\DormitorySiteId;

interface DormitoryReadContract
{
    /**
     * @return list<DormitorySiteSummaryDTO>
     */
    public function listInternalSites(): array;

    public function siteExists(DormitorySiteId $id): bool;

    public function bedExists(BedId $id): bool;

    public function getBedPhysicalStatus(BedId $id): ?BedPhysicalStatusDTO;

    public function isBedAssignable(BedId $id): bool;

    public function getAssignableCapacity(DormitorySiteId $siteId): AssignableCapacityDTO;

    public function getAssignableCapacityByBuilding(BuildingId $buildingId): AssignableCapacityDTO;
}
```

---

## DTOs

### `DormitorySiteSummaryDTO`

| Field | Type | Notes |
| ----- | ---- | ----- |
| `id` | `string` | UUID |
| `code` | `string` | |
| `name` | `string` | |
| `type` | `string` | `internal` only in `listInternalSites` |
| `status` | `string` | `active` \| `inactive` |

### `BedPhysicalStatusDTO`

| Field | Type | Notes |
| ----- | ---- | ----- |
| `bedId` | `string` | UUID |
| `dormitorySiteId` | `string` | |
| `roomId` | `string` | |
| `bedCode` | `string` | |
| `operabilityStatus` | `string` | `in_service` \| `out_of_service` \| `maintenance` |
| `occupancyMarker` | `string` | `vacant` \| `reserved` \| `occupied` |
| `roomKind` | `string` | `private` \| `shared` |
| `isAssignable` | `bool` | Computed per AssignableBed predicate |

**Excluded:** `employee_id`, `person_id`, allocation assignee — Allocation owns assignment (CD-014).

### `AssignableCapacityDTO`

| Field | Type | Notes |
| ----- | ---- | ----- |
| `scopeId` | `string` | Site or building UUID |
| `scopeType` | `string` | `site` \| `building` |
| `totalBeds` | `int` | All beds in scope |
| `assignableBeds` | `int` | Matching AssignableBed predicate |
| `inServiceBeds` | `int` | Operability = in_service |
| `occupiedBeds` | `int` | Marker = occupied |
| `reservedBeds` | `int` | Marker = reserved |

---

## Implementation rules

| Rule | Detail |
| ---- | ------ |
| Implementation | `DormitoryReadService` |
| Internal dependency | Repositories inside Dormitory module only |
| Forbidden | Consumers importing `BedModel` or querying `dormitory_*` tables |
| External sites | `listInternalSites` excludes `external`; capacity APIs return zero beds for external |

---

## Testing

- Feature tests: `DormitoryReadContractTest` with fixture hierarchy
- Architecture test: Allocation module (when added) must not import Dormitory Infrastructure

---

## Related

- [allocation-physical-state-port.md](./allocation-physical-state-port.md) — inbound marker updates
- [data-model.md](../data-model.md) — AssignableBed definition
- CD-014, context-map R7
