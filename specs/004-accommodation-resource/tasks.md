# Tasks: Accommodation Resource (spec04)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md) (§MVP boundary), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/](./contracts/), [quickstart.md](./quickstart.md)

**Branch**: `004-accommodation-resource`

**Design checkpoint**: tag `spec04-design-approved`

**Scope guards** (from plan.md):

- **Dormitory bounded context only** — no Allocation/Request/Employee/Identity Infrastructure imports (BT-D05)
- **CD-014:** Dormitory owns physical state; Allocation owns assignment — no person FK on `Bed`
- **AssignableBed:** derived predicate only — `InService` + `Vacant` + active internal site (R-05); no `available` column
- **Occupancy markers:** via `AllocationPhysicalStatePort` only — see transition matrix in [research.md](./research.md) and [contracts/allocation-physical-state-port.md](./contracts/allocation-physical-state-port.md)
- **External sites:** no Building/Room/Bed children (OA-04-03)
- **No** Workflow, CheckIn/CheckOut implementation, Livewire admin (Phase H deferred)

**Status**: **Design approved** — **Implementation not authorized** until separate go-ahead (`handoff/spec04-planning-authorization.md`).

---

## Phase Summary

| Phase | Purpose | Task IDs | MVP? |
|-------|---------|----------|------|
| 1 — Setup | Module paths, provider wiring | T001–T004 | Yes |
| 2 — Foundational | VOs, enums, exceptions, migrations | T005–T016 | Yes |
| 3 — US1 (P1) | Internal dormitory hierarchy | T017–T028 | Yes |
| 4 — US2 (P1) | External dormitory catalog | T029–T031 | Yes |
| 5 — US3 (P2) | Physical bed operability + AssignableBed | T032–T035 | Yes |
| 6 — US4 (P2) | Allocation physical state port (stub) | T036–T041 | Yes |
| 7 — US5 (P3) | `DormitoryReadContract` supplier | T042–T046 | Yes |
| 8 — Polish | BT-D05, PHPStan, quickstart gate | T047–T051 | Yes (quality) |

**Total tasks:** 51 (MVP: T001–T051 excluding deferred Phase H)

---

## User Story Mapping

| Story | Priority | Phases | Independent Test |
|-------|----------|--------|------------------|
| US1 — Internal Dormitory Physical Catalog | P1 | 2→3 | Create site → building → room → bed; capacity count |
| US2 — External Dormitory Catalog | P1 | 4 | External site; reject physical children |
| US3 — Physical Bed & Room Status | P2 | 5 | Operability change; AssignableBed query |
| US4 — Allocation-Driven Physical State | P2 | 6 | reserve → occupy → release via port stub |
| US5 — Capacity Supplier Queries | P3 | 7 | `DormitoryReadContract` without cross-module Eloquent |

**Suggested MVP:** Phases 1–8 (T001–T051) — full spec04 MVP per plan.md §MVP boundary.

---

## Dependency Graph

```text
Phase 1 (Setup)
    └── Phase 2 (Foundational) ──blocks──► Phase 3 (US1) 🎯
                                              ├──► Phase 4 (US2)  [needs site entity]
                                              ├──► Phase 5 (US3)  [needs Bed entity]
                                              ├──► Phase 6 (US4)  [needs Bed + operability]
                                              └──► Phase 7 (US5)  [needs repositories + bed state]
                                                        └──► Phase 8 (Polish)
```

**Parallel opportunities:** Tasks marked `[P]` within a phase touch different files with no ordering dependency on other `[P]` tasks in the same phase.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Dormitory module migration path and DI scaffolding.

- [ ] T001 Verify `app/Modules/Dormitory/Infrastructure/Providers/DormitoryServiceProvider.php` loads migrations from `database/migrations/modules/dormitory/` per plan.md
- [ ] T002 [P] Ensure `database/migrations/modules/dormitory/` directory exists
- [ ] T003 [P] Add `DormitoryPresentationServiceProvider` in `app/Modules/Dormitory/Presentation/Providers/DormitoryPresentationServiceProvider.php` for Artisan commands and register in `bootstrap/providers.php`
- [ ] T004 [P] Update `app/Modules/Dormitory/README.md` with spec04 scope, CD-014 boundary, and quickstart prerequisites

