# Employee Context UI — Repository Inspection

## Feature

| Field | Value |
|---|---|
| **Canonical feature slug** | `employee-context-ui` |
| **Feature title** | Employee Context UI |
| **Domain** | Employee |
| **Source specification** | `specs/003-employee-context` |
| **Product authorization** | `docs/product/product-authorization-next-ui-feature.md` — **`AUTHORIZED`** for UI governance intake; **`repo-inspection` explicitly permitted** |

## Inspection date

2026-07-10

## Inspection scope

Repository-observable facts only for Employee Context UI intake. No product re-selection, no inferred UI requirements from deferred Phase F / R-15 language, no feature-analysis conclusions beyond readiness to proceed.

---

## 1. Inspection Summary

**Employee Context UI does not exist** in the repository. There are no Employee web routes, Livewire components, Blade views, or layout navigation entries for HR/employee administration.

**Employee backend mutation capabilities exist** for create-employee, create-department, deactivate-department, and assign-department, exposed today only through Artisan console commands and Application actions. Persistence covers `employee_employees` and `employee_departments`.

**Dependent records (US3 / CD-009) are not implemented** beyond domain value objects/enums. **`EmployeeReadContract` / list-query surfaces are absent.** Eligibility computation exists as an Application contract but is a supplier API for Request, not an HR admin UI surface by itself.

Product authorization is present and permits this gate. Repository baseline is **sufficient to proceed to feature-analysis**, with explicit gaps that feature-analysis must bound to delivered Application surfaces (or flag as backend prerequisites).

---

## 2. Inputs Reviewed

### Required governance / product / spec inputs

| Path | Role |
|---|---|
| `.specify/governance/_meta/authority-model.md` | Authority vocabulary (authorization ≠ inference) |
| `docs/ui/review/governance-next-candidate-triage.md` | Prior queue freeze context |
| `docs/ui/review/backlog-authority-discovery.md` | Backlog authority context |
| `docs/product/product-authorization-next-ui-feature.md` | **AUTHORIZED** intake for `employee-context-ui` |
| `docs/product/next-ui-feature-authorization-discovery.md` | Prior discovery inventory (advisory) |
| `specs/003-employee-context/spec.md` | Spec03 requirements / exclusions |
| `specs/003-employee-context/plan.md` | Phase F Livewire deferred; MVP boundary |
| `specs/003-employee-context/tasks.md` | Task status; US3+ hold; Livewire deferred |

### Code / tests inspected

| Path | Role |
|---|---|
| `app/Modules/Employee/**` | Module Application / Domain / Infrastructure / Presentation |
| `app/Modules/Employee/Presentation/Livewire/.gitkeep` | Empty Livewire placeholder |
| `app/Modules/Employee/Presentation/Views/.gitkeep` | Empty views placeholder |
| `app/Modules/Employee/Presentation/Routes/.gitkeep` | Empty routes placeholder |
| `app/Modules/Employee/Presentation/Console/*.php` | Artisan presentation surface |
| `app/Application/Mutation/Registry/MutationCapabilityCatalog.php` | Employee mutation capability keys |
| `app/Modules/Identity/Application/Contracts/IdentityUserReadContract.php` | Upstream identity read for create |
| `resources/views/components/layouts/app.blade.php` | Shared layout nav (no Employee link) |
| `routes/web.php` | Web route registration (no employee routes found) |
| `bootstrap/providers.php` | Employee providers registered |
| `database/migrations/modules/employee/*` | Employee/department tables |
| `tests/Feature/Modules/Employee/*` | Feature tests |
| `tests/Unit/Modules/Employee/*` | Unit tests |
| `tests/Architecture/EmployeeSupplierBoundaryTest.php` | Boundary architecture test |

---

## 3. Existing Employee Backend Capabilities

### 3.1 Persistence

