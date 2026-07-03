# Tasks: Allocation & Occupancy (spec07)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md)

**Branch**: `007-allocation-checkin`

**Scope guards** (from spec.md / plan.md):

- **CD-014:** Allocation owns assignment authority only — no physical bed operability or occupancy-marker persistence in Allocation
- **CD-015:** CheckIn/CheckOut is a separate active boundary — not folded into Allocation or Dormitory
- **R5 / R6 / R7:** Consume Request and Lottery via Application contracts only; emit Dormitory signals via `AllocationPhysicalStatePort` / integration events — no cross-module Eloquent
- **No** Voucher policy, Reporting projections, Notification delivery, Workflow engine, Livewire UI, or reconciliation engine (UD-02)
- **UD-07:** `DormitoryReadContract` + `AllocationPhysicalStatePort` may use null/stub adapters until spec04 is live
- **Architecture frozen** — tasks must not alter boundaries ([`architecture-freeze-spec07.md`](../../.specify/governance/freeze/architecture-freeze-spec07.md))

**Authorization gate**: Architecture freeze **APPROVED** · Design Approval **APPROVED WITH CONDITIONS** ([`spec07-design-approved.md`](../../.specify/docs/handoff/spec07-design-approved.md)) · Wave 1A **closed** T006–T052 · Wave 1B **closed** T053–T074

**Status**: **Implementation complete** — all authorized tasks T001–T074 complete; PHPStan/Pint gates satisfied (T072–T073)

---

## Phase Summary

| Phase | Purpose | Task IDs | Scope |
|-------|---------|----------|-------|
| 0 — Design artifacts | `data-model.md`, `contracts/` under this spec | T001–T005 | **MVP** |
| 1 — Setup | Module paths, CheckIn scaffold, DI | T006–T011 | **MVP** |
| 2 — Foundational | VOs, entities, migrations (Allocation + CheckIn) | T012–T028 | **MVP** |
| 3 — US1 (P1) | Allocation assignment authority | T029–T038 | **MVP** |
| 4 — US2 (P1) | Upstream Request + Lottery adapters | T039–T045 | **MVP** |
| 5 — US3 (P2) | Dormitory integration (R7 / ADIC) | T046–T052 | **MVP** |
| 6 — US4 (P2) | CheckIn/CheckOut operational transitions | T053–T060 | Post-MVP expansion |
| 7 — US5 (P2) | Downstream supplier contracts + events | T061–T068 | Post-MVP expansion |
| 8 — Polish | Architecture gate, static analysis | T069–T074 | Post-MVP expansion |

**Total tasks:** 74 (**MVP:** T001–T052 · **Post-MVP:** T053–T074)

---

## User Story Mapping

*Derived from [spec.md](./spec.md) responsibilities and [plan.md](./plan.md) phase design — architecture spec has no formal user-story section.*

| Story | Priority | Phases | Independent Test |
|-------|----------|--------|------------------|
| US1 — Allocation Assignment | P1 | 2→3 | Assign person to bed for date range; reject overlapping assignment (CD-014) |
| US2 — Upstream Integration | P1 | 4 | Create allocation from `RequestReadContract` or `ProposedAllocationPort` payload without upstream mutation |
| US3 — Dormitory Integration | P2 | 5 | On assign/release, emit `AllocationAssigned` / `AllocationReleased` via physical-state port (stub until spec04) |
| US4 — CheckIn/CheckOut Operations | P2 | 6 | Given active allocation → `CheckedIn` → `CheckedOut` via Operator command; no assignment decisions in CheckIn |
| US5 — Downstream Suppliers | P2 | 7 | `AllocationReadContract` answers active-allocation queries; `RequestLifecycleCommandPort` invoked on allocation outcome |

**MVP:** Phases **0–5** (T001–T052) — assignable Allocation with upstream + Dormitory signal path (stubs OK per UD-07).

**Post-MVP expansion:** Phases **6–8** (T053–T074) — CheckIn/CheckOut, downstream suppliers, polish.

---

## Dependency Graph