**Checkpoint**: Module boots; migrations path wired.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared value objects, enums, exceptions, and **all** `dormitory_*` migrations (FK order per `data-model.md`).

**⚠️ CRITICAL**: No user story work until this phase is complete.

- [ ] T005 Create `DormitorySiteId` value object in `app/Modules/Dormitory/Domain/ValueObjects/DormitorySiteId.php`
- [ ] T006 [P] Create `BuildingId`, `RoomId`, `BedId` value objects in `app/Modules/Dormitory/Domain/ValueObjects/`
- [ ] T007 [P] Create enums in `app/Modules/Dormitory/Domain/Enums/` — `DormitoryType.php`, `DormitorySiteStatus.php`, `RoomKind.php`, `BedOperabilityStatus.php`, `BedOccupancyMarker.php` per `data-model.md`
- [ ] T008 [P] Create domain exceptions in `app/Modules/Dormitory/Domain/Exceptions/` — `DormitoryNotFoundException`, `ExternalDormitoryStructureException`, `BedNotOperableException`, `DuplicateBedCodeException`, `InvalidOccupancyTransitionException`
- [ ] T009 Create migration `database/migrations/modules/dormitory/*_create_dormitory_sites_table.php` per `data-model.md`
- [ ] T010 Create migration `database/migrations/modules/dormitory/*_create_dormitory_buildings_table.php` — FK `dormitory_site_id` → `dormitory_sites`
- [ ] T011 Create migration `database/migrations/modules/dormitory/*_create_dormitory_rooms_table.php` — FK `building_id` → `dormitory_buildings`
- [ ] T012 Create migration `database/migrations/modules/dormitory/*_create_dormitory_beds_table.php` — FK `room_id`, denormalized `dormitory_site_id`; `last_signal_reference_id` nullable; unique (`dormitory_site_id`, `bed_code`)
- [ ] T013 Create `DormitorySite` entity in `app/Modules/Dormitory/Domain/Entities/DormitorySite.php` per `data-model.md`
- [ ] T014 Create `DormitorySiteModel` in `app/Modules/Dormitory/Infrastructure/Persistence/Models/DormitorySiteModel.php` extending `BaseModel` with `HasUuid`, `RecordsActivity`
- [ ] T015 Create `DormitorySiteRepositoryContract` in `app/Modules/Dormitory/Application/Contracts/DormitorySiteRepositoryContract.php` and `DormitorySiteRepository` in `app/Modules/Dormitory/Infrastructure/Repositories/DormitorySiteRepository.php`
- [ ] T016 Create `CreateDormitorySiteAction` in `app/Modules/Dormitory/Application/Services/CreateDormitorySiteAction.php` — internal and external types

**Checkpoint**: All `dormitory_*` tables migrate; site aggregate persistable.

---

## Phase 3: User Story 1 — Internal Dormitory Physical Catalog (Priority: P1) 🎯

**Goal**: Dormitory → Building → Room → Bed hierarchy for internal sites.

**Independent Test**: Create internal site → building → room (with `floor_label`) → bed → query capacity — per quickstart Scenario 1.