| Item | Evidence |
|---|---|
| Tables present | `employee_departments` (`2026_06_26_000001_...`), `employee_employees` (`2026_06_26_000002_...`) |
| Dependents table | **Absent** — no `employee_dependents` migration in `database/migrations/modules/employee/` |
| Employee fields (entity) | `identityId`, `employeeCode`, `firstName`, `lastName`, `nationalCode`, `departmentId`, `hireDate`, `baseLotteryScore`, `status` |
| Department entity | Create/deactivate; code uniqueness; optional manager/parent |

### 3.2 Application mutation actions (delivered)

| Action | Path | Mutation capability key |
|---|---|---|
| `CreateEmployeeAction` | `Application/Services/CreateEmployeeAction.php` | `employee.create` |
| `CreateDepartmentAction` | `Application/Services/CreateDepartmentAction.php` | `employee.department.create` |
| `DeactivateDepartmentAction` | `Application/Services/DeactivateDepartmentAction.php` | `employee.department.deactivate` |
| `AssignDepartmentToEmployeeAction` | `Application/Services/AssignDepartmentToEmployeeAction.php` | `employee.department.assign` |

### 3.3 Application read / supplier surfaces

| Surface | Evidence |
|---|---|
| `EmployeeRepositoryContract` | `save`, `findById`, `findByIdentityId`, `findEmployeeIdByIdentityUserId`, `existsByIdentityId` — **no list/search/paginate** |
| `DepartmentRepositoryContract` | `save`, `findById`, `existsByCode` — **no list/search/paginate** |
| `EmployeeEligibilityContract` | Implemented (`EmployeeEligibilityService`) — `computeRequestEligibility` |
| `EmployeeReadContract` / `EmployeeSummaryDTO` | **Absent** in `app/Modules/Employee` (README still lists as later-wave supplier) |
| Dependent CRUD actions / repository | **Absent** (only `DependentId` VO + `DependentRelationship` enum) |

### 3.4 Domain behaviors present but not exposed as Application UI actions

| Behavior | Evidence | Application action |
|---|---|---|
| `Employee::deactivate()` / `activate()` | Domain entity methods | **No** dedicated Application action found |
| Profile field update after create | Not evidenced as Application action | **Unknown / absent** |

### 3.5 Classification

Delivered Employee Application surface is **mutation-oriented + point lookup**, with eligibility as a **cross-module supplier**. It is **not** a presentation-ready HR list/admin read model.

---

## 4. Existing Routes

| Surface | Evidence |
|---|---|
| Employee web routes under `app/Modules/Employee/Presentation/Routes/` | **None** — directory contains `.gitkeep` only |
| `routes/web.php` / route name `employees.*` | **No matches** for employee/department route registration |
| HTTP Controllers | `Presentation/Controllers/.gitkeep` only |

**Conclusion:** No Employee HTTP/Livewire routes exist.

---

## 5. Existing Livewire / Blade / UI Surfaces

| Surface | Evidence |
|---|---|
| `Presentation/Livewire/` | `.gitkeep` only — **no Livewire classes** |
| `Presentation/Views/` | `.gitkeep` only — **no Blade views** |
| `resources/views` Employee references | **No matches** |
| Shared layout nav (`app.blade.php`) | Links: **درخواست‌ها** (`requests.*`), **اعلان‌ها** (`notifications.*`) — **no Employee/HR nav item** |
| Existing UI modules (comparison only) | Request + Notification Livewire pages exist; Employee does not |

### Current presentation that does exist

| Surface | Evidence |
|---|---|
| Artisan `employee:create` | `Presentation/Console/CreateEmployeeCommand.php` |
| Artisan department create | `Presentation/Console/CreateDepartmentCommand.php` |
| Artisan department assign | `Presentation/Console/AssignDepartmentCommand.php` |
| Provider registration | `EmployeePresentationServiceProvider` registers console commands only when `runningInConsole()` |

**Conclusion:** Employee presentation today is **console-only**. Web UI gap is total for this feature slug.

---

## 6. Existing Application Contracts / Actions (summary)