```text
Phase 0 (Design artifacts)
    └── Phase 1 (Setup)
            └── Phase 2 (Foundational) ──blocks──► Phase 3 (US1) 🎯
                                                      ├──► Phase 4 (US2)
                                                      ├──► Phase 5 (US3)  [null Dormitory OK until spec04]
                                                      ├──► Phase 6 (US4)  [needs US1 assignment facts]
                                                      └──► Phase 7 (US5) ──► Phase 8 (Polish)
```

**Parallel opportunities:** Tasks marked `[P]` within a phase touch different files with no ordering dependency on other `[P]` tasks in the same phase.

**Runtime sequencing:** End-to-end Dormitory integration tests require spec04 `DormitoryReadContract` + `AllocationPhysicalStatePort` live (UD-07) — not an architecture blocker.

---

## Phase 0: Design Artifacts

**Purpose**: Author spec-level design documents referenced in [plan.md](./plan.md) Phase 0 before implementation code.

- [x] T001 Author `specs/007-allocation-checkin/data-model.md` — `Allocation`, `AllocationItem`, `AllocationMethod`, CheckIn stay record; UUID refs without cross-module FKs; PostgreSQL exclusion constraint for overlap (BR-02)
- [x] T002 [P] Create `specs/007-allocation-checkin/contracts/allocation-read-contract.md` per governance stub pack `AllocationReadContract`
- [x] T003 [P] Create `specs/007-allocation-checkin/contracts/check-in-command-port.md` per `CheckInCommandPort`
- [x] T004 [P] Create `specs/007-allocation-checkin/contracts/request-lifecycle-command-port.md` per OA-05-03 / UD-10 open payload note
- [x] T005 [P] Create `specs/007-allocation-checkin/contracts/dormitory-integration-adapters.md` — consumer `App\Modules\Dormitory\Application\Contracts\DormitoryReadContract`, producer `AllocationPhysicalStatePort`, events `AllocationAssigned` / `AllocationReleased` per ADIC-2026-07-01-001

**Checkpoint**: Design artifacts ready for Design Approval handoff.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Wire Allocation module migrations and scaffold CheckIn/CheckOut module per CD-015.

- [x] T006 Verify `app/Modules/Allocation/Infrastructure/Providers/AllocationServiceProvider.php` loads migrations from `database/migrations/modules/allocation/`
- [x] T007 [P] Ensure `database/migrations/modules/allocation/` directory exists
- [x] T008 [P] Scaffold four-layer `app/Modules/CheckIn/` directory structure (`Domain/`, `Application/`, `Infrastructure/`, `Presentation/`) per spec01 conventions
- [x] T009 Create `app/Modules/CheckIn/Infrastructure/Providers/CheckInServiceProvider.php` — register migrations path `database/migrations/modules/check_in/`
- [x] T010 [P] Register `CheckInServiceProvider` in `bootstrap/providers.php`
- [x] T011 [P] Update `app/Modules/Allocation/README.md` and create `app/Modules/CheckIn/README.md` with spec07 scope, CD-014/CD-015, R5/R6/R7

**Checkpoint**: Both modules boot; migration paths wired.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared value objects, enums, domain entities, exceptions, and migrations for Allocation and CheckIn.

**⚠️ CRITICAL**: No user story work until this phase is complete.

