# Implementation Plan: Accommodation Resource (spec04)

**Branch**: `004-accommodation-resource` | **Date**: 2026-06-23 | **Spec**: [spec.md](./spec.md)

**Input**: Dormitory bounded context — physical accommodation catalog (internal hierarchy + external sites), physical operability status, occupancy markers driven by Allocation signals (CD-014), supplier read API for downstream contexts.

**Governance**: Planning authorized — [`handoff/spec04-planning-authorization.md`](../../.specify/docs/handoff/spec04-planning-authorization.md). Implementation **not** authorized until separate go-ahead.

---

## Pre-Plan Validation

| Source | Check | Result |
| ------ | ----- | ------ |
| [spec.md](./spec.md) | US1–US5, FR-001–FR-013, OA-04-01–OA-04-06 | ✅ Complete; hierarchy and CD-014 split documented |
| [spec-catalog.md](../../.specify/docs/spec-catalog.md) | spec04 Planning Authorized; depends spec01 only | ✅ Aligned |
| [context-map.md](../../.specify/docs/context-map.md) | Dormitory row; R7 Allocation → Dormitory | ✅ Aligned — Dormitory is downstream consumer of Allocation signals |
| [CD-014](../../.specify/docs/catalog-decisions.md) | Dormitory owns physical state; Allocation owns assignment | ✅ Plan respects split; no assignment authority in Dormitory |
| spec03 handoff | US2 complete; US3+ hold | ✅ Unchanged — no Employee coupling required for spec04 |
| Module scaffold | `app/Modules/Dormitory/` (spec01) | ✅ Four-layer paths exist; no business code |

**Catalog note:** Building/floor hierarchy open question **resolved in spec** as OA-04-01 (floor = Room attribute).

---

## Summary

Implement the **Dormitory** module as the physical accommodation resource supplier for Allocation (spec07) and Reporting (spec11). Internal dormitories use **Dormitory → Building → Room → Bed** with **intra-module FKs only**. External dormitories are catalog-only (BR-12). Dormitory owns **physical operability** and **occupancy markers**; it **does not** own person-to-bed assignment (CD-014).

Wave 1 delivery (when implementation authorized): catalog CRUD via Application Actions, physical status management, **stub consumer** for Allocation assignment/release signals, **`DormitoryReadContract`** supplier surface, boundary architecture test, audit via `RecordsActivity`. **No** Request, Allocation implementation, Workflow, CheckIn/CheckOut, or Livewire admin.

---

## Technical Context

| Dimension | Value |
| --------- | ------- |
| **Language/Version** | PHP 8.4; Laravel 13 |
| **Primary Dependencies** | spec01 `app/Support/` (`BaseModel`, `HasUuid`, `RecordsActivity`, `BaseEvent`) |
| **UUID strategy** | UUID v7 via `HasUuid` on all `dormitory_*` tables |
| **Storage** | PostgreSQL 17 — migrations under `database/migrations/modules/dormitory/` |
| **Testing** | Pest PHP 4; unit (Domain/Application), feature (actions + contracts), architecture (boundary) |
| **Target Platform** | Laravel Sail |
| **Performance Goals** | Admin catalog scale (~50 dormitories, ~5,000 beds per constitution) |
| **Constraints** | No cross-module Eloquent; no FK to `allocation_*`, `employee_*`, `identity_*`; Persian RTL admin deferred |
| **Scale/Scope** | 4 entity types (Dormitory, Building, Room, Bed); 1 supplier read contract; 1 inbound signal port (stub until spec07) |

---

## Constitution Check

*GATE: Must pass before implementation. Re-check after Phase 1 design artifacts.*