### Mutations (usable by a future thin UI if authorized later)

1. Create employee (requires Identity UUID + profile fields; validates via `IdentityUserReadContract::userExists`)
2. Create department
3. Deactivate department
4. Assign department to employee (rejects inactive department)

### Reads available today

1. Point load employee by id / identity id
2. Point load department by id
3. Eligibility computation by employee id (supplier)

### Reads missing for typical HR admin UI

1. Employee list / filter / search / pagination
2. Department list / filter / search / pagination
3. `EmployeeReadContract` summary projection for UI
4. Dependent list/create/update
5. Identity user picker list (Identity admin UI also deferred; `findUserSummary` exists for single id)

---

## 7. Existing Authorization Boundaries

| Mechanism | Evidence |
|---|---|
| Mutation policy | `MutationPolicyEnforcementPoint` + `MutationCapabilityCatalog` keys for employee create / department create / deactivate / assign |
| Employee mutation gate | `EmployeeMutationAuthorizationGate` — requires mutation principal id; requires `IdentityUserReadContract::isUserActive` |
| Role-based HR permission checks in Employee module | **Not evidenced** in `EmployeeMutationAuthorizationGate` (active identity actor only) |
| Web middleware pattern for other UI | Request/Notification use authenticated web groups (existing precedent) — **Employee has no web routes to attach** |
| Login/session UX (OA-02-01) | Spec-excluded; product authorization excludes inventing login UI. Layout already has logout for authenticated app shell. |

**Unknown:** Exact role/permission matrix intended for HR admin UI (e.g. which Spatie roles may access Employee screens) — not defined in Employee presentation code because presentation is absent.

---

## 8. Existing Tests

| Path | Coverage evidenced |
|---|---|
| `tests/Feature/Modules/Employee/EmployeeIdentityBoundaryTest.php` | Identity attachment boundary (BT-style) |
| `tests/Feature/Modules/Employee/DepartmentTest.php` | Department create, assign, inactive assign rejection |
| `tests/Feature/Modules/Employee/DuplicateIdentityIdTest.php` | Duplicate identity_id |
| `tests/Feature/Modules/Employee/EmployeeAuditTest.php` | Audit/activity expectations |
| `tests/Unit/Modules/Employee/Application/CreateEmployeeActionTest.php` | Create action unit |
| `tests/Unit/Modules/Employee/Domain/EmployeeTest.php` | Domain entity |
| `tests/Architecture/EmployeeSupplierBoundaryTest.php` | Supplier/boundary architecture |
| `tests/Feature/Modules/Identity/IdentityEmployeeMutationAuthorizationTest.php` | Cross-module mutation auth interaction |
| UI flow tests for Employee Livewire | **Absent** |

---

## 9. Missing Capabilities (evidence gaps)

| Gap | Evidence status |
|---|---|
| Any Employee Livewire/Blade/web route | **Missing** |
| Layout discoverability for Employee/HR | **Missing** |
| Employee/department list read APIs | **Missing** |
| `EmployeeReadContract` | **Missing** |
| Dependent persistence + Application CRUD | **Missing** (US3 hold in tasks; no migration) |
| Update-employee / deactivate-employee Application actions | **Missing** (domain deactivate exists; no Application action found) |
| Dedicated HR role/permission presentation policy | **Unknown** / not implemented in Employee UI layer |
| UI tests | **Missing** |

---

## 10. Dependency Blockers

| Dependency | Status for UI intake | Notes |
|---|---|---|
| Product authorization | **Satisfied** | `AUTHORIZED`; repo-inspection permitted |
| Spec03 Wave 1A + US2 backend | **Present** | Create employee + department flows exist |
| Identity read contract | **Present** | `userExists`, `isUserActive`, `findUserSummary` |
| Identity auth UX (OA-02-01) | **Excluded** by product auth | Must not invent login under this feature |
| Identity Livewire admin | **Excluded** | Separate feature if ever authorized |
| US3 dependents backend | **Not delivered** | Blocker **only if** UI scope includes dependents |
| List/read model for HR browse | **Not delivered** | Blocker **only if** UI scope requires list screens without new backend work |
| Cross-module Request/Allocation UI | **Excluded** | Out of product authorization scope |

