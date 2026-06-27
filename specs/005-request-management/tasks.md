# Tasks: Request Management (spec05)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md) (§MVP waves), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/](./contracts/), [quickstart.md](./quickstart.md)

**Branch**: `005-request-management`

**Design checkpoint**: tag `spec05-design-approved` → commit `6ce0e94`

**Scope guards** (from plan.md):

- **Request bounded context only** — no Employee/Dormitory/Allocation/Lottery Infrastructure imports (BT-R05)
- **CD-009:** Dependent **snapshots** only — no `employee_dependents` FK
- **CD-010:** `RequestApproval` append-only; inline routing; Workflow deferred
- **CD-013:** Enforce BR-01 at submit via `EmployeeEligibilityContract`; Employee computes
- **OA-05-09:** `PendingRequestReadPort` — query-only; `RequestReadContract` — read-only projections ([contracts/](./contracts/))
- **OA-05-01:** Terminal at `Approved` — no `WaitingForAllocation`+ states (spec07)
- **No** Workflow engine, Allocation, Lottery implementation, Livewire UI

**Status**: **Design approved** · **Implementation not authorized** until separate go-ahead ([`handoff/spec05-design-approved.md`](../../.specify/docs/handoff/spec05-design-approved.md)).

---

## Phase Summary

| Phase | Purpose | Task IDs | MVP wave |
|-------|---------|----------|----------|
| 1 — Setup | Module paths, provider wiring | T001–T004 | 1A |
| 2 — Foundational | VOs, enums, states, migrations | T005–T020 | 1A |
| 3 — US1 (P1) | Personal request draft/submit | T021–T026 | 1A |
| 4 — US2 (P1) | Lifecycle + cancellation | T027–T030 | 1A |
| 5 — US3 (P2) | Four-stage approval | T031–T036 | 1A |
| 6 — US4 (P2) | FamilyDirect snapshots | T037–T039 | 1B |
| 7 — US5 (P3) | Mission / BR-04 | T040–T042 | 1C |
| 8 — US6 (P3) | LotteryRegistration type | T043–T044 | 1C |
| 9 — Supplier | Read contracts + pending port adapter | T045–T049 | 1A |
| 10 — Polish | Events, boundary tests, MVP gate | T050–T052 | 1A |

**Total tasks:** 52 (Wave 1A: T001–T036, T045–T052; Wave 1B: T037–T039; Wave 1C: T040–T044)

---

## User Story Mapping

| Story | Priority | Phases | Independent Test |
|-------|----------|--------|------------------|
| US1 — Personal Request | P1 | 2→3 | Draft/submit with eligibility + dates |
| US2 — Lifecycle & Cancellation | P1 | 4 | Cancel early; reject terminal |
| US3 — Four-Stage Approval | P2 | 5 | DeptMgr → … → `Approved` |
| US4 — FamilyDirect Snapshots | P2 | 6 | Immutable dependent rows |
| US5 — Mission Group | P3 | 7 | 2–20 members + leader |
| US6 — LotteryRegistration | P3 | 8 | Type flag; supplier read |

**Suggested MVP:** Wave **1A** (T001–T036, T045–T052) before Wave 1B/1C expansion.

---

## Dependency Graph

```text
Phase 1 (Setup)
    └── Phase 2 (Foundational) ──blocks──► Phase 3 (US1) 🎯
                                              ├──► Phase 4 (US2)
                                              ├──► Phase 5 (US3)
                                              ├──► Phase 6 (US4)  [spec03 US3 gate]
                                              ├──► Phase 7 (US5)
                                              ├──► Phase 8 (US6)
                                              └──► Phase 9 (Supplier) ──► Phase 10 (Polish)
```

**Parallel opportunities:** Tasks marked `[P]` within a phase touch different files with no ordering dependency on other `[P]` tasks in the same phase.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Request module migration path and DI scaffolding.

- [x] T001 Verify `app/Modules/Request/Infrastructure/Providers/RequestServiceProvider.php` loads migrations from `database/migrations/modules/request/` per plan.md
- [x] T002 [P] Ensure `database/migrations/modules/request/` directory exists
- [x] T003 [P] Add `RequestPresentationServiceProvider` in `app/Modules/Request/Presentation/Providers/RequestPresentationServiceProvider.php` for Artisan commands; register in `bootstrap/providers.php`
- [x] T004 [P] Update `app/Modules/Request/README.md` with spec05 scope, CD-009/010/013, OA-05-09

**Checkpoint**: Module boots; migrations path wired.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared value objects, enums, state machine classes, exceptions, and **all** `request_*` migrations (FK order per `data-model.md`).

**⚠️ CRITICAL**: No user story work until this phase is complete.