- [x] T012 Create `AllocationId`, `AllocationItemId`, `PersonAllocationRef` value objects in `app/Modules/Allocation/Domain/ValueObjects/`
- [x] T013 [P] Create enums in `app/Modules/Allocation/Domain/Enums/` — `AllocationMethod`, `AllocationStatus` per `data-model.md`
- [x] T014 [P] Create domain exceptions in `app/Modules/Allocation/Domain/Exceptions/` — `AllocationNotFoundException`, `AllocationOverlapException`, `InvalidAllocationTransitionException`, `BedNotAssignableException`
- [x] T015 Create migration `database/migrations/modules/allocation/*_create_allocations_table.php` — person ref UUID, bed ref UUID, `daterange`, status; enable `btree_gist` extension migration if not present
- [x] T016 Create migration `database/migrations/modules/allocation/*_create_allocation_items_table.php` — line items linked to parent allocation
- [x] T017 [P] Add PostgreSQL exclusion constraint migration `database/migrations/modules/allocation/*_add_allocation_overlap_exclusion.php` on `(person_id, daterange)` per constitution BR-02
- [x] T018 Implement `Allocation` domain entity in `app/Modules/Allocation/Domain/Models/Allocation.php`
- [x] T019 [P] Implement `AllocationItem` domain entity in `app/Modules/Allocation/Domain/Models/AllocationItem.php`
- [x] T020 Create `AllocationModel` in `app/Modules/Allocation/Infrastructure/Persistence/Models/AllocationModel.php` — `HasUuid`, `RecordsActivity`
- [x] T021 [P] Create `AllocationItemModel` in `app/Modules/Allocation/Infrastructure/Persistence/Models/AllocationItemModel.php`
- [x] T022 Create `AllocationRepositoryContract` in `app/Modules/Allocation/Application/Contracts/` and `AllocationRepository` Eloquent adapter in `Infrastructure/Repositories/`
- [x] T023 [P] Create `CheckInRecordId` value object in `app/Modules/CheckIn/Domain/ValueObjects/`
- [x] T024 [P] Create migration `database/migrations/modules/check_in/*_create_check_in_records_table.php` — allocation ref UUID, checked_in_at, checked_out_at nullable
- [x] T025 Implement `CheckInRecord` domain entity in `app/Modules/CheckIn/Domain/Models/CheckInRecord.php`
- [x] T026 Create `CheckInRecordRepositoryContract` and Eloquent adapter under `app/Modules/CheckIn/`
- [x] T027 Bind Allocation repositories and foundational services in `AllocationServiceProvider.php`
- [x] T028 [P] Bind CheckIn repositories in `CheckInServiceProvider.php`

**Checkpoint**: All `allocation_*` and `check_in_*` tables migrate; overlap constraint enforced at DB level.

---

## Phase 3: US1 — Allocation Assignment Authority (Priority: P1) 🎯

**Goal**: Assignment authority — create and release allocations with overlap prevention (CD-014).

**Independent Test**: Assign person to bed for valid date range → persist; second overlapping assignment for same person rejected.

- [x] T029 [US1] Implement `CreateAllocationAction` in `app/Modules/Allocation/Application/Services/CreateAllocationAction.php` — validate bed ref and date range; persist allocation + items
- [x] T030 [US1] Implement `ReleaseAllocationAction` in `app/Modules/Allocation/Application/Services/ReleaseAllocationAction.php` — terminal release with reason
- [x] T031 [US1] Enforce person-level date-range overlap in `CreateAllocationAction` — catch `AllocationOverlapException` from exclusion constraint
- [x] T032 [P] [US1] Create domain events `AllocationCreated`, `AllocationReleased` in `app/Modules/Allocation/Domain/Events/` — extend `BaseEvent` with `EVENT_NAME` / `VERSION`
- [x] T033 [P] [US1] Unit test `tests/Unit/Modules/Allocation/Domain/AllocationOverlapTest.php` — non-overlapping ranges allowed; overlapping rejected
- [x] T034 [US1] Feature test `tests/Feature/Modules/Allocation/CreateAllocationTest.php` — happy path assign + release
- [x] T035 [P] [US1] Unit test `tests/Unit/Modules/Allocation/Application/ReleaseAllocationTest.php` — invalid release transitions rejected
- [x] T036 [US1] Wire `CreateAllocationAction` and `ReleaseAllocationAction` in `AllocationServiceProvider.php`
- [x] T037 [P] [US1] Add `RecordsActivity` configuration on `AllocationModel` for assign/release operations
- [x] T038 [P] [US1] Document `AllocationMethod` enum usage in `app/Modules/Allocation/README.md` — manual vs lottery-sourced vs request-sourced (architecture-level only)

**Checkpoint**: US1 acceptance — assignment and release without upstream or Dormitory adapters.

---

## Phase 4: US2 — Upstream Integration (Priority: P1)

