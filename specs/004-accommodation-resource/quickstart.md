# Quickstart: Accommodation Resource (spec04)

**Date**: 2026-06-23 | **Plan**: [plan.md](./plan.md)

Validation scenarios for Dormitory module **after implementation is authorized**. Prerequisites assume spec01 Foundation running. No Identity or Employee setup required.

---

## Prerequisites

```powershell
docker compose up -d
docker compose exec laravel.test php artisan migrate
```

---

## Scenario 1 — Internal dormitory hierarchy (US1)

**Proves:** FR-001, FR-003, FR-005, SC-001

1. `php artisan dormitory:create-site INTERNAL-01 "Campus North" --type=internal`
2. Add building, room (with `--floor-label=2`), and bed via commands or actions
3. Query capacity — total beds = 1

**Expected:** Hierarchy persisted under `dormitory_*` tables; floor stored on room, not separate Floor table.

---

## Scenario 2 — External dormitory catalog (US2)

**Proves:** FR-002, FR-009, SC-002

1. `php artisan dormitory:create-site EXT-01 "Partner Hotel" --type=external`
2. Attempt to add building or bed to external site
3. Assert rejection (`ExternalDormitoryStructureException` or equivalent)

**Expected:** External site exists; zero physical inventory; structural children rejected.

---

## Scenario 3 — AssignableBed capacity (US3)

**Proves:** FR-007, R-05, SC-003, system-flow INV-2

1. Create internal site with one `InService` / `Vacant` bed
2. `DormitoryReadContract::getAssignableCapacity` → `assignableBeds = 1`
3. Mark bed `Maintenance` via operability action
4. `assignableBeds = 0`; `isBedAssignable(bedId) = false`

**Expected:** No `available` column; predicate from operability + occupancy only.

---

## Scenario 4 — Occupancy markers via port stub (US4)

**Proves:** FR-008, SC-004, CD-014

1. Bind `AllocationPhysicalStatePort` to real action (not null) in test
2. `reserveBed(bedId, signalUuid)` → marker `Reserved`; `last_signal_reference_id` set
3. `occupyBed` → `Occupied`; `releaseBed` → `Vacant`
4. Assert **no** `employee_id` / `person_id` columns on `dormitory_beds`

**Expected:** Dormitory applies markers; does not store assignment authority.

---

## Scenario 5 — Supplier read contract (US5)

**Proves:** FR-010, SC-005

1. Call `DormitoryReadContract::getBedPhysicalStatus(bedId)`
2. Assert `BedPhysicalStatusDTO` includes `isAssignable`, `roomKind`, operability, marker
3. Architecture test: no Allocation/Request/Employee Infrastructure imports in Dormitory module

**Expected:** Downstream can read without cross-module Eloquent.

---

## Scenario 6 — Duplicate bed code (edge)

**Proves:** data-model uniqueness

1. Create second bed with same `bed_code` under same site
2. Assert rejection (unique `dormitory_site_id` + `bed_code`)

---

## MVP gate commands

```powershell
docker compose exec laravel.test php artisan test tests/Feature/Modules/Dormitory tests/Unit/Modules/Dormitory tests/Architecture/DormitorySupplierBoundaryTest.php
docker compose exec laravel.test vendor/bin/phpstan analyse app/Modules/Dormitory
docker compose exec laravel.test vendor/bin/pint --test app/Modules/Dormitory
```

---

## Out of scope (documented only)

| Scenario | Deferred to |
| -------- | ----------- |
| Real Allocation adapter | spec07 |
| Check-in / check-out marker changes | OQ-06 / spec07 |
| Allocation ↔ Dormitory reconciliation | spec07 |
| Livewire admin UI | Phase H |

---

## Related

- [data-model.md](./data-model.md)
- [contracts/dormitory-read-service.md](./contracts/dormitory-read-service.md)
- [contracts/allocation-physical-state-port.md](./contracts/allocation-physical-state-port.md)