- [x] T005 Create `RequestId` and `RequestCode` value objects in `app/Modules/Request/Domain/ValueObjects/`
- [x] T006 [P] Create enums in `app/Modules/Request/Domain/Enums/` — `RequestType`, `ApprovalStage`, `ApprovalDecision` per `data-model.md`
- [x] T007 [P] Create domain exceptions — `RequestNotFoundException`, `InvalidRequestTransitionException`, `RequestNotEligibleException`, `RequestValidationException`, `InvalidGroupRequestException`
- [x] T008 Create migration `database/migrations/modules/request/*_create_requests_table.php` per `data-model.md`
- [x] T009 Create migration `database/migrations/modules/request/*_create_request_approvals_table.php` — FK `request_id`; append-only (no `updated_at`)
- [x] T010 [P] Create migration `database/migrations/modules/request/*_create_request_dependent_snapshots_table.php`
- [x] T011 [P] Create migration `database/migrations/modules/request/*_create_request_members_table.php`
- [x] T012 [P] Create migration `database/migrations/modules/request/*_create_request_mission_details_table.php` — 1:1 PK `request_id`
- [x] T013 Create `Request` entity in `app/Modules/Request/Domain/Entities/Request.php` per `data-model.md`
- [x] T014 Create `RequestModel` in `app/Modules/Request/Infrastructure/Persistence/Models/RequestModel.php` — `HasUuid`, `RecordsActivity`, spatie `HasStates`
- [x] T015 Create `RequestRepositoryContract` and `RequestRepository` in Application/Infrastructure layers
- [x] T016 Implement `RequestCodeGenerator` domain/application service — pattern `REQ-YYYYMMDD-NNNN` (R-02)
- [x] T017 Create base `RequestState` and `DraftState` in `app/Modules/Request/Domain/States/` per R-07
- [x] T018 [P] Create pending state classes — `SubmittedState`, `PendingDepartmentManagerState`, `PendingHRState`, `PendingDormitoryManagerState`, `PendingDormitoryUnitState`
- [x] T019 [P] Create terminal state classes — `ApprovedState`, `RejectedState`, `CancelledState`
- [x] T020 Bind repositories and foundational services in `RequestServiceProvider.php`

**Checkpoint**: All `request_*` tables migrate; `Draft` state persistable.

---

## Phase 3: User Story 1 — Personal Request (Priority: P1) 🎯

**Goal**: Personal draft/create and submit with BR-01 enforcement.

**Independent Test**: Create Personal request → submit → enters approval pipeline — per quickstart Scenario 1.

- [ ] T021 [US1] Implement `CreatePersonalRequestAction` in `app/Modules/Request/Application/Services/CreatePersonalRequestAction.php`
- [ ] T022 [US1] Implement `SubmitRequestAction` — pipeline per [contracts/request-eligibility-enforcement.md](./contracts/request-eligibility-enforcement.md)
- [ ] T023 [P] [US1] Create `NullDormitoryReadAdapter` in `app/Modules/Request/Infrastructure/Adapters/` (R-06)
- [ ] T024 [US1] Feature test `tests/Feature/Modules/Request/PersonalRequestTest.php` — BT-R01, BT-R02
- [ ] T025 [P] [US1] Artisan commands `CreatePersonalRequestCommand`, `SubmitRequestCommand` per `quickstart.md`
- [ ] T026 [P] [US1] Unit test `tests/Unit/Modules/Request/Application/SubmitDateValidationTest.php` — BR-01 date subset

**Checkpoint**: US1 acceptance scenarios pass; quickstart Scenarios 1–2.

---

## Phase 4: User Story 2 — Lifecycle & Cancellation (Priority: P1)

**Goal**: Governed transitions; cancel only from `Draft`/`Submitted`.

**Independent Test**: Cancel/reject paths — per quickstart Scenarios 4–5.

- [ ] T027 [US2] Implement `CancelRequestAction` — FR-017 / R-07
- [ ] T028 [P] [US2] Create domain events `RequestSubmitted`, `RequestCancelled`, `RequestRejected` — `BaseEvent` + `EVENT_NAME`/`VERSION` (R-13)
- [ ] T029 [US2] Feature test `tests/Feature/Modules/Request/RequestLifecycleTest.php` — cancel rules, reject terminal
- [ ] T030 [P] [US2] Unit test `tests/Unit/Modules/Request/Domain/RequestTransitionMatrixTest.php` — R-07 matrix cells

**Checkpoint**: US2 acceptance scenarios pass.

---

## Phase 5: User Story 3 — Four-Stage Approval (Priority: P2)

**Goal**: Append-only `RequestApproval` and inline routing to `Approved`.

**Independent Test**: Four approvals → `Approved` — per quickstart Scenario 3.

- [ ] T031 [US3] Implement `ApproveRequestStageAction` in `app/Modules/Request/Application/Services/ApproveRequestStageAction.php`
- [ ] T032 [P] [US3] Implement `RejectRequestAction` with required reason
- [ ] T033 [US3] Create `RequestApproval` entity + `RequestApprovalModel` — append-only persistence (R-08)
- [ ] T034 [P] [US3] Implement `AutoApprovalSettingsReader` — keys per R-09 (`request.approval.auto.*`)
- [ ] T035 [US3] Feature test `tests/Feature/Modules/Request/RequestApprovalTest.php` — BT-R03, BT-R04
- [ ] T036 [US3] Bind approval actions; dispatch events on stage completion

