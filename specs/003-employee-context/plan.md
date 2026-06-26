# Implementation Plan: Employee Context (spec03)

**Branch**: `003-employee-context` | **Date**: 2026-06-26 | **Spec**: [spec.md](./spec.md)

**Input**: Wave 1A — Employee bounded context: profiles with immutable `identity_id` (CD-012), departments, dependents (CD-009), eligibility computation supplier API (CD-013).

**Governance**: Upstream Identity contract frozen in spec02; normative boundary [`../002-identity-access/contracts/identity-employee-boundary.md`](../002-identity-access/contracts/identity-employee-boundary.md).

---

## Summary

Implement the **Employee** module as organizational upstream supplier for Request (spec05). Employees receive **UUID v7** PKs via spec01 kernel. `identity_id` attaches to Identity **once** at create using **`IdentityUserReadContract::userExists`** only (OA-03-02). Departments and dependents are owned entirely within Employee persistence with **intra-module FKs only**.

Wave 1A delivers: Employee/Department/Dependent CRUD via Application Actions, boundary tests BT-01–BT-03, architecture test BT-05, **`EmployeeEligibilityContract`** with BR-01 partial rules (allocation/request via stub ports), optional **`EmployeeReadContract`**, audit via `RecordsActivity`. **No** Request, Allocation, or login UI.

---

## Technical Context

| Dimension | Value |
|-----------|-------|
| **Language/Version** | PHP 8.4; Laravel 13 |
| **Primary Dependencies** | spec01 `app/Support/` (`BaseModel`, `HasUuid`, `NationalCode`, `RecordsActivity`); spec02 `IdentityUserReadContract` |
| **UUID strategy** | UUID v7 via `HasUuid` |
| **Storage** | PostgreSQL 17 — migrations under `database/migrations/modules/employee/` |
| **Testing** | Pest PHP 4; unit (Domain/Application), feature (actions + contracts), architecture (BT-05) |
| **Target Platform** | Laravel Sail |
| **Performance Goals** | HR admin scale; eligibility sync compute |
| **Constraints** | No cross-module Eloquent; no FK to Identity; Persian RTL admin deferred |
| **Scale/Scope** | 3 aggregates/entities; 2 supplier contracts; 2 internal stub ports; 1 optional domain event |

---

## Constitution Check

*GATE: Must pass before implementation. Re-checked after Phase 1 design.*

| Principle | Compliance | Notes |
|-----------|------------|-------|
| AP-01 Technology Stack | ✅ PASS | Laravel 13, PostgreSQL 17, Pest |
| AP-01 Presentation | ⚠️ DEFERRED | Livewire HR admin post-MVP tail (R-15) |
| AP-02 Modular Monolith | ✅ PASS | `app/Modules/Employee/` four layers |
| AP-03 Clean Architecture | ✅ PASS | Domain pure PHP; Eloquent in Infrastructure |
| AP-04 Shared DB / Module Ownership | ✅ PASS | `employee_*` tables owned by Employee; `identity_id` UUID ref without FK |
| AP-05 State Machines | ⬜ N/A | Simple enums (`EmployeeStatus`, `DepartmentStatus`) — no spatie states Wave 1A |
| AP-06 Audit Everything | ⚠️ CONDITIONAL | `RecordsActivity` on models; central `AuditService` deferred |
| AP-07 Background Processing | ⬜ N/A | Synchronous events Wave 1A |
| AP-08 Configuration | ⬜ N/A | No settings-driven rules in Wave 1A |
| 10.7 Localization | ⚠️ DEFERRED | Persian RTL admin UI deferred |
| 10.4 DoD | ✅ PASS | PHPStan L8, Pint, Pest — planned in tasks |
| CD-009 | ✅ PASS | Dependent ∈ Employee; no `request_id` Wave 1A |
| CD-012 | ✅ PASS | `identity_id` immutable; Identity read contract only |
| CD-013 | ✅ PASS | Eligibility computation in Employee; enforcement spec05 |
| BR-01 | ⚠️ PARTIAL | Employee-active live; allocation/request via stubs (R-12) |

