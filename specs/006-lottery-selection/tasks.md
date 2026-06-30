# Tasks: Lottery Selection (spec06)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md)

**Branch**: `006-lottery-selection`

**Scope guards**:

- **Lottery bounded context only** — no Request/Allocation/Employee Infrastructure Eloquent imports
- **CD-011:** All lottery rules, registrations, scoring, results in Lottery
- **R4:** Consume requests via `RequestReadContract` only
- **R5:** Emit proposed allocations via stub port only — no Allocation module code
- **Scoring:** Formula from `settings` — never hardcoded
- **No** Allocation, Voucher, Workflow, Livewire UI in MVP

**Status**: **Complete** — T001–T055 implemented

---

## Phase Summary

| Phase | Purpose | Task IDs | MVP |
|-------|---------|----------|-----|
| 1 — Setup | Module paths, provider wiring | T001–T004 | Yes |
| 2 — Foundational | VOs, states, migrations | T005–T018 | Yes |
| 3 — US1 (P1) | Lottery program lifecycle | T019–T026 | Yes |
| 4 — US2 (P1) | Registration enrollment | T027–T032 | Yes |
| 5 — US3 (P1) | Scoring engine + lock snapshot | T033–T038 | Yes |
| 6 — US4 (P2) | Draw execution + results | T039–T044 | Yes |
| 7 — US5 (P3) | Background jobs | T045–T047 | No |
| 8 — Supplier | Result read contract + stubs | T048–T051 | Yes |
| 9 — Polish | Tests, architecture gate | T052–T055 | Yes |

**Total tasks:** 55 (MVP core: T001–T044, T048–T055)

---

## Dependency Graph

```text
Phase 1 (Setup)
    └── Phase 2 (Foundational) ──blocks──► Phase 3 (US1) 🎯
                                              ├──► Phase 4 (US2)  [needs Program + Request port]
                                              ├──► Phase 5 (US3)  [needs Registration]
                                              ├──► Phase 6 (US4)  [needs Scoring + Lock]
                                              ├──► Phase 7 (US5)  [needs Draw action]
                                              └──► Phase 8 (Supplier) ──► Phase 9 (Polish)
```

---

## Phase 1: Setup

- [x] T001 Verify `LotteryServiceProvider` registers migrations from `database/migrations/modules/lottery/`
- [x] T002 [P] Create `database/migrations/modules/lottery/` directory
- [x] T003 [P] Register `LotteryPresentationServiceProvider` if routes/commands needed
- [x] T004 [P] Update `app/Modules/Lottery/README.md` with spec06 scope and CD-011/R4/R5

---

## Phase 2: Foundational

- [x] T005 Create `LotteryProgramId`, `LotteryRegistrationId` value objects in `Domain/ValueObjects/`
- [x] T006 [P] Create enums — `LotteryProgramStatus`, `LotteryResultOutcome` in `Domain/Enums/`
- [x] T007 [P] Create domain exceptions — `LotteryProgramNotFoundException`, `InvalidLotteryTransitionException`, `RegistrationClosedException`, `DrawNotAllowedException`
- [x] T008 Create migration `*_create_lottery_programs_table.php`
- [x] T009 Create migration `*_create_lottery_registrations_table.php`
- [x] T010 [P] Create migration `*_create_lottery_results_table.php`
- [x] T011 [P] Create migration `*_create_lottery_eligible_snapshots_table.php` (JSON payload at lock)
- [x] T012 Implement `LotteryProgram` domain entity in `Domain/Models/`
- [x] T013 [P] Implement `LotteryRegistration` domain entity
- [x] T014 [P] Implement `LotteryResult` domain entity
- [x] T015 Create `LotteryProgramState` base + state classes per constitution AP-05 (`Draft` … `Completed`, `Cancelled`)
- [x] T016 Create `LotteryProgramRepository` contract + Eloquent adapter
- [x] T017 [P] Create `LotteryRegistrationRepository` contract + adapter
- [x] T018 [P] Create `LotteryResultRepository` contract + adapter