**Intake blocker for starting feature-analysis:** none.  
**Potential downstream blockers:** list/read gaps and dependents absence — must be classified in feature-analysis, not converted into requirements here.

---

## 11. Candidate UI Surfaces Supported by Evidence

Surfaces that could **later** be considered (not requirements; evidence-backed only):

| Candidate surface | Supporting evidence | Missing for full surface |
|---|---|---|
| Create-employee form (HR) | `CreateEmployeeAction` + console command | Web route/Livewire; identity UUID capture UX without Identity admin list |
| Create-department form | `CreateDepartmentAction` | Web route/Livewire; department list for parent/manager pickers |
| Deactivate-department control | `DeactivateDepartmentAction` | Web route/Livewire; department lookup/list |
| Assign-department-to-employee control | `AssignDepartmentToEmployeeAction` | Web route/Livewire; employee + department selectors/lists |
| Layout nav link to Employee HR area | Shared layout pattern from Request/Notification | Route + page must exist first |
| Employee/department index/list pages | **Not supported** by current repository contracts | Needs new read/list Application capability |
| Dependent management screens | **Not supported** | Needs US3 backend delivery |
| Eligibility admin screen | `EmployeeEligibilityContract` exists | Unclear product need; not an HR admin CRUD surface by evidence alone |

Deferred Phase F / R-15 language in plan/tasks is **historical deferral evidence only** — not converted into requirements by this inspection.

---

## 12. Spec / Task Status vs Repository Reality

| Spec/tasks claim | Repository reality |
|---|---|
| Livewire HR admin deferred (Phase F) | Confirmed absent |
| US2 department complete | Confirmed present (actions + tests) |
| US3+ on hold | Dependents absent — confirmed |
| `EmployeeReadContract` later | Absent — confirmed |
| Eligibility tasks unchecked in `tasks.md` | **Code present** (`EmployeeEligibilityContract` / service) — repository reality overrides stale checkbox text for inspection |

---

## 13. Repository Readiness Status

**`REPO_INSPECTION_COMPLETE_READY_FOR_FEATURE_ANALYSIS`**

Rationale:

1. Product authorization is explicit and permits this gate.
2. Backend mutation baseline for employee + department is observable and test-covered.
3. UI absence is clearly evidenced (not ambiguous).
4. Gaps (list reads, dependents, update/deactivate employee actions) are identifiable for feature-analysis scoping without inventing requirements here.

---

## 14. Confirmed Next Governance Gate

**`feature-analysis`**

Expected next artifact (not created in this task):

`docs/ui/analysis/employee/employee-context-ui.feature-analysis.md`

---

## 15. Blocking Findings

### Blocking for this gate (repo-inspection)

**None.**

### Non-blocking / downstream risks (for feature-analysis)

| Finding | Classification |
|---|---|
| No Employee web UI exists | Expected gap; defines greenfield presentation work |
| No list/read Application APIs for HR browse | May force MVF to mutation-only forms with id entry, or require separate backend authorization |
| Dependents not implemented | Must remain out of MVF unless backend work is separately authorized |
| Auth gate is “active identity principal,” not HR-role-specific | Role/capability presentation policy unknown — resolve in later gates without inventing here |
| Identity user picker list absent | Create-employee may need UUID input or limited Identity read usage — unknown until analysis |

---

## 16. Explicit Non-Actions

This inspection did **not**:

- Implement code
- Create feature-contract or implementation-lock
- Select MVF scope beyond evidence mapping
- Convert Phase F / R-15 deferred items into requirements
- Reopen closed notification/request UI features

---

*Repository evidence inspection only. Next gate: feature-analysis.*
