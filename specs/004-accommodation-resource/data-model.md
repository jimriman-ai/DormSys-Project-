# Data Model: Accommodation Resource (spec04)

**Date**: 2026-06-23 | **Plan**: [plan.md](./plan.md) | **Research**: [research.md](./research.md)

---

## Bounded context

**Dormitory** — aggregate roots: **DormitorySite** (catalog). **Building**, **Room**, **Bed** are entities within internal site scope (OA-04-02).

Dormitory does **not** import or FK to Allocation, Request, Employee, or Identity tables.

---

## Derived concept: AssignableBed (not persisted)

**Definition (R-05):**

```text
AssignableBed :=
    operability_status == InService
    AND
    occupancy_marker == Vacant
    AND
    parent site.status == Active
    AND
    site.type == internal
```

**No** `available` / `is_assignable` column. Capacity queries compute from enums.

---

## 1. DormitorySite (aggregate root)

### Domain entity: `DormitorySite`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `DormitorySiteId` | UUID v7 |
| `code` | string | Unique platform-wide |
| `name` | string | Required |
| `type` | `DormitoryType` | `Internal` \| `External` |
| `status` | `DormitorySiteStatus` | `Active` \| `Inactive` |
| `city` | string? | Optional metadata |
| `address` | string? | Optional metadata |

### Invariants

1. `code` unique
2. External sites **must not** have Building/Room/Bed children (OA-04-03)
3. Inactive sites excluded from assignable capacity queries

---

## 2. Persistence: `dormitory_sites`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | UUID v7 via `HasUuid` |
| `code` | `string` | unique |
| `name` | `string` | |
| `type` | `string` | `internal`, `external` |
| `status` | `string` | `active`, `inactive` |
| `city` | `string` nullable | |
| `address` | `text` nullable | |
| audit + soft delete | | BaseModel |

**Module path:** `database/migrations/modules/dormitory/`

**Cross-module FK:** none

---

## 3. Building (entity — internal only)

### Domain entity: `Building`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `BuildingId` | UUID v7 |
| `dormitorySiteId` | `DormitorySiteId` | Parent must be `Internal` |
| `code` | string | Unique within site |
| `name` | string | Required |

### Persistence: `dormitory_buildings`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | |
| `dormitory_site_id` | `uuid` | FK → `dormitory_sites.id` (intra-module) |
| `code` | `string` | unique (`dormitory_site_id`, `code`) |
| `name` | `string` | |
| audit + soft delete | | BaseModel |

---

## 4. Room (entity — internal only)

### Domain entity: `Room`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `RoomId` | UUID v7 |
| `buildingId` | `BuildingId` | |
| `code` | string | Unique within building |
| `name` | string? | Display label |
| `floorLabel` | string? | OA-04-01 — not a Floor aggregate |
| `kind` | `RoomKind` | `Private` \| `Shared` (OA-04-05) |

### Persistence: `dormitory_rooms`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | |
| `building_id` | `uuid` | FK → `dormitory_buildings.id` |
| `code` | `string` | unique (`building_id`, `code`) |
| `name` | `string` nullable | |
| `floor_label` | `string` nullable | |
| `kind` | `string` | `private`, `shared` |
| audit + soft delete | | BaseModel |

---

## 5. Bed (entity — internal only)

### Domain entity: `Bed`

| Attribute | Type | Rules |
| --------- | ---- | ----- |
| `id` | `BedId` | UUID v7 |
| `roomId` | `RoomId` | |
| `dormitorySiteId` | `DormitorySiteId` | Denormalized for uniqueness/queries |
| `bedCode` | string | Unique within site (R-10) |
| `label` | string? | Optional display |
| `operabilityStatus` | `BedOperabilityStatus` | `InService`, `OutOfService`, `Maintenance` |
| `occupancyMarker` | `BedOccupancyMarker` | `Vacant`, `Reserved`, `Occupied` |
| `lastSignalReferenceId` | string? | UUID trace only; **no FK** (R-06) |

### Invariants

1. `bedCode` unique per `dormitory_site_id`
2. New beds default: `operabilityStatus = InService`, `occupancyMarker = Vacant`
3. **No** `employee_id`, `person_id`, or authoritative assignment FK
4. AssignableBed predicate (R-05) — computed, not stored
5. Operability downgrade blocked when `occupancyMarker` ∈ {`Reserved`, `Occupied`} (R-07 default)

### State transitions

**Operability (R-07):** see [research.md](./research.md#r-07--bed-operability-transitions)

**Occupancy marker (R-08):** applied via `AllocationPhysicalStatePort` only

```text
Vacant ──reserve──► Reserved ──occupy──► Occupied ──release──► Vacant
```

### Persistence: `dormitory_beds`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | `uuid` PK | |
| `room_id` | `uuid` | FK → `dormitory_rooms.id` |
| `dormitory_site_id` | `uuid` | FK → `dormitory_sites.id` (denormalized) |
| `bed_code` | `string` | unique (`dormitory_site_id`, `bed_code`) |
| `label` | `string` nullable | |
| `operability_status` | `string` | `in_service`, `out_of_service`, `maintenance` |
| `occupancy_marker` | `string` | `vacant`, `reserved`, `occupied` |
| `last_signal_reference_id` | `uuid` nullable | no FK; any signal source (R-06) |
| audit + soft delete | | BaseModel |

---

## 6. Enums (persistence values)

| Enum | Values |
| ---- | ------ |
| `DormitoryType` | `internal`, `external` |
| `DormitorySiteStatus` | `active`, `inactive` |
| `RoomKind` | `private`, `shared` |
| `BedOperabilityStatus` | `in_service`, `out_of_service`, `maintenance` |
| `BedOccupancyMarker` | `vacant`, `reserved`, `occupied` |

---

## 7. Migration order

```text
dormitory_sites
  → dormitory_buildings
  → dormitory_rooms
  → dormitory_beds
```

---

## 8. Cross-context references

| Field | Rule |
| ----- | ---- |
| `last_signal_reference_id` | Optional UUID; no FK; not assignment authority |
| Allocation / Employee / Identity | **Prohibited** as FK columns |

---

## 9. Capacity query semantics

| Query | Definition |
| ----- | ---------- |
| Total beds (site) | Count `dormitory_beds` where site internal + not soft-deleted |
| Assignable beds | Count beds matching **AssignableBed** predicate (§ Derived concept) |
| By building / room | Same predicate scoped to hierarchy |

---

## Related

- [research.md](./research.md) — R-05 AssignableBed, R-06 signal reference, transitions
- [contracts/dormitory-read-service.md](./contracts/dormitory-read-service.md)
- [contracts/allocation-physical-state-port.md](./contracts/allocation-physical-state-port.md)
