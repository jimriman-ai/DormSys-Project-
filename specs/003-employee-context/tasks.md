# Tasks: Employee Context (spec03)

**Input**: [spec.md](./spec.md), [plan.md](./plan.md) (§5 MVP boundary), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/](./contracts/), [events.md](./events.md), [quickstart.md](./quickstart.md)

**Branch**: `003-employee-context`

**Scope guards** (from plan.md):

- **Identity consumer only:** `IdentityUserReadContract` — no Identity Infrastructure imports (BT-05)
- **CD-012:** `identity_id` immutable, unique, no FK to `identity_users`
- **OA-03-02:** `userExists` required at create; `isUserActive` **not** a create gate
- **CD-013:** Eligibility computation in Employee; BR-01 allocation/request via **stub ports** until spec05/spec07
- **No** Request, Allocation, or login UI tasks

**Status**: Wave 1A — ready for `/speckit-implement`

---

## Phase Summary

| Phase | Purpose | Task IDs | MVP? |
|-------|---------|----------|------|
| 1 — Setup | Module paths, provider wiring | T001–T004 | Yes |
| 2 — Foundational | VOs, enums, exceptions, core migrations | T005–T014 | Yes |
| 3 — US1 (P1) | Employee + `identity_id` boundary | T015–T026 | Yes |
| 4 — US2 (P2) | Department CRUD + assignment | T027–T034 | No |
| 5 — US3 (P2) | Dependent CRUD (CD-009) | T035–T040 | No |
| 6 — US4 (P3) | Eligibility supplier + stub ports | T041–T048 | No |
| 7 — Supplier read | `EmployeeReadContract` | T049–T052 | No |
| 8 — Polish | BT-05 arch, PHPStan, quickstart | T053–T058 | Yes (quality) |

**Total tasks:** 58

---

## User Story Mapping

| Story | Priority | Phases | Independent Test |
|-------|----------|--------|------------------|
| US1 — Employee Profile with Identity Attachment | P1 | 2→3 | Create employee with `identity_id` → immutability → reject unknown UUID (BT-01–03) |
| US2 — Department & Organizational Structure | P2 | 4 | Department CRUD; assign employee; query by department |
| US3 — Dependent Records | P2 | 5 | Add/list/update dependents under employee |
| US4 — Eligibility Computation | P3 | 6 | `EmployeeEligibilityContract` — active vs inactive; mock port blocking |

**Suggested MVP:** Phases 1–3 (T001–T026) — Employee aggregate with CD-012 boundary compliance.

---

## Dependency Graph

```text
Phase 1 (Setup)
    └── Phase 2 (Foundational) ──blocks──► Phase 3 (US1) 🎯 MVP
                                              ├──► Phase 4 (US2)  [needs Employee aggregate]
                                              ├──► Phase 5 (US3)  [needs Employee aggregate]
                                              └──► Phase 6 (US4)  [needs Employee + repository]
                                                        └──► Phase 7 (Read contract)
Phase 8 (Polish) — after US1 minimum; full suite after US4
```

**Parallel opportunities:** Tasks marked `[P]` within a phase touch different files and have no ordering dependency on other `[P]` tasks in the same phase.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Employee module migration path and DI scaffolding — depends on spec02 `IdentityUserReadContract` (frozen).

- [ ] T001 Verify `app/Modules/Employee/Infrastructure/Providers/EmployeeServiceProvider.php` loads migrations from `database/migrations/modules/employee/` per plan.md §2
- [ ] T002 [P] Ensure `database/migrations/modules/employee/` directory exists and is empty-ready for module migrations
- [ ] T003 [P] Add `EmployeePresentationServiceProvider` in `app/Modules/Employee/Presentation/Providers/EmployeePresentationServiceProvider.php` for Artisan commands (mirror Identity pattern) and register in `bootstrap/providers.php`
- [ ] T004 [P] Document spec02 prerequisite in `app/Modules/Employee/README.md` stub — Identity user must exist before `employee:create`

**Checkpoint**: Module boots; migrations path wired; no cross-module Infrastructure imports yet.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared value objects, enums, exceptions, and **department + employee** table migrations (FK order per `data-model.md`).