**Goal**: Consume approved requests and lottery proposed allocations as read-only assignment input (R5, R6).

**Independent Test**: Build allocation from `RequestReadContract` summary and from `ProposedAllocationPort` payload — no Request/Lottery Infrastructure imports.

- [x] T039 [US2] Create `RequestReadAdapter` in `app/Modules/Allocation/Infrastructure/Adapters/RequestReadAdapter.php` binding `App\Modules\Request\Application\Contracts\RequestReadContract`
- [x] T040 [US2] Create `LotteryResultReadAdapter` in `app/Modules/Allocation/Infrastructure/Adapters/LotteryResultReadAdapter.php` binding `App\Modules\Lottery\Application\Contracts\LotteryResultReadContract`
- [x] T041 [US2] Create `ProposedAllocationConsumer` in `app/Modules/Allocation/Application/Services/ProposedAllocationConsumer.php` — map `ProposedAllocationPort` payloads to `CreateAllocationAction`
- [x] T042 [US2] Implement `CreateAllocationFromRequestAction` in `app/Modules/Allocation/Application/Services/CreateAllocationFromRequestAction.php` — approved accommodation requests only
- [x] T043 [P] [US2] Feature test `tests/Feature/Modules/Allocation/RequestDrivenAllocationTest.php` — allocation from Request fixture via read contract
- [x] T044 [P] [US2] Feature test `tests/Feature/Modules/Allocation/LotteryDrivenAllocationTest.php` — allocation from lottery proposed payload stub
- [x] T045 [US2] Register upstream adapters in `AllocationServiceProvider.php` — no cross-module Eloquent

**Checkpoint**: US2 acceptance — assignment creatable from upstream read ports only.

---

## Phase 5: US3 — Dormitory Integration (Priority: P2)

**Goal**: Pre-check capacity via `DormitoryReadContract`; emit physical-marker signals per R7 and ADIC (UD-07 stub acceptable).

**Independent Test**: On assign → `reserve`/`occupy` signal dispatched; on release → `release` signal — via null adapter until spec04 live.

- [x] T046 [US3] Create `DormitoryReadAdapter` in `app/Modules/Allocation/Infrastructure/Adapters/DormitoryReadAdapter.php` — consume `App\Modules\Dormitory\Application\Contracts\DormitoryReadContract` (spec04 supplier); register `NullDormitoryReadAdapter` until spec04 exists; Allocation must NOT implement this contract; do NOT use `App\Modules\Request\Application\Contracts\DormitoryReadContract` for assignability or occupancy logic
- [x] T047 [US3] Create `AllocationPhysicalStateAdapter` in `app/Modules/Allocation/Infrastructure/Adapters/AllocationPhysicalStateAdapter.php` — implement outbound calls to `AllocationPhysicalStatePort` per `specs/004-accommodation-resource/contracts/allocation-physical-state-port.md`
- [x] T048 [US3] Integrate Dormitory pre-check into `CreateAllocationAction` — reject when bed not assignable (INV-2)
- [x] T049 [US3] Emit integration events `AllocationAssigned`, `AllocationReleased` from assign/release actions in `app/Modules/Allocation/Application/Services/`
- [x] T050 [P] [US3] Unit test `tests/Unit/Modules/Allocation/Infrastructure/AllocationPhysicalStateAdapterTest.php` — verify port method invocations on assign/release
- [x] T051 [US3] Feature test `tests/Feature/Modules/Allocation/DormitoryIntegrationTest.php` — use test double for `AllocationPhysicalStatePort`
- [x] T052 [P] [US3] Architecture note in `specs/007-allocation-checkin/contracts/dormitory-integration-adapters.md` — document UD-07 stub vs live spec04 switchover criteria

**Checkpoint**: US3 acceptance — Dormitory signal path wired; full E2E blocked only on spec04 deployment.

---

## Phase 6: US4 — CheckIn/CheckOut Operations (Priority: P2)

**Goal**: Operator-driven `CheckedIn` / `CheckedOut` transitions consuming assignment facts (CD-015).

**Independent Test**: Active allocation → check in → check out; CheckIn module does not create or modify assignments.