| Principle | Compliance | Notes |
| --------- | ------------ | ----- |
| AP-01 Technology Stack | ✅ PASS | Laravel 13, PostgreSQL 17, Pest |
| AP-01 Presentation | ⚠️ DEFERRED | Livewire dormitory admin post-MVP |
| AP-02 Modular Monolith | ✅ PASS | `app/Modules/Dormitory/` four layers |
| AP-03 Clean Architecture | ✅ PASS | Domain pure PHP; Eloquent in Infrastructure |
| AP-04 Shared DB / Module Ownership | ✅ PASS | `dormitory_*` tables owned by Dormitory; cross-context UUID refs without FK |
| AP-05 State Machines | ⬜ N/A | Simple enums for operability/occupancy markers — no spatie states Wave 1 |
| AP-06 Audit Everything | ⚠️ CONDITIONAL | `RecordsActivity` on models; central `AuditService` deferred |
| AP-07 Background Processing | ⬜ N/A | Synchronous signal handling Wave 1 |
| AP-08 Configuration | ⬜ N/A | No settings-driven rules in Wave 1 |
| 10.7 Localization | ⚠️ DEFERRED | Persian RTL admin UI deferred |
| 10.4 DoD | ✅ PASS | PHPStan L8, Pint, Pest — planned in tasks (future) |
| CD-014 | ✅ PASS | Physical state in Dormitory; assignment in Allocation |
| BR-12 | ✅ PASS | External dormitories — no bed inventory |
| system-flow INV-2 | ✅ PASS | Assignable capacity excludes non-`InService` beds |

**Post-design re-check**: Deferred to completion of `data-model.md` and `contracts/` (next planning artifacts — not created in this step).

---

## Domain Boundaries

### Owned by Dormitory (spec04)

| Concern | Entities / artifacts |
| ------- | -------------------- |
| Site catalog | `Dormitory` (internal \| external) |
| Physical hierarchy | `Building`, `Room`, `Bed` (internal only) |
| Floor grouping | `Room.floor_label` attribute (OA-04-01) |
| Room classification | `Room.kind` — `private` \| `shared` (OA-04-05) |
| Physical operability | `Bed.operability_status` — `InService`, `OutOfService`, `Maintenance` |
| Physical occupancy markers | `Bed.occupancy_marker` — `Vacant`, `Reserved`, `Occupied` (applied from signals) |
| Capacity queries | Assignable bed counts per **AssignableBed** predicate (R-05) |
| Supplier read API | `DormitoryReadContract` |

### Owned elsewhere (explicitly out of scope)

| Concern | Owner | spec04 interaction |
| ------- | ----- | ------------------ |
| Who is assigned to which bed | **Allocation** | Consumes inbound signals only; no person FK |
| Assignment overlap / exclusion constraints | **Allocation** | spec07 — PostgreSQL exclusion on allocation |
| Accommodation requests | **Request** | None |
| Lottery / scoring | **Lottery** | None |
| Check-in / check-out transitions | **CheckIn/CheckOut** (OQ-06) | **Documented deferral** — not implemented |
| Voucher issuance | **Voucher** | External dormitory ID reference only |
| BR-03 private-room enforcement | **Allocation** | Reads `Room.kind` via contract |

### CD-014 integration model (R7)

```
Allocation (spec07)  ──domain event / application contract──►  Dormitory (spec04)
         │ owns assignment decision                              │ applies occupancy markers
         └──────────────────────────────────────────────────────┘
                              unidirectional
```

**Dormitory MUST NOT:**

- Store `employee_id`, `person_id`, or `allocation_id` as authoritative assignment records on `Bed`
- Query `allocation_*` tables via Eloquent
- Decide assignment eligibility or overlap

**Dormitory MAY:**

- Store `last_signal_reference_id` (optional UUID, no FK) for traceability on marker updates — any signal source (R-06)
- Reject operability changes that violate documented invariants when bed is `Occupied` (policy in plan Phase C)

### Recorded deferrals (documentation only)

| Topic | Status | Where resolved |
| ----- | ------ | -------------- |
| CheckIn/CheckOut module boundary | **OQ-06 OPEN** | spec07 planning |
| Allocation ↔ Dormitory state reconciliation | **Not decided** | spec07 planning |
| Effective occupancy derivation (Allocation + CheckIn) | **CD-014 invariant** | spec07 + OQ-06 |

---

## Required Contracts

*To be formalized under `contracts/` in Phase 1 design artifacts. Names fixed here for task generation.*

### Supplier (outbound — Dormitory exposes)