- [ ] T017 [US1] Create `Building` entity in `app/Modules/Dormitory/Domain/Entities/Building.php` per `data-model.md`
- [ ] T018 [P] [US1] Create `Room` entity in `app/Modules/Dormitory/Domain/Entities/Room.php` with `floorLabel` and `RoomKind`
- [ ] T019 [P] [US1] Create `Bed` entity in `app/Modules/Dormitory/Domain/Entities/Bed.php` with operability + occupancy defaults (`InService`, `Vacant`)
- [ ] T020 [US1] Create `BuildingModel`, `RoomModel`, `BedModel` in `app/Modules/Dormitory/Infrastructure/Persistence/Models/` with intra-module relations
- [ ] T021 [P] [US1] Create repository contracts and implementations for Building, Room, Bed in `app/Modules/Dormitory/Application/Contracts/` and `app/Modules/Dormitory/Infrastructure/Repositories/`
- [ ] T022 [US1] Implement `CreateBuildingAction` in `app/Modules/Dormitory/Application/Services/CreateBuildingAction.php` — reject external parent site
- [ ] T023 [P] [US1] Implement `CreateRoomAction` in `app/Modules/Dormitory/Application/Services/CreateRoomAction.php`
- [ ] T024 [P] [US1] Implement `CreateBedAction` in `app/Modules/Dormitory/Application/Services/CreateBedAction.php` — enforce unique `bed_code` per site
- [ ] T025 [P] [US1] Create Artisan commands `app/Modules/Dormitory/Presentation/Console/CreateDormitorySiteCommand.php` and `AddBedCommand.php` per `quickstart.md`
- [ ] T026 [US1] Feature test `tests/Feature/Modules/Dormitory/InternalHierarchyTest.php` — hierarchy CRUD, floor label, capacity count (BT-D01)
- [ ] T027 [P] [US1] Unit test `tests/Unit/Modules/Dormitory/Domain/BedAssignablePredicateTest.php` — AssignableBed predicate (R-05)
- [ ] T028 [US1] Bind site/building/room/bed repositories and create actions in `app/Modules/Dormitory/Infrastructure/Providers/DormitoryServiceProvider.php`

**Checkpoint**: US1 acceptance scenarios pass; quickstart Scenario 1 pass.

---

## Phase 4: User Story 2 — External Dormitory Catalog (Priority: P1)

**Goal**: External sites without physical inventory.

**Independent Test**: Register external site; reject building/bed creation — per quickstart Scenario 2.

- [ ] T029 [US2] Add `ExternalDormitoryStructureException` guards to `CreateBuildingAction`, `CreateRoomAction`, `CreateBedAction` when parent site `type = external`
- [ ] T030 [US2] Feature test `tests/Feature/Modules/Dormitory/ExternalDormitoryTest.php` — external catalog only; structural children rejected (BT-D02)
- [ ] T031 [P] [US2] Unit test `tests/Unit/Modules/Dormitory/Application/ExternalSiteGuardTest.php` — guard logic isolated

**Checkpoint**: US2 acceptance scenarios pass; quickstart Scenario 2 pass.

---

## Phase 5: User Story 3 — Physical Bed & Room Status (Priority: P2)

**Goal**: Operability status and AssignableBed capacity queries.

**Independent Test**: Mark bed maintenance → assignable count drops — per quickstart Scenario 3.

- [ ] T032 [US3] Implement `UpdateBedOperabilityAction` in `app/Modules/Dormitory/Application/Services/UpdateBedOperabilityAction.php` — transitions per research.md R-07; block when `Reserved`/`Occupied`
- [ ] T033 [US3] Implement assignable capacity query helper in `app/Modules/Dormitory/Domain/Services/AssignableBedPolicy.php` (or inline in read service) per R-05
- [ ] T034 [P] [US3] Feature test `tests/Feature/Modules/Dormitory/BedOperabilityTest.php` — operability transitions; assignable capacity (BT-D03)
- [ ] T035 [US3] Bind `UpdateBedOperabilityAction` in `DormitoryServiceProvider.php`

**Checkpoint**: US3 acceptance scenarios pass; quickstart Scenario 3 pass.

---

## Phase 6: User Story 4 — Allocation-Driven Physical State (Priority: P2)

**Goal**: Occupancy markers via inbound port; no assignment authority on Bed.

**Independent Test**: reserve → occupy → release via port; no person FK — per quickstart Scenario 4.

- [ ] T036 [US4] Create `AllocationPhysicalStatePort` in `app/Modules/Dormitory/Application/Contracts/Ports/AllocationPhysicalStatePort.php` per `contracts/allocation-physical-state-port.md`
- [ ] T037 [US4] Implement `ApplyAllocationPhysicalStateAction` in `app/Modules/Dormitory/Application/Services/ApplyAllocationPhysicalStateAction.php` — enforce occupancy transition matrix; set `last_signal_reference_id`
- [ ] T038 [P] [US4] Create `NullAllocationPhysicalStateAdapter` in `app/Modules/Dormitory/Infrastructure/Adapters/NullAllocationPhysicalStateAdapter.php` for optional test wiring
- [ ] T039 [US4] Feature test `tests/Feature/Modules/Dormitory/AllocationPhysicalStateTest.php` — full matrix happy path + rejections (BT-D04)
- [ ] T040 [P] [US4] Unit test `tests/Unit/Modules/Dormitory/Domain/OccupancyTransitionMatrixTest.php` — all matrix cells per research.md
- [ ] T041 [US4] Bind port and action in `DormitoryServiceProvider.php`