**Checkpoint**: US3 acceptance scenarios pass; quickstart Scenario 3.

---

## Phase 6: User Story 4 — FamilyDirect Snapshots (Priority: P2) — Wave 1B

**Goal**: Immutable dependent snapshots (CD-009).

**Gate**: spec03 **US3** authorized or approved fixture strategy.

- [ ] T037 [US4] Implement `CreateFamilyDirectRequestAction` with snapshot capture at submit
- [ ] T038 [US4] Feature test `tests/Feature/Modules/Request/FamilyDirectSnapshotTest.php` — BT-R06
- [ ] T039 [P] [US4] Test fixture `DependentSnapshotSourceStub` when Employee US3 unavailable

**Checkpoint**: US4 acceptance scenarios pass; quickstart Scenario 7.

---

## Phase 7: User Story 5 — Mission Group Request (Priority: P3) — Wave 1C

**Goal**: `RequestMember` + BR-04 validation.

- [ ] T040 [US5] Implement `CreateMissionRequestAction` with `request_members` + `request_mission_details`
- [ ] T041 [P] [US5] Enforce 2–20 members, exactly one leader, leader in member set (BR-04)
- [ ] T042 [US5] Feature test `tests/Feature/Modules/Request/MissionRequestTest.php` — BT-R07

**Checkpoint**: US5 acceptance scenarios pass; quickstart Scenario 8.

---

## Phase 8: User Story 6 — LotteryRegistration (Priority: P3) — Wave 1C

**Goal**: LotteryRegistration type without lottery rules (CD-011 → spec06).

- [ ] T043 [US6] Extend create flow for `LotteryRegistration` type (same shape as Personal)
- [ ] T044 [US6] Feature test `tests/Feature/Modules/Request/LotteryRegistrationRequestTest.php` — quickstart Scenario 9

**Checkpoint**: US6 acceptance scenarios pass.

---

## Phase 9: Supplier Contracts

**Goal**: Downstream read API and Employee pending port loop.

- [ ] T045 Create DTOs and `RequestReadContract` per [contracts/request-read-service.md](./contracts/request-read-service.md)
- [ ] T046 Implement `RequestReadService` in `app/Modules/Request/Application/Services/RequestReadService.php`
- [ ] T047 Implement `PendingRequestReadAdapter` + internal `PendingRequestQueryPort` per [contracts/employee-request-boundary.md](./contracts/employee-request-boundary.md) (OA-05-09)
- [ ] T048 Register `PendingRequestReadPort` binding — replaces Employee null stub when integrated
- [ ] T049 Feature test `tests/Feature/Modules/Request/RequestReadContractTest.php` + `PendingRequestReadPortTest.php` — BT-R08, BT-R09

**Checkpoint**: Supplier contracts pass; quickstart Scenarios 6, 10.

---

## Phase 10: Polish & Cross-Cutting Concerns

- [ ] T050 [P] Add events `RequestApproved`, `RequestApprovalRecorded`; dispatch from approval actions (R-13)
- [ ] T051 Create `tests/Architecture/RequestConsumerBoundaryTest.php` — BT-R05, BT-R09
- [ ] T052 Run MVP gate: `php artisan test tests/Feature/Modules/Request tests/Unit/Modules/Request tests/Architecture/RequestConsumerBoundaryTest.php`; `vendor/bin/phpstan analyse app/Modules/Request`; `vendor/bin/pint app/Modules/Request`; verify `quickstart.md` Wave 1A scenarios

### MVP Gate (Wave 1A)

| Gate | Command / criterion |
|------|---------------------|
| Feature + Unit + Architecture | `php artisan test tests/Feature/Modules/Request tests/Unit/Modules/Request tests/Architecture/RequestConsumerBoundaryTest.php` |
| BT-R01–R05, R08–R09 | Phases 3–5, 9–10 |
| PHPStan | `vendor/bin/phpstan analyse app/Modules/Request` |
| Pint | `vendor/bin/pint app/Modules/Request` |
| Quickstart | Scenarios 1–6, 10 (Wave 1A) |

**Implementation authorization:** Required before executing T001 — separate from design approval.

---

## Deferred (out of task scope)

| Item | Reason |
|------|--------|
| Post-approval states (`WaitingForAllocation`+) | spec07 (OA-05-03) |
| Workflow engine | Deferred (CD-010) |
| Lottery draw / scoring | spec06 (CD-011) |
| Allocation overlap / assignment | spec07 (CD-014) |
| Livewire request UI | plan Phase I deferred |
| Real `DormitoryReadContract` adapter | spec04 implementation |

---

## Implementation Strategy

1. **Foundational first**: Phases 1–2 (T001–T020) — all migrations and state classes before submit.
2. **Wave 1A MVP**: Phases 3–5 + 9–10 (T021–T036, T045–T052) — Personal through approval + supplier port.
3. **Wave 1B**: Phase 6 after spec03 US3 or fixture approval.
4. **Wave 1C**: Phases 7–8 — Mission and LotteryRegistration types.
5. **No cross-module FKs**: Enforce in migration review + BT-R05.