**Post-design re-check**: No constitution violations requiring ADR. Conditional/deferred items documented; do not block MVP (US1 + boundary tests).

---

## FR-004 → Identity Consumer Contract (explicit mapping)

Employee module is **consumer** of frozen spec02 supplier:

| spec03 need | Identity contract method | When called |
|-------------|-------------------------|-------------|
| Validate `identity_id` at create | `userExists(UserId $id): bool` | `CreateEmployeeAction` — **required** |
| Optional active check | `isUserActive(UserId $id): bool` | **Not called** Wave 1A (OA-03-02) |
| Display Identity label (admin later) | `findUserSummary(UserId $id): ?UserSummaryDTO` | Optional presentation |

**Forbidden:** `use App\Modules\Identity\Infrastructure\*` in Employee module (BT-05).

---

## FR-007 → Eligibility Supplier Contract (CD-013)

| spec.md FR-007 | Contract | Layer |
|----------------|----------|-------|
| Compute request eligibility | `EmployeeEligibilityContract::computeRequestEligibility` | Application |
| BR-01 employee active | `EligibilityCalculator` + `EmployeeRepository` | Domain + Infra |
| BR-01 allocation / pending request | `ActiveAllocationReadPort`, `PendingRequestReadPort` | Application ports → stub adapters |

See [`contracts/employee-eligibility-service.md`](./contracts/employee-eligibility-service.md).

---

## 1. UUID v7 from Kernel

Same rules as spec02 plan §1:

- `EmployeeModel`, `DepartmentModel`, `DependentModel` extend `BaseModel` + `HasUuid`
- Domain VOs: `EmployeeId`, `DepartmentId`, `DependentId`
- **Prohibited:** `Str::uuid()` (v4)

---

## 2. Module Structure (Employee)

```text
app/Modules/Employee/
├── Domain/
│   ├── Entities/Employee.php
│   ├── Entities/Department.php
│   ├── Entities/Dependent.php
│   ├── ValueObjects/EmployeeId.php, DepartmentId.php, DependentId.php
│   ├── Enums/EmployeeStatus.php, DepartmentStatus.php, DependentRelationship.php
│   ├── Services/EligibilityCalculator.php
│   ├── Events/EmployeeCreated.php
│   └── Exceptions/IdentityIdImmutableException.php, EmployeeNotFoundException.php, ...
├── Application/
│   ├── Contracts/
│   │   ├── EmployeeEligibilityContract.php
│   │   ├── EmployeeReadContract.php
│   │   └── Ports/ActiveAllocationReadPort.php, PendingRequestReadPort.php
│   ├── DTOs/EligibilityResultDTO.php, EmployeeSummaryDTO.php
│   ├── Services/
│   │   ├── CreateEmployeeAction.php
│   │   ├── AssignDepartmentAction.php
│   │   ├── AddDependentAction.php
│   │   ├── EmployeeEligibilityService.php
│   │   └── EmployeeReadService.php
│   └── ...
├── Infrastructure/
│   ├── Persistence/Models/EmployeeModel.php, DepartmentModel, DependentModel.php
│   ├── Repositories/EmployeeRepository.php, DepartmentRepository.php, DependentRepository.php
│   ├── Adapters/NullActiveAllocationReadAdapter.php, NullPendingRequestReadAdapter.php
│   └── Providers/EmployeeServiceProvider.php
└── Presentation/
    └── Console/Commands/...              # employee:create, etc. (dev/test)

database/migrations/modules/employee/
├── *_create_employee_departments_table.php   # departments first (FK order)
├── *_create_employee_employees_table.php
└── *_create_employee_dependents_table.php

tests/
├── Unit/Modules/Employee/
├── Feature/Modules/Employee/
│   ├── EmployeeIdentityBoundaryTest.php    # BT-01, BT-02, BT-03
│   └── EmployeeEligibilityContractTest.php
└── Architecture/                           # BT-05 — no Identity Infra imports
```

---

## 3. `identity_id` Lifecycle (CD-012)