| Contract | Purpose | Consumers |
| -------- | ------- | --------- |
| `DormitoryReadContract` | Read-only: list dormitories, hierarchy summary, bed physical status, assignable capacity filters | Allocation (spec07), Reporting (spec11) |
| `BedPhysicalStatusDTO` | Operability + occupancy marker + room kind for a bed ID | Allocation assignment validation (INV-2) |
| `DormitoryCapacitySummaryDTO` | Aggregated bed counts by operability/occupancy | Lottery capacity planning (spec06) |

### Consumer port (inbound — Dormitory implements)

| Port / handler | Purpose | Producer (future) |
| -------------- | ------- | ----------------- |
| `AllocationPhysicalStatePort` | Apply `reserve`, `occupy`, `release` markers on bed by bed ID + allocation reference UUID | Allocation (spec07) |
| `NullAllocationPhysicalStateAdapter` | No-op / log stub for spec04 Wave 1 testing without Allocation module | spec04 tests |

### Domain events (outbound — optional Wave 1)

| Event | When |
| ----- | ---- |
| `DormitorySiteRegistered` | Internal or external dormitory created |
| `BedOperabilityChanged` | Physical status transition |
| `BedOccupancyMarkerChanged` | Marker updated (including from Allocation port) |

All events versioned per **module pattern** (`EVENT_NAME` + `VERSION` on `BaseEvent` subclasses — R-11). No kernel event registry yet; shapes in future `events.md`, not in `contracts/`.

### Forbidden imports (architecture test)

- `App\Modules\Allocation\Infrastructure\*`
- `App\Modules\Request\Infrastructure\*`
- `App\Modules\Employee\Infrastructure\*`
- `App\Modules\Identity\Infrastructure\*`

---

## Persistence Strategy

### Table ownership

All tables prefixed `dormitory_*`, owned exclusively by Dormitory module migrations.

| Table | Purpose | Key constraints |
| ----- | ------- | ----------------- |
| `dormitory_sites` | Root dormitory catalog | `type` enum: `internal`, `external`; unique `code` |
| `dormitory_buildings` | Buildings (internal only) | FK `dormitory_id` → `dormitory_sites` (intra-module) |
| `dormitory_rooms` | Rooms | FK `building_id` → `dormitory_buildings`; `floor_label` nullable; `kind` enum |
| `dormitory_beds` | Beds | FK `room_id` → `dormitory_rooms`; unique `(dormitory_id, bed_code)` or scoped code; operability + occupancy columns |

### FK rules

| Allowed | Prohibited |
| ------- | ---------- |
| `building → site`, `room → building`, `bed → room` | FK to `allocation_*`, `employee_*`, `identity_*`, `request_*` |
| Soft deletes via `BaseModel` | Cross-module cascade |

### Migration order

```
dormitory_sites → dormitory_buildings → dormitory_rooms → dormitory_beds
```

### Cross-context references

- Optional `last_signal_reference_id` UUID on `dormitory_beds` — **no FK**, audit trace only (FR-013, R-06)
- No `person_id` / `employee_id` on bed rows

### External dormitory enforcement

- Application layer rejects `CreateBuildingAction` / `CreateRoomAction` / `CreateBedAction` when parent site `type = external` (OA-04-03)
- DB optional check constraint deferred to `data-model.md`

---

## Implementation Phases

*Mapped to spec.md user stories. Task IDs deferred to `/speckit-tasks`.*

### Phase A — Foundation & module wiring (enabler)

- Value objects: `DormitorySiteId`, `BuildingId`, `RoomId`, `BedId`
- Enums: `DormitoryType`, `RoomKind`, `BedOperabilityStatus`, `BedOccupancyMarker`
- Domain exceptions: `DormitoryNotFoundException`, `ExternalDormitoryStructureException`, `BedNotOperableException`, `DuplicateBedCodeException`
- `DormitoryServiceProvider` — migration path, repository bindings (skeleton)
- Architecture test scaffold: `DormitorySupplierBoundaryTest`

### Phase B — Internal catalog (US1 / P1)

- Domain entities: `DormitorySite`, `Building`, `Room`, `Bed`
- Migrations: all four tables (intra-module FKs)
- Repositories + Application Actions: `CreateDormitorySiteAction`, `CreateBuildingAction`, `CreateRoomAction`, `CreateBedAction`
- Feature tests: hierarchy creation, capacity summary, floor label on room
- Artisan commands: `dormitory:create-site`, `dormitory:add-bed` (dev/quickstart pattern from spec03)