**⚠️ CRITICAL**: No user story work until this phase is complete.

- [ ] T005 Create `EmployeeId` value object in `app/Modules/Employee/Domain/ValueObjects/EmployeeId.php` with `fromString()` validation
- [ ] T006 [P] Create `DepartmentId` value object in `app/Modules/Employee/Domain/ValueObjects/DepartmentId.php`
- [ ] T007 [P] Create `DependentId` value object in `app/Modules/Employee/Domain/ValueObjects/DependentId.php`
- [ ] T008 [P] Create enums in `app/Modules/Employee/Domain/Enums/` — `EmployeeStatus.php`, `DepartmentStatus.php`, `DependentRelationship.php` per `data-model.md`
- [ ] T009 [P] Create domain exceptions in `app/Modules/Employee/Domain/Exceptions/` — `IdentityIdImmutableException`, `EmployeeNotFoundException`, `DuplicateIdentityIdException`, `UnknownIdentityUserException`, `InactiveDepartmentAssignmentException`
- [ ] T010 Create migration `database/migrations/modules/employee/*_create_employee_departments_table.php` per `data-model.md` (UUID PK, code unique, manager_id/parent_id self-FKs nullable, status, audit columns)
- [ ] T011 Create migration `database/migrations/modules/employee/*_create_employee_employees_table.php` — `identity_id` UUID **unique**, **no FK** to Identity; `department_id` nullable FK → `employee_departments`; `national_code` unique; `employee_code` unique
- [ ] T012 Create pure PHP `Employee` entity in `app/Modules/Employee/Domain/Entities/Employee.php` with `identityId` set-once invariant and `EmployeeStatus` transitions per `data-model.md`
- [ ] T013 Create `EmployeeModel` in `app/Modules/Employee/Infrastructure/Persistence/Models/EmployeeModel.php` extending `App\Support\Models\BaseModel` with `HasUuid`, `RecordsActivity`; table `employee_employees`; **guard** `identity_id` from mass-assignment updates after create
- [ ] T014 Create `EmployeeRepositoryContract` in `app/Modules/Employee/Application/Contracts/EmployeeRepositoryContract.php` and Eloquent `EmployeeRepository` in `app/Modules/Employee/Infrastructure/Repositories/EmployeeRepository.php` with `save()`, `findById()`, `findByIdentityId()`, `assertIdentityIdImmutable()` for BT-02

**Checkpoint**: `employee_departments` + `employee_employees` migrate; repository persists Employee — Identity contract not wired yet.

---

## Phase 3: User Story 1 — Employee Profile with Identity Attachment (Priority: P1) 🎯 MVP

**Goal**: Create employee linked once to Identity via immutable `identity_id`; validate via `IdentityUserReadContract` only.

**Independent Test**: `CreateEmployeeAction` with valid/invalid `UserId` — BT-01 success, BT-02 mutation blocked, BT-03 unknown rejected; inactive Identity user allowed (OA-03-02).

### Domain event

- [ ] T015 [US1] Create `EmployeeCreated` event in `app/Modules/Employee/Domain/Events/EmployeeCreated.php` with payload per `events.md` (`employeeId`, `identityId`, `occurredAt`)

### Application action

- [ ] T016 [US1] Implement `CreateEmployeeAction` in `app/Modules/Employee/Application/Services/CreateEmployeeAction.php` — inject `IdentityUserReadContract`; call `userExists` only (not `isUserActive`); persist via `EmployeeRepository`; dispatch `EmployeeCreated` synchronously
- [ ] T017 [P] [US1] Create Artisan command `app/Modules/Employee/Presentation/Console/CreateEmployeeCommand.php` (`employee:create`) for dev/quickstart per `quickstart.md` Scenario 1

### Boundary tests (BT-01–BT-03)