- [x] T053 [US4] Define `CheckInCommandPort` interface in `app/Modules/CheckIn/Application/Contracts/CheckInCommandPort.php` per spec contract doc
- [x] T054 [US4] Implement `CheckInService` in `app/Modules/CheckIn/Application/Services/CheckInService.php` implementing `CheckInCommandPort`
- [x] T055 [US4] Implement `CheckInAction` in `app/Modules/CheckIn/Application/Services/CheckInAction.php` — validate active allocation exists via Allocation read port; Operator role gate
- [x] T056 [US4] Implement `CheckOutAction` in `app/Modules/CheckIn/Application/Services/CheckOutAction.php` — require prior check-in; Operator role gate
- [x] T057 [P] [US4] Create domain events `CheckedIn`, `CheckedOut` in `app/Modules/CheckIn/Domain/Events/`
- [x] T058 [US4] Create `AllocationAssignmentReadAdapter` in `app/Modules/CheckIn/Infrastructure/Adapters/AllocationAssignmentReadAdapter.php` — read assignment facts without Allocation Infrastructure Eloquent
- [x] T059 [US4] Feature test `tests/Feature/Modules/CheckIn/CheckInOutFlowTest.php` — full check-in/out on allocated bed
- [x] T060 [P] [US4] Feature test `tests/Feature/Modules/CheckIn/CheckInBoundaryTest.php` — reject check-in without allocation; reject non-Operator

**Checkpoint**: US4 acceptance — operational transitions isolated in CheckIn module.

---

## Phase 7: US5 — Downstream Suppliers (Priority: P2)

**Goal**: Supplier read surface and request lifecycle handoff for Employee, Request, Voucher, Reporting consumers.

**Independent Test**: `AllocationReadContract` returns active allocation for person; allocation outcome invokes `RequestLifecycleCommandPort` stub.

- [x] T061 [US5] Define `AllocationReadContract` in `app/Modules/Allocation/Application/Contracts/AllocationReadContract.php` per spec contract doc
- [x] T062 [US5] Implement `AllocationReadService` in `app/Modules/Allocation/Application/Services/AllocationReadService.php`
- [x] T063 [US5] Define `RequestLifecycleCommandPort` in `app/Modules/Allocation/Application/Contracts/RequestLifecycleCommandPort.php` — document UD-10 payload TBD in contract
- [x] T064 [US5] Implement `RequestLifecycleCommandAdapter` in `app/Modules/Allocation/Infrastructure/Adapters/RequestLifecycleCommandAdapter.php` — stub or no-op until Request consumer ready
- [x] T065 [P] [US5] Create `VoucherIssuancePort` interface + `NullVoucherIssuanceAdapter` in `app/Modules/Allocation/Application/Contracts/` and `Infrastructure/Adapters/` — trigger facts only (CD-016)
- [x] T066 [US5] Feature test `tests/Feature/Modules/Allocation/AllocationReadContractTest.php` — contract shape for `hasActiveAllocation` / active assignment query (replaces spec03 `ActiveAllocationReadPort` stub per UD-11)
- [x] T067 [US5] Register `AllocationReadContract`, `RequestLifecycleCommandPort`, `VoucherIssuancePort` in `AllocationServiceProvider.php`
- [x] T068 [P] [US5] Contract test `tests/Feature/Modules/Allocation/RequestLifecycleHandoffTest.php` — verify command port invoked on successful allocation from request source

**Checkpoint**: US5 acceptance — downstream supplier surfaces registered and testable.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Boundary enforcement, static analysis, and integration verification.

- [x] T069 Create architecture test `tests/Architecture/AllocationBoundaryTest.php` — no forbidden imports from Request, Lottery, Dormitory, Employee Infrastructure layers
- [x] T070 [P] Create architecture test `tests/Architecture/CheckInBoundaryTest.php` — CheckIn does not import Allocation Infrastructure Eloquent
- [x] T071 Feature test `tests/Feature/Modules/Allocation/AllocationIntegrationBoundaryTest.php` — Request read → assign → Dormitory signal → read contract round-trip
- [x] T072 [P] PHPStan level 8 on `app/Modules/Allocation/` and `app/Modules/CheckIn/`
- [x] T073 [P] Run Laravel Pint on `app/Modules/Allocation/` and `app/Modules/CheckIn/`
- [x] T074 Update this `tasks.md` status section when Implementation Authorization handoff is granted