### Phase C — External catalog (US2 / P1)

- `CreateDormitorySiteAction` supports `external` type
- Guard actions reject structure under external sites
- Feature tests: external site without children; rejection on add building/bed

### Phase D — Physical operability (US3 / P2)

- `UpdateBedOperabilityAction` — transitions between `InService`, `OutOfService`, `Maintenance`
- Assignable capacity query uses **AssignableBed** predicate: `InService` + `Vacant` + active internal site (R-05) — no `available` column
- Policy: block `OutOfService` when `Occupied` unless force policy documented (default: block)
- Feature tests: operability transitions, capacity query impact

### Phase E — Allocation signal consumer (US4 / P2)

- `AllocationPhysicalStatePort` interface + `ApplyAllocationPhysicalStateAction`
- `NullAllocationPhysicalStateAdapter` for Wave 1; feature tests with explicit port binding
- Marker transitions: `Vacant → Reserved → Occupied → Vacant` on signal types
- **No** Allocation module code — stub signals in tests only
- Verify: no person/employee fields persisted

### Phase F — Supplier read contract (US5 / P3)

- `DormitoryReadContract` + `DormitoryReadService`
- DTOs: `BedPhysicalStatusDTO`, `DormitoryCapacitySummaryDTO`
- Feature test: consumer stub queries bed status and capacity without Infrastructure imports

### Phase G — Polish & MVP gate

- Domain events (`DormitorySiteRegistered`, `BedOperabilityChanged`, `BedOccupancyMarkerChanged`)
- `RecordsActivity` on persistence models
- PHPStan `app/Modules/Dormitory` — 0 errors
- Pint formatting
- `quickstart.md` scenarios pass

### Phase H — Admin UI (deferred)

- Livewire dormitory catalog screens — Persian RTL
- Mirror spec02/spec03 deferred Livewire pattern

---

## MVP Boundary (pre-tasks lock)

**MVP = US1 + US2 + US3 + supplier read skeleton + boundary compliance**

| In MVP | Out of MVP |
| ------ | ---------- |
| Internal + external site catalog | Livewire admin (Phase H) |
| Building / room / bed hierarchy | Real Allocation module adapter (stub port only) |
| Physical operability status | CheckIn/CheckOut transitions |
| Occupancy markers via stub port | Allocation reconciliation jobs |
| `DormitoryReadContract` | Central AuditService integration |
| Boundary architecture test | Reporting read models (spec11) |

---

## Test Strategy

| Layer | Location | Focus |
| ----- | -------- | ----- |
| **Unit** | `tests/Unit/Modules/Dormitory/` | Entity invariants, operability transition rules, external-site guards |
| **Feature** | `tests/Feature/Modules/Dormitory/` | Action flows per US1–US5; contract tests with stub consumer |
| **Architecture** | `tests/Architecture/DormitorySupplierBoundaryTest.php` | No forbidden cross-module Infrastructure imports |
| **Contract** | `tests/Feature/Modules/Dormitory/DormitoryReadContractTest.php` | Supplier API stability for downstream |

### Boundary tests (BT analog)

| ID | Proves |
| -- | ------ |
| BT-D01 | Internal hierarchy CRUD independent of other modules |
| BT-D02 | External site rejects physical children |
| BT-D03 | Non-`InService` bed excluded from assignable capacity (INV-2) |
| BT-D04 | Allocation stub signal updates occupancy marker without person FK |
| BT-D05 | Architecture — no Allocation/Request/Employee/Identity Infrastructure imports |

### Verification gates (MVP)

```bash
php artisan test tests/Feature/Modules/Dormitory tests/Unit/Modules/Dormitory tests/Architecture/DormitorySupplierBoundaryTest.php
vendor/bin/phpstan analyse app/Modules/Dormitory
vendor/bin/pint app/Modules/Dormitory
```

---

## Dependency Order

### Upstream (must exist before implementation)

| Dependency | Status | spec04 usage |
| ---------- | ------ | ------------ |
| spec01 Foundation | Approved | Kernel, module scaffold, `BaseModel`, arch test patterns |
| spec01 migrations pattern | Live | `database/migrations/modules/dormitory/` |