- [ ] T018 [US1] Feature test `tests/Feature/Modules/Employee/EmployeeIdentityBoundaryTest.php` — **BT-01**: create with valid `identity_id` succeeds and is immutable on reload
- [ ] T019 [US1] Extend `tests/Feature/Modules/Employee/EmployeeIdentityBoundaryTest.php` — **BT-02**: attempt `identity_id` update via repository/model throws `IdentityIdImmutableException`
- [ ] T020 [US1] Extend `tests/Feature/Modules/Employee/EmployeeIdentityBoundaryTest.php` — **BT-03**: create with unknown UUID rejected; no row persisted
- [ ] T021 [P] [US1] Extend `tests/Feature/Modules/Employee/EmployeeIdentityBoundaryTest.php` — **OA-03-02**: create succeeds when Identity user is disabled (`userExists` true, `isUserActive` false)

### Unit & audit tests

- [ ] T022 [P] [US1] Unit test `tests/Unit/Modules/Employee/Domain/EmployeeTest.php` — identity immutability, status transitions
- [ ] T023 [P] [US1] Unit test `tests/Unit/Modules/Employee/Application/CreateEmployeeActionTest.php` — mocks `IdentityUserReadContract`; asserts `EmployeeCreated` dispatched
- [ ] T024 [US1] Feature test `tests/Feature/Modules/Employee/EmployeeAuditTest.php` — `RecordsActivity` on employee create
- [ ] T025 [P] [US1] Feature test `tests/Feature/Modules/Employee/DuplicateIdentityIdTest.php` — second employee with same `identity_id` fails unique constraint
- [ ] T026 [US1] Bind `EmployeeRepositoryContract` in `app/Modules/Employee/Infrastructure/Providers/EmployeeServiceProvider.php`

**Checkpoint**: US1 acceptance scenarios 1–4 pass; quickstart Scenarios 1–4 pass; SC-001, SC-003 satisfied.

---

## Phase 4: User Story 2 — Department & Organizational Structure (Priority: P2)

**Goal**: Department aggregate CRUD and employee department assignment.

**Independent Test**: Create department → assign to employee → query employee with `departmentId`; inactive department blocks new assignment.

- [ ] T027 [US2] Create `Department` entity in `app/Modules/Employee/Domain/Entities/Department.php` per `data-model.md`
- [ ] T028 [US2] Create `DepartmentModel` in `app/Modules/Employee/Infrastructure/Persistence/Models/DepartmentModel.php` with `HasUuid`, `RecordsActivity`; add `belongsTo`/`hasMany` relations to `EmployeeModel` in `app/Modules/Employee/Infrastructure/Persistence/Models/EmployeeModel.php` (`department()`, `employees()`)
- [ ] T029 [US2] Create `DepartmentRepositoryContract` in `app/Modules/Employee/Application/Contracts/DepartmentRepositoryContract.php` and `DepartmentRepository` in `app/Modules/Employee/Infrastructure/Repositories/DepartmentRepository.php`
- [ ] T030 [US2] Implement `CreateDepartmentAction` in `app/Modules/Employee/Application/Services/CreateDepartmentAction.php` and `DeactivateDepartmentAction` in `app/Modules/Employee/Application/Services/DeactivateDepartmentAction.php`
- [ ] T031 [US2] Implement `AssignDepartmentToEmployeeAction` in `app/Modules/Employee/Application/Services/AssignDepartmentToEmployeeAction.php` — reject inactive department (R-17)
- [ ] T032 [P] [US2] Create Artisan commands `app/Modules/Employee/Presentation/Console/CreateDepartmentCommand.php` and `AssignDepartmentCommand.php`
- [ ] T033 [P] [US2] Feature test `tests/Feature/Modules/Employee/DepartmentTest.php` — CRUD, assignment, inactive department rejection
- [ ] T034 [US2] Bind `DepartmentRepositoryContract` in `app/Modules/Employee/Infrastructure/Providers/EmployeeServiceProvider.php`

**Checkpoint**: US2 acceptance scenarios pass; quickstart Scenario 5 pass.

---

## Phase 5: User Story 3 — Dependent Records (Priority: P2)

**Goal**: Dependent entity owned by Employee (CD-009); no `request_id` in Wave 1A.

**Independent Test**: Add dependent to employee → list → update; reject orphan dependent without employee.