**Checkpoint**: Definition of Done gates from constitution §10.4 satisfied for spec07 scope.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Design artifacts (Phase 0)**: No code dependency — should complete before or in parallel with early Setup
- **Setup (Phase 1)**: Depends on Phase 0 contract shapes (minimum T002–T005 before adapter tasks)
- **Foundational (Phase 2)**: Depends on Setup — **blocks** all user stories
- **User Stories (Phases 3–7)**: Depend on Foundational; US2–US5 depend on US1 core assignment actions
- **Polish (Phase 8)**: Depends on desired user story phases being complete

### User Story Dependencies

- **US1 (P1)**: After Foundational — no upstream adapters required for core assign/release
- **US2 (P1)**: After US1 — extends creation paths with upstream read ports
- **US3 (P2)**: After US1 — hooks into assign/release; stub Dormitory acceptable (UD-07)
- **US4 (P2)**: After US1 — needs assignment facts; independent of US2/US3 for basic check-in/out
- **US5 (P2)**: After US1 — read/command ports over persisted allocations

### Parallel Opportunities

- Phase 0: T002–T005 in parallel after T001 started
- Phase 1: T007, T008, T010, T011 in parallel after T006
- Phase 2: All `[P]` tasks within the phase
- Phases 3–7: `[P]` test and event tasks within each phase
- Phase 8: T070, T072, T073 in parallel

---

## Parallel Example: Phase 2

```bash
# After T012, launch in parallel:
T013 — Allocation enums in app/Modules/Allocation/Domain/Enums/
T014 — Allocation exceptions in app/Modules/Allocation/Domain/Exceptions/
T017 — Overlap exclusion migration
T019 — AllocationItem entity
T021 — AllocationItemModel
T023 — CheckInRecordId value object
T024 — check_in_records migration
```

---

## Implementation Strategy

### MVP First (US1 + US2 + US3 stubs)

1. Complete Phase 0: Design artifacts (T001–T005)
2. Complete Phase 1: Setup (T006–T011)
3. Complete Phase 2: Foundational (T012–T028) — **CRITICAL**
4. Complete Phase 3: US1 — core assignment
5. Complete Phase 4: US2 — upstream adapters
6. Complete Phase 5: US3 — Dormitory signal path with stubs
7. **STOP and VALIDATE** before CheckIn/CheckOut and full supplier rollout

### Incremental Delivery

1. Setup + Foundational → schema and repositories ready
2. US1 → assign/release independently testable
3. US2 → request/lottery-driven assignment
4. US3 → Dormitory integration (stubs → live when spec04 ships)
5. US4 → operational check-in/out
6. US5 → downstream contracts for Employee / Request handoff
7. Polish → architecture and static analysis gates

### Open Dependencies (not resolved by tasks)

| ID | Impact on tasks |
|----|-----------------|
| UD-01 | Event payloads between Allocation, Dormitory, CheckIn — names only in T049/T057 |
| UD-07 | T046/T047 use stubs until spec04 implementation |
| UD-08 | T002–T005 migrate governance stubs to spec-level contracts — **resolved** (Phase 0 artifacts) |
| UD-10 | T063/T064 — `RequestLifecycleCommandPort` payload deferred |
| UD-11 | T066 — `AllocationReadContract` replaces Employee null adapter |

---

## Immediate Next Actions

1. **Obtain Implementation Authorization** handoff (`spec07-implementation-authorization.md`)
2. **T006–T011** — wire module setup and CheckIn scaffold (first code phase after authorization)
3. **T012–T028** — foundational schema (blocks all user stories)
4. **T029–T052** — MVP user stories (US1–US3)
5. **T053–T074** — Post-MVP expansion after MVP validation