**Checkpoint**: US4 acceptance scenarios pass; quickstart Scenario 4 pass.

---

## Phase 7: User Story 5 — Capacity Supplier Queries (Priority: P3)

**Goal**: Read-only `DormitoryReadContract` for downstream consumers.

**Independent Test**: Query bed status and assignable capacity via contract — per quickstart Scenario 5.

- [ ] T042 [US5] Create DTOs `BedPhysicalStatusDTO`, `AssignableCapacityDTO`, `DormitorySiteSummaryDTO` in `app/Modules/Dormitory/Application/DTOs/` per `contracts/dormitory-read-service.md`
- [ ] T043 [US5] Create `DormitoryReadContract` in `app/Modules/Dormitory/Application/Contracts/DormitoryReadContract.php` per contract doc
- [ ] T044 [US5] Implement `DormitoryReadService` in `app/Modules/Dormitory/Application/Services/DormitoryReadService.php`
- [ ] T045 [P] [US5] Feature test `tests/Feature/Modules/Dormitory/DormitoryReadContractTest.php` — `getBedPhysicalStatus`, `getAssignableCapacity`, `isBedAssignable`
- [ ] T046 [US5] Bind `DormitoryReadContract` in `DormitoryServiceProvider.php`

**Checkpoint**: US5 acceptance scenarios pass; quickstart Scenario 5 pass.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Events, architecture boundary, quality gates.

- [ ] T047 [P] Create domain events `DormitorySiteRegistered`, `BedOperabilityChanged`, `BedOccupancyMarkerChanged` in `app/Modules/Dormitory/Domain/Events/` — `BaseEvent` + `EVENT_NAME`/`VERSION` (R-11)
- [ ] T048 [P] Dispatch events from create/operability/allocation actions after persist
- [ ] T049 Create `tests/Architecture/DormitorySupplierBoundaryTest.php` — BT-D05: no Allocation/Request/Employee/Identity Infrastructure imports
- [ ] T050 Run MVP gate: `php artisan test tests/Feature/Modules/Dormitory tests/Unit/Modules/Dormitory tests/Architecture/DormitorySupplierBoundaryTest.php`; `vendor/bin/phpstan analyse app/Modules/Dormitory`; `vendor/bin/pint app/Modules/Dormitory`
- [ ] T051 Verify all `quickstart.md` scenarios 1–6 pass under Sail

### MVP Gate

Run after T051; **do not deploy without authorization**:

| Gate | Command / criterion |
|------|---------------------|
| Feature + Unit + Architecture | `php artisan test tests/Feature/Modules/Dormitory tests/Unit/Modules/Dormitory tests/Architecture/DormitorySupplierBoundaryTest.php` |
| BT-D01–D05 | Phases 3–8 tests |
| PHPStan | `vendor/bin/phpstan analyse app/Modules/Dormitory` |
| Pint | `vendor/bin/pint app/Modules/Dormitory` |
| Quickstart | Scenarios 1–6 in `quickstart.md` |

**Implementation authorization:** Required before executing T001 — see `handoff/spec04-planning-authorization.md`.

---

## Deferred (out of task scope)

| Item | Reason |
|------|--------|
| Phase H — Livewire admin | plan.md deferred |
| Real Allocation adapter | spec07 |
| CheckIn/CheckOut | OQ-06 |
| Allocation ↔ Dormitory reconciliation | spec07 |
| `events.md` artifact | Optional; R-11 module events only |

---

## Implementation Strategy

1. **Foundational first**: Phases 1–2 (T001–T016) — all migrations before hierarchy actions.
2. **Incremental by story**: US1 internal catalog → US2 external guard → US3 operability → US4 port → US5 read contract → polish.
3. **MVP gate**: Phase 8 before any implementation authorization expansion.
4. **No cross-module FKs**: Enforce in migrations review + BT-D05.