- [ ] T035 [US3] Create migration `database/migrations/modules/employee/*_create_employee_dependents_table.php` per `data-model.md` (`employee_id` FK → `employee_employees`; no `request_id`)
- [ ] T036 [US3] Create `Dependent` entity in `app/Modules/Employee/Domain/Entities/Dependent.php` and `DependentModel` in `app/Modules/Employee/Infrastructure/Persistence/Models/DependentModel.php` with `belongsTo(EmployeeModel::class)` relation; add `dependents()` on `EmployeeModel`
- [ ] T037 [US3] Create `DependentRepositoryContract` in `app/Modules/Employee/Application/Contracts/DependentRepositoryContract.php` and `DependentRepository` in `app/Modules/Employee/Infrastructure/Repositories/DependentRepository.php`
- [ ] T038 [US3] Implement `AddDependentAction` in `app/Modules/Employee/Application/Services/AddDependentAction.php` and `UpdateDependentAction` in `app/Modules/Employee/Application/Services/UpdateDependentAction.php` — use `App\Support\ValueObjects\Identity\NationalCode` when code provided
- [ ] T039 [P] [US3] Feature test `tests/Feature/Modules/Employee/DependentTest.php` — add, list, update; `NationalCode` validation; employee ownership
- [ ] T040 [US3] Bind `DependentRepositoryContract` in `app/Modules/Employee/Infrastructure/Providers/EmployeeServiceProvider.php`

**Checkpoint**: US3 acceptance scenarios pass; quickstart Scenario 6 pass.

---

## Phase 6: User Story 4 — Eligibility Computation (Priority: P3)

**Goal**: `EmployeeEligibilityContract` supplier API with BR-01 partial rules and stub ports (CD-013).

**Independent Test**: Active employee → eligible; inactive → `employee_inactive`; mock allocation port `true` → `active_allocation_exists`.

### Contracts & DTOs

- [ ] T041 [US4] Create `EligibilityReasonCode` enum in `app/Modules/Employee/Domain/Enums/EligibilityReasonCode.php` per `contracts/employee-eligibility-service.md`
- [ ] T042 [US4] Create `EligibilityResultDTO` in `app/Modules/Employee/Application/DTOs/EligibilityResultDTO.php`
- [ ] T043 [US4] Create port interfaces `ActiveAllocationReadPort` and `PendingRequestReadPort` in `app/Modules/Employee/Application/Contracts/Ports/` per `contracts/internal-read-ports.md`

### Stub adapters & domain calculator

- [ ] T044 [US4] Create `NullActiveAllocationReadAdapter` in `app/Modules/Employee/Infrastructure/Adapters/NullActiveAllocationReadAdapter.php` and `NullPendingRequestReadAdapter` in `app/Modules/Employee/Infrastructure/Adapters/NullPendingRequestReadAdapter.php` — always return `false`
- [ ] T045 [US4] Implement `EligibilityCalculator` in `app/Modules/Employee/Domain/Services/EligibilityCalculator.php` — evaluate employee active + port checks; return `EligibilityResultDTO` with stable reason codes

### Supplier service

- [ ] T046 [US4] Create `EmployeeEligibilityContract` in `app/Modules/Employee/Application/Contracts/EmployeeEligibilityContract.php` per `contracts/employee-eligibility-service.md`
- [ ] T047 [US4] Implement `EmployeeEligibilityService` in `app/Modules/Employee/Application/Services/EmployeeEligibilityService.php` and bind contract + stub ports in `app/Modules/Employee/Infrastructure/Providers/EmployeeServiceProvider.php`

### Tests

- [ ] T048 [US4] Feature test `tests/Feature/Modules/Employee/EmployeeEligibilityContractTest.php` — active eligible; inactive ineligible; bind mock `ActiveAllocationReadPort` returning `true` → `active_allocation_exists`; bind mock `PendingRequestReadPort` → `pending_request_exists`

**Checkpoint**: US4 acceptance scenarios pass; quickstart Scenario 7 pass; SC-004 satisfied.

---

## Phase 7: Supplier Read Contract (downstream prep)

**Goal**: Optional `EmployeeReadContract` — mirror Identity supplier pattern for spec05.

**Independent Test**: `employeeExists`, `isEmployeeActive`, `findEmployeeSummary` — no PII beyond summary DTO.

