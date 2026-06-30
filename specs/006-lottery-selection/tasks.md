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

**Status**: **Execution initialized** — tasks defined; implementation not started

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

- [ ] T001 Verify `LotteryServiceProvider` registers migrations from `database/migrations/modules/lottery/`
- [ ] T002 [P] Create `database/migrations/modules/lottery/` directory
- [ ] T003 [P] Register `LotteryPresentationServiceProvider` if routes/commands needed
- [ ] T004 [P] Update `app/Modules/Lottery/README.md` with spec06 scope and CD-011/R4/R5

---

## Phase 2: Foundational

- [ ] T005 Create `LotteryProgramId`, `LotteryRegistrationId` value objects in `Domain/ValueObjects/`
- [ ] T006 [P] Create enums — `LotteryProgramStatus`, `LotteryResultOutcome` in `Domain/Enums/`
- [ ] T007 [P] Create domain exceptions — `LotteryProgramNotFoundException`, `InvalidLotteryTransitionException`, `RegistrationClosedException`, `DrawNotAllowedException`
- [ ] T008 Create migration `*_create_lottery_programs_table.php`
- [ ] T009 Create migration `*_create_lottery_registrations_table.php`
- [ ] T010 [P] Create migration `*_create_lottery_results_table.php`
- [ ] T011 [P] Create migration `*_create_lottery_eligible_snapshots_table.php` (JSON payload at lock)
- [ ] T012 Implement `LotteryProgram` domain entity in `Domain/Models/`
- [ ] T013 [P] Implement `LotteryRegistration` domain entity
- [ ] T014 [P] Implement `LotteryResult` domain entity
- [ ] T015 Create `LotteryProgramState` base + state classes per constitution AP-05 (`Draft` … `Completed`, `Cancelled`)
- [ ] T016 Create `LotteryProgramRepository` contract + Eloquent adapter
- [ ] T017 [P] Create `LotteryRegistrationRepository` contract + adapter
- [ ] T018 [P] Create `LotteryResultRepository` contract + adapter

**Checkpoint**: Migrations run; state classes registered with spatie.

---

## Phase 3: US1 — Lottery Program Lifecycle

- [ ] T019 `CreateLotteryProgramAction` — draft with dormitory ref, capacity, registration window
- [ ] T020 `OpenRegistrationAction` — `Draft`/`Approved` → `RegistrationOpen`
- [ ] T021 `CloseRegistrationAction` — → `RegistrationClosed`
- [ ] T022 `CancelLotteryProgramAction` — → `Cancelled` with reason
- [ ] T023 [P] Unit tests for program state transitions
- [ ] T024 [P] Feature test: create program → open registration
- [ ] T025 Wire program actions in `LotteryServiceProvider`
- [ ] T026 [P] Domain events — `LotteryProgramCreated`, `LotteryProgramStateChanged`

---

## Phase 4: US2 — Registration Enrollment

- [ ] T027 Create `RequestReadAdapter` in `Infrastructure/Adapters/` binding `RequestReadContract`
- [ ] T028 `EnrollRegistrationAction` — validate approved `lottery_registration` request; persist registration
- [ ] T029 Reject duplicate enrollment and closed-program enrollment
- [ ] T030 [P] Unit tests for enrollment validation rules
- [ ] T031 Feature test: enroll with Request fixture → registration row exists
- [ ] T032 [P] Domain event `LotteryRegistrationCreated`

---

## Phase 5: US3 — Scoring & Lock

- [ ] T033 Create `LotteryScoringEngine` in `Domain/Services/` — settings-driven formula + PRNG
- [ ] T034 `LockLotteryProgramAction` — snapshot eligible registrations; persist `random_seed`; → `Locked`
- [ ] T035 Re-validate request approval at lock (OA-06-01)
- [ ] T036 [P] Unit test: identical inputs → identical scores (SC-002)
- [ ] T037 Create `EmployeeLotteryScorePort` + null/stub adapter
- [ ] T038 Feature test: lock program → snapshot + scores persisted

---

## Phase 6: US4 — Draw & Results

- [ ] T039 `ExecuteDrawAction` — select winners/reserves to capacity; transactional
- [ ] T040 Persist `LotteryResult` rows with rank and outcome
- [ ] T041 Transition program `Locked` → `Drawn` → `Completed`
- [ ] T042 Create `ProposedAllocationPort` stub — emit payload for spec07
- [ ] T043 [P] Unit test: draw idempotency on retry
- [ ] T044 Feature test: full path open → enroll → lock → draw → results queryable

---

## Phase 7: US5 — Background Jobs

- [ ] T045 `AutoLockLotteryJob` — close + lock past deadline programs
- [ ] T046 `ExecuteLotteryDrawJob` — dispatch draw for locked programs
- [ ] T047 [P] Feature test: jobs idempotent with queue fake

---

## Phase 8: Supplier Contract

- [ ] T048 Author `contracts/lottery-result-read-service.md`
- [ ] T049 Implement `LotteryResultReadContract` + `LotteryResultReadService`
- [ ] T050 Register contract in `LotteryServiceProvider`
- [ ] T051 [P] Contract test: consumer receives winner list by program id

---

## Phase 9: Polish

- [ ] T052 Architecture test — no forbidden cross-module imports (SC-005)
- [ ] T053 [P] `LotteryScoringEngineTest` reproducibility suite
- [ ] T054 PHPStan level 8 on Lottery module
- [ ] T055 [P] Run Pint on `app/Modules/Lottery/`

---

## Parallel Opportunities

Tasks marked `[P]` within a phase may run in parallel when they touch disjoint files.

---

## Immediate Next Actions

1. **T001–T004** — wire module setup and migration path
2. **T005–T018** — foundational schema and state machine (blocks all user stories)
3. **T019–T026** — program lifecycle (first demonstrable slice)
4. Author `data-model.md` and `contracts/` in parallel with T008–T011 if not yet present