### Not required for spec04

| Module | Reason |
| ------ | ------ |
| spec02 Identity | No identity attachment in Dormitory catalog |
| spec03 Employee | No organizational coupling |
| spec05 Request | Out of scope |
| spec07 Allocation | Stub port only; real adapter in spec07 |

### Downstream (depends on spec04 after delivery)

| Consumer | Needs from Dormitory |
| -------- | ------------------- |
| spec07 Allocation | `DormitoryReadContract`, `AllocationPhysicalStatePort` target |
| spec06 Lottery | Capacity summaries (planning) |
| spec11 Reporting | Read projections |

### Recommended implementation sequence (platform)

```
spec01 → spec02 → spec03 (1A+1B) → spec04 → spec05 → spec06 → spec07 → …
```

---

## Module Structure (documentation)

```text
app/Modules/Dormitory/
├── Domain/
│   ├── Entities/DormitorySite.php, Building.php, Room.php, Bed.php
│   ├── ValueObjects/DormitorySiteId.php, BuildingId.php, RoomId.php, BedId.php
│   ├── Enums/DormitoryType.php, RoomKind.php, BedOperabilityStatus.php, BedOccupancyMarker.php
│   ├── Events/DormitorySiteRegistered.php, BedOperabilityChanged.php, ...
│   └── Exceptions/ExternalDormitoryStructureException.php, ...
├── Application/
│   ├── Contracts/DormitoryReadContract.php
│   ├── Contracts/Ports/AllocationPhysicalStatePort.php
│   ├── DTOs/BedPhysicalStatusDTO.php, DormitoryCapacitySummaryDTO.php
│   └── Services/
│       ├── CreateDormitorySiteAction.php
│       ├── CreateBuildingAction.php, CreateRoomAction.php, CreateBedAction.php
│       ├── UpdateBedOperabilityAction.php
│       ├── ApplyAllocationPhysicalStateAction.php
│       └── DormitoryReadService.php
├── Infrastructure/
│   ├── Persistence/Models/DormitorySiteModel.php, BuildingModel.php, RoomModel.php, BedModel.php
│   ├── Repositories/...
│   ├── Adapters/NullAllocationPhysicalStateAdapter.php
│   └── Providers/DormitoryServiceProvider.php
└── Presentation/
    └── Console/...                         # dormitory:* dev commands

database/migrations/modules/dormitory/
├── *_create_dormitory_sites_table.php
├── *_create_dormitory_buildings_table.php
├── *_create_dormitory_rooms_table.php
└── *_create_dormitory_beds_table.php

specs/004-accommodation-resource/
├── spec.md                                 # ✅ complete
├── plan.md                                 # this file
├── research.md                             # ✅ complete
├── data-model.md                           # ✅ complete
├── contracts/                              # ✅ complete
└── quickstart.md                           # ✅ complete
```

---

## Phase 1 Design Artifacts

| Artifact | Status |
| -------- | ------ |
| [research.md](./research.md) | ✅ Complete — OA-04-xx, AssignableBed, transitions, R-11 event versioning |
| [data-model.md](./data-model.md) | ✅ Complete — schema, `last_signal_reference_id`, AssignableBed |
| [contracts/dormitory-read-service.md](./contracts/dormitory-read-service.md) | ✅ Complete |
| [contracts/allocation-physical-state-port.md](./contracts/allocation-physical-state-port.md) | ✅ Complete |
| [quickstart.md](./quickstart.md) | ✅ Complete |
| `events.md` | ⏳ Optional before implementation — module events only (not public contracts) |

**Review checkpoint:** Validate spec + plan + Phase 1 artifacts before `/speckit-tasks` or implementation authorization.

**`/speckit-tasks`:** Deferred until review checkpoint passed and implementation authorized.

---

## Governance Traceability

| Reference | Relevance |
| --------- | --------- |
| `spec-catalog.md` spec04 | Planning Authorized |
| `handoff/spec04-planning-authorization.md` | Scope fence |
| CD-014 | Domain boundary split |
| `context-map.md` R7 | Integration direction |
| BR-12 | External dormitory |
| OQ-06 | CheckIn/CheckOut — deferred |
| `spec03-wave1b-complete` tag | Upstream baseline unchanged |