- [ ] T049 Create `EmployeeSummaryDTO` in `app/Modules/Employee/Application/DTOs/EmployeeSummaryDTO.php` per `contracts/employee-read-service.md`
- [ ] T050 Create `EmployeeReadContract` in `app/Modules/Employee/Application/Contracts/EmployeeReadContract.php`
- [ ] T051 Implement `EmployeeReadService` in `app/Modules/Employee/Application/Services/EmployeeReadService.php` delegating to `EmployeeRepository` inside module only
- [ ] T052 [P] Feature test `tests/Feature/Modules/Employee/EmployeeReadContractTest.php` — exists, active, summary, unknown id; bind in `EmployeeServiceProvider.php`

**Checkpoint**: quickstart Scenario 9 pass.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Architecture boundary BT-05, PHPStan, documentation, quickstart validation. **Livewire HR admin deferred** (plan §Phase F).

- [ ] T053 [P] Architecture test `tests/Architecture/EmployeeSupplierBoundaryTest.php` — **BT-05**: Employee module must not import `App\Modules\Identity\Infrastructure\*` or query `identity_users` via Eloquent
- [ ] T054 Update `app/Modules/Employee/README.md` with module boundaries, contract usage, stub port strategy, and CD-012/CD-013 traceability
- [ ] T055 Run `docker compose exec laravel.test vendor/bin/pint` and fix formatting across Employee module
- [ ] T056 Run `docker compose exec laravel.test vendor/bin/phpstan analyse --memory-limit=1G app/Modules/Employee` — zero errors (SC-005)
- [ ] T057 Execute `quickstart.md` Scenarios 1–9 via tests or documented commands and record pass/fail
- [ ] T058 [P] Scope audit — verify no tasks introduced Request/Allocation modules, Identity Infrastructure imports, or FK `identity_id` → `identity_users`

**Deferred (no tasks):**

- ~~Livewire HR admin~~ — plan §Phase F
- ~~Real `ActiveAllocationReadPort` / `PendingRequestReadPort` adapters~~ — spec07 / spec05
- ~~`request_id` on dependents~~ — spec05

**Checkpoint**: Definition of Done — PHPStan L8 Employee paths, Pint, BT-01–BT-05 green, quickstart validated.

---

## Parallel Execution Examples

### Within Phase 2 (after T010 migration exists)

```bash
T006 DepartmentId VO
T007 DependentId VO
T008 Enums
T009 Domain exceptions
```

### Within Phase 3 (US1)

```bash
T017 CreateEmployeeCommand
T021 OA-03-02 test
T022 Employee domain unit test
T023 CreateEmployeeAction unit test
T025 DuplicateIdentityIdTest
```

### Across stories (after Phase 3 MVP)

```text
US2 (Phase 4) and US3 (Phase 5) can proceed in parallel once US1 complete.
US4 (Phase 6) needs Employee repository from US1 only.
Phase 7 read contract can parallel US4 tests after T047.
```

---

## Implementation Strategy

1. **MVP first**: Phases 1–3 (T001–T026) — Employee + `identity_id` boundary; unblocks spec05 Request `employee_id` references.
2. **Incremental**: US2 Department → US3 Dependent → US4 Eligibility → read contract → polish.
3. **Defer**: Livewire admin, real allocation/request port adapters, `AuditService` central integration.
4. **Validate early**: Run BT-01–BT-03 after T026 before declaring MVP complete; BT-05 after T053.

---

## Format Validation

- [x] All tasks use `- [ ] T###` checkbox format
- [x] Story labels `[US1]`–`[US4]` on user-story phase tasks only
- [x] `[P]` only where parallel-safe
- [x] Every task includes concrete file path(s)
- [x] Migrations: T010, T011, T035 (Department, Employee, Dependent)
- [x] Models + relations: T013, T028, T036
- [x] Service layer CRUD + eligibility stubs: T016, T030–T031, T038, T044–T047
- [x] Boundary tests BT-01–BT-05: T018–T021, T053
- [x] `EmployeeEligibilityContract`: T046–T048
- [x] PHPStan: T056