**Checkpoint**: Migrations run; state classes registered with spatie.

---

## Phase 3: US1 — Lottery Program Lifecycle

- [x] T019 `CreateLotteryProgramAction` — draft with dormitory ref, capacity, registration window
- [x] T020 `OpenRegistrationAction` — `Draft`/`Approved` → `RegistrationOpen`
- [x] T021 `CloseRegistrationAction` — → `RegistrationClosed`
- [x] T022 `CancelLotteryProgramAction` — → `Cancelled` with reason
- [x] T023 [P] Unit tests for program state transitions
- [x] T024 [P] Feature test: create program → open registration
- [x] T025 Wire program actions in `LotteryServiceProvider`
- [x] T026 [P] Domain events — `LotteryProgramCreated`, `LotteryProgramStateChanged`

---

## Phase 4: US2 — Registration Enrollment

- [x] T027 Create `RequestReadAdapter` in `Infrastructure/Adapters/` binding `RequestReadContract`
- [x] T028 `EnrollRegistrationAction` — validate approved `lottery_registration` request; persist registration
- [x] T029 Reject duplicate enrollment and closed-program enrollment
- [x] T030 [P] Unit tests for enrollment validation rules
- [x] T031 Feature test: enroll with Request fixture → registration row exists
- [x] T032 [P] Domain event `LotteryRegistrationCreated`

---

## Phase 5: US3 — Scoring & Lock

- [x] T033 Create `LotteryScoringEngine` in `Domain/Services/` — settings-driven formula + PRNG
- [x] T034 `LockLotteryProgramAction` — snapshot eligible registrations; persist `random_seed`; → `Locked`
- [x] T035 Re-validate request approval at lock (OA-06-01)
- [x] T036 [P] Unit test: identical inputs → identical scores (SC-002)
- [x] T037 Create `EmployeeLotteryScorePort` + null/stub adapter
- [x] T038 Feature test: lock program → snapshot + scores persisted

---

## Phase 6: US4 — Draw & Results

- [x] T039 `ExecuteDrawAction` — select winners/reserves to capacity; transactional
- [x] T040 Persist `LotteryResult` rows with rank and outcome
- [x] T041 Transition program `Locked` → `Drawn` → `Completed`
- [x] T042 Create `ProposedAllocationPort` stub — emit payload for spec07
- [x] T043 [P] Unit test: draw idempotency on retry
- [x] T044 Feature test: full path open → enroll → lock → draw → results queryable

---

## Phase 7: US5 — Background Jobs

- [x] T045 `AutoLockLotteryJob` — close + lock past deadline programs
- [x] T046 `ExecuteLotteryDrawJob` — dispatch draw for locked programs
- [x] T047 [P] Feature test: jobs idempotent with queue fake

---

## Phase 8: Supplier Contract

- [x] T048 Author `contracts/lottery-result-read-service.md`
- [x] T049 Implement `LotteryResultReadContract` + `LotteryResultReadService`
- [x] T050 Register contract in `LotteryServiceProvider`
- [x] T051 [P] Contract test: consumer receives winner list by program id

---

## Phase 9: Polish

- [x] T052 Architecture test — no forbidden cross-module imports (SC-005)
- [x] T053 [P] `LotteryScoringEngineTest` reproducibility suite
- [x] T054 PHPStan level 8 on Lottery module
- [x] T055 [P] Run Pint on `app/Modules/Lottery/`

---

## Parallel Opportunities

Tasks marked `[P]` within a phase may run in parallel when they touch disjoint files.

---

## Immediate Next Actions

1. **T001–T004** — wire module setup and migration path
2. **T005–T018** — foundational schema and state machine (blocks all user stories)
3. **T019–T026** — program lifecycle (first demonstrable slice)
4. Author `data-model.md` and `contracts/` in parallel with T008–T011 if not yet present