| Step | Rule |
|------|------|
| Create | `CreateEmployeeAction` calls `IdentityUserReadContract::userExists`; throws if false (BT-03) |
| Persist | `identity_id` written once with employee row |
| Update | Repository/model **blocks** `identity_id` column changes (BT-02) |
| Unique | DB unique index on `identity_id` |
| Identity deactivated later | No Employee mutation (BT-04 deferred) |

---

## 4. Implementation Phases

### Phase A — Employee + Identity boundary (P1 / US1)

- Migrations: departments (if needed for FK order), employees
- Domain `Employee` + `EmployeeModel` + repository
- `CreateEmployeeAction` with Identity contract injection
- Unit tests: immutability, unique identity_id
- Feature: BT-01, BT-02, BT-03

### Phase B — Department (P2 / US2)

- `Department` aggregate + CRUD actions
- Assign employee to department; block inactive department assignment
- Feature tests: department CRUD, assignment query

### Phase C — Dependent (P2 / US3)

- `Dependent` entity + `AddDependentAction` / update / list
- FK `employee_id` within module
- Feature tests: dependent lifecycle

### Phase D — Eligibility supplier (P3 / US4)

- `EligibilityCalculator` + `EmployeeEligibilityService`
- Stub port adapters (always false)
- `EmployeeEligibilityContract` binding
- Unit tests with mock ports; feature test active vs inactive employee

### Phase E — Supplier read + polish (P3)

- `EmployeeReadContract` + `EmployeeReadService`
- `EmployeeCreated` event + `RecordsActivity`
- Artisan commands for dev quickstart
- Architecture test BT-05
- PHPStan `app/Modules/Employee` — 0 errors

### Phase F — Admin UI (deferred)

- Livewire HR screens — Persian RTL
- Mirror spec02 deferred Livewire pattern

---

## 5. MVP Boundary (pre-tasks lock)

**MVP = US1 + boundary compliance + minimal eligibility skeleton**

| In MVP | Out of MVP |
|--------|------------|
| T00x migrations Employee + identity_id | Livewire admin (Phase F) |
| CreateEmployeeAction + BT-01–03 | Full BR-01 (date rules → spec05) |
| BT-05 architecture test | Real Allocation/Request port adapters |
| EligibilityContract + inactive rule | Department tree UI |
| RecordsActivity | Central AuditService integration |

---

## 6. Dependencies

| Dependency | Status |
|------------|--------|
| spec01 Foundation | Approved — kernel, module scaffold, arch tests |
| spec02 Identity | **Frozen** — `IdentityUserReadContract` live |
| spec05 Request | Not required; stub `PendingRequestReadPort` |
| spec07 Allocation | Not required; stub `ActiveAllocationReadPort` |

---

## 7. Project Structure (documentation)

```text
specs/003-employee-context/
├── spec.md
├── plan.md                    # this file
├── research.md
├── data-model.md
├── events.md
├── quickstart.md
├── contracts/
│   ├── identity-employee-boundary.md   # pointer → spec02
│   ├── employee-eligibility-service.md
│   ├── employee-read-service.md
│   └── internal-read-ports.md
└── tasks.md                   # /speckit-tasks (next)
```

---

## Complexity Tracking

> No constitution violations requiring justification.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| — | — | — |

---

## Generated Artifacts (this plan run)

| Artifact | Path | Status |
|----------|------|--------|
| Implementation plan | `specs/003-employee-context/plan.md` | ✅ Complete |
| Research | `specs/003-employee-context/research.md` | ✅ Complete |
| Data model | `specs/003-employee-context/data-model.md` | ✅ Complete |
| Eligibility contract | `contracts/employee-eligibility-service.md` | ✅ Complete |
| Read service contract | `contracts/employee-read-service.md` | ✅ Complete |
| Internal stub ports | `contracts/internal-read-ports.md` | ✅ Complete |
| Events | `events.md` | ✅ Complete |
| Quickstart | `quickstart.md` | ✅ Complete |
| Boundary pointer | `contracts/identity-employee-boundary.md` | ✅ Unchanged (spec02 authoritative) |

---

## Next Step

Run **`/speckit-tasks`** to generate dependency-ordered `tasks.md` from this plan.
