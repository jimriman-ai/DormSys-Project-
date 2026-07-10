# Employee Context UI — Implementation Lock

## Feature Identity

| Field | Value |
|---|---|
| **Feature id** | `employee-context-ui` |
| **Feature title** | Employee Context UI |
| **Module** | Employee |
| **Area** | employee |
| **Classification** | greenfield-presentation-mvf |
| **Gap type** | `UI_PRESENTATION_GAP` |
| **Version** | `0.1.0` |
| **Lock date** | 2026-07-10 |
| **Current gate** | `implementation-lock` |

---

## 1. Lock summary

| Field | Value |
|---|---|
| **Final lock status** | **`IMPLEMENTATION_LOCK_CREATED_READY_FOR_REVIEW`** |
| **Coding authorized?** | **No** |
| **Implementation authorized?** | **No** — not until lock-review approval |
| **Next gate** | `lock-review` |
| **Blocking gaps** | **None** |

This artifact freezes the exact implementation boundary for `employee-context-ui` after lock-review approval. It does **not** authorize Livewire, Blade, routes, layout edits, tests, or any Application/Domain/Infrastructure changes in this gate.

---

## 2. Governance chain verification

| Gate | Artifact | Status consumed |
|---|---|---|
| Product authorization | `docs/product/product-authorization-next-ui-feature.md` | `AUTHORIZED` |
| repo-inspection | `docs/ui/analysis/employee/employee-context-ui.repo-inspection.md` | Complete |
| feature-analysis | `docs/ui/analysis/employee/employee-context-ui.feature-analysis.md` | `READY_FOR_REVIEW_DECISION` |
| review-decision | `docs/ui/review/employee/employee-context-ui.review-decision.md` | `APPROVED_READY_FOR_CONTRACT` |
| feature-contract | `docs/ui/contracts/employee/employee-context-ui.feature-contract.yaml` | `FEATURE_CONTRACT_CREATED_READY_FOR_REVIEW` |
| contract-review | `docs/ui/review/employee/employee-context-ui.contract-review.md` | `CONTRACT_APPROVED_FOR_IMPLEMENTATION_LOCK` |
| **implementation-lock** | **This artifact** | **`IMPLEMENTATION_LOCK_CREATED_READY_FOR_REVIEW`** |
| lock-review | *(not created)* | Next gate |
| implementation | *(not authorized)* | After lock-review approval only |

**Governing UI contract:** `docs/architecture/ui/UI-ANTI-LEAK-CONTRACT.md`

**Contract-review bindings frozen into this lock:** CR-EC-001 (route), CR-EC-002 (flash+id only; no repository injection), CR-EC-003 (Livewire 3 + Blade), CR-EC-004 (validation boundary).

---

## 3. Approved implementation scope

### 3.1 Presentation MVF (only)

| Surface | Frozen boundary |
|---|---|
| **Employee Hub Page** | Single authenticated Persian RTL Livewire 3 + Blade hub |
| **Route** | `employees.hub` — `GET /employees` |
| **Shared layout nav** | Label **کارکنان**; discoverability only |
| **Nav order** | 1) درخواست‌ها → 2) اعلان‌ها → 3) کارکنان |

### 3.2 UI capabilities (exactly four)

| # | Capability | Application action | Mutation key |
|---|---|---|---|
| 1 | Create Employee | `App\Modules\Employee\Application\Services\CreateEmployeeAction` | `employee.create` |
| 2 | Create Department | `App\Modules\Employee\Application\Services\CreateDepartmentAction` | `employee.department.create` |
| 3 | Assign Department to Employee | `App\Modules\Employee\Application\Services\AssignDepartmentToEmployeeAction` | `employee.department.assign` |
| 4 | Deactivate Department | `App\Modules\Employee\Application\Services\DeactivateDepartmentAction` | `employee.department.deactivate` |

No additional Employee UI capability is included.

### 3.3 Stack freeze (CR-EC-003)

| Allowed | Forbidden |
|---|---|
| Existing Laravel UI architecture | SPA architecture |
| Livewire 3 | Vue / React |
| Blade | Inertia |
| | Any alternative frontend stack |

---

## 4. Route and navigation lock

### 4.1 Frozen route (CR-EC-001)

| Field | Value |
|---|---|
| **Route name** | `employees.hub` |
| **Method / path** | `GET /employees` |
| **Middleware** | Same authenticated web stack as `requests` / `notifications` (`auth:api` group in `routes/web.php`) |
| **Public mutation routes** | **None** — hub hosts all four forms |

**Route creation rule:** Allowed **only after lock-review approval**. Not allowed during this implementation-lock gate.

**Registration pattern (post lock-review):**

1. Create `app/Modules/Employee/Presentation/Routes/web.php` registering Livewire hub at `/` within the `employees` prefix group, named `employees.hub`.
2. Add `EmployeePresentationServiceProvider::employeeWebRoutePath()` returning that path.
3. In `routes/web.php` authenticated group only, add:

```php
Route::prefix('employees')
    ->group(EmployeePresentationServiceProvider::employeeWebRoutePath());
```

Do **not** rename to `employees.index`. Do **not** add per-mutation public routes.

### 4.2 Frozen navigation

| Field | Value |
|---|---|
| **File** | `resources/views/components/layouts/app.blade.php` |
| **Label** | **کارکنان** |
| **Href** | `route('employees.hub')` |
| **Placement** | Immediately after **اعلان‌ها** |
| **Order** | درخواست‌ها → اعلان‌ها → کارکنان |
| **Transport** | Plain `href` — **no** `wire:navigate` |
| **Active state** | `request()->routeIs('employees.*')` with same class ternary as requests/notifications (`font-semibold text-sky-700` vs `text-slate-600 hover:text-slate-900`) |
| **Visibility** | All authenticated users rendering the shared layout — no `@can` / `@role` / Gate / capability-flag wrap (RD-EC-005) |
| **Scope** | Nav item only — no other layout behavior changes (badge composer, logout, brand link unchanged) |

---

## 5. Livewire / Blade implementation boundary

### 5.1 Pinned component and view names

| Role | Frozen path |
|---|---|
| Livewire hub class | `app/Modules/Employee/Presentation/Livewire/EmployeeHubPage.php` |
| Blade view | `resources/views/livewire/employee/employee-hub-page.blade.php` |
| Layout attribute | `#[Layout('components.layouts.app')]` |
| View name | `livewire.employee.employee-hub-page` |

**Note:** Repository convention places Livewire Blade under `resources/views/livewire/{module}/` (Request/Notification). Do **not** invent a parallel SPA. `app/Modules/Employee/Presentation/Views/**` remains unused unless lock-review later authorizes a documented deviation; default is the `resources/views/livewire/employee/` path above.

### 5.2 Allowed Livewire responsibilities

- Receive user input (`wire:model` form state)
- Primitive / required-field / UUID / date / integer shape checks
- Empty / null normalization for optional fields (transport hygiene)
- Invoke exactly one Application action per UI method
- Display backend success / error outcomes
- Use `App\Support\Presentation\Concerns\HandlesUiMutationFeedback` (or equivalent existing thin-UI feedback pattern)

### 5.3 Forbidden Livewire responsibilities

- Domain mutation
- Repository access / injection (`EmployeeRepositoryContract`, `DepartmentRepositoryContract`, or any repository)
- Eloquent access
- Database queries / `DB::transaction`
- Business rule calculation
- Authorization calculation / role mirroring
- Workflow orchestration (multiple Application actions in one UI method)
- Capability derivation
- Identity existence checks as UI authority
- Department active/inactive decisions as UI authority

### 5.4 Confirmation behavior (CR-EC-002)

| Approved | Forbidden |
|---|---|
| Success flash message | Livewire repository injection |
| Returned identifier display from Application action return value | `EmployeeRepositoryContract` / `DepartmentRepositoryContract` in Presentation |
| Non-authoritative display of fields **already present** on the action return | New read contracts / `EmployeeReadContract` |
| | Separate post-mutation `findById` point-read |
| | List / search APIs |
| | Profile / edit panels |

**Optional point-read:** **Not authorized** for this feature — no existing Application-layer UI read surface exists without backend expansion. Contract YAML `optional_point_read` lines are superseded by contract-review CR-EC-002 for lock/implementation.

---

## 6. Application action bindings

### 6.1 Allowed consumption (existing only)

| UI method (suggested name) | Inject / call | `execute` inputs (from form) |
|---|---|---|
| `createEmployee` | `CreateEmployeeAction` | `identity_id`, `employee_code`, `first_name`, `last_name`, `national_code`, `hire_date` |
| `createDepartment` | `CreateDepartmentAction` | `name`, `code`, optional `manager_id`, optional `parent_id`, optional `lottery_priority` (default `0`) |
| `assignDepartment` | `AssignDepartmentToEmployeeAction` | `employee_id`, `department_id` |
| `deactivateDepartment` | `DeactivateDepartmentAction` | `department_id` |

Each UI method delegates to **exactly one** `execute()` call. Map string form fields to Value Objects / types at the Presentation→Application call boundary without business reinterpretation.

### 6.2 Forbidden Application / backend work

- New Application services
- New contracts / ports
- New DTOs
- New capability flags
- New migrations / schema
- Domain changes
- Infrastructure changes
- Identity module changes
- Employee deactivate Application action (undelivered)
- Any change under `app/Modules/Employee/Application/**`, `Domain/**`, or `Infrastructure/**`

---

## 7. Input and validation boundary

### 7.1 Input model lock

| Approved | Forbidden |
|---|---|
| Explicit text inputs for `identity_id`, `employee_id`, `department_id` (and other action-required scalar fields) | Employee dropdown |
| Manual UUID/id entry | Department dropdown |
| | Autocomplete / typeahead |
| | Picker components |
| | Department tree browser |
| | Browse selectors |

**Reason:** Required list/read APIs do not exist and are excluded from this feature.

### 7.2 Validation boundary lock (CR-EC-004)

| Allowed UI validation | Forbidden UI authority |
|---|---|
| Required fields | Identity existence decision |
| Primitive format checks | Department active/inactive authority |
| UUID shape checks | Employee eligibility calculation |
| Date shape checks | Permission evaluation |
| Integer shape checks | Capability calculation |
| Empty / null normalization | Business state derivation |

All business authority remains in Application / Domain layers (and existing Identity read usage **inside** `CreateEmployeeAction`).

---

## 8. Authorization boundary

| Layer | Rule |
|---|---|
| **Authority** | Backend remains authoritative |
| **Allowed existing** | `MutationPolicyEnforcementPoint`, `MutationCapabilityCatalog`, `EmployeeMutationAuthorizationGate` |
| **Capability keys** | `employee.create`, `employee.department.create`, `employee.department.assign`, `employee.department.deactivate` |
| **Hub form visibility** | Forms may render for authenticated users who reach the hub (RD-EC-005) |
| **Submit legality** | Decided solely by Application actions |
| **Forbidden** | UI permission recreation; UI `hasRole` / Gate as authority; new authorization layer; new capability flags |

Surface backend validation / authorization failures via thin UI feedback without semantic remapping.

---

## 9. Anti-leak constraints

Governing document: `docs/architecture/ui/UI-ANTI-LEAK-CONTRACT.md`

| Required | Forbidden smells |
|---|---|
| Thin Livewire → Application action only | Calculated status smell |
| No Domain entity mutation in Presentation | Authorization mirroring smell |
| No Employee / Identity Infrastructure or Eloquent imports in Presentation | Workflow orchestration smell |
| Errors surfaced from backend outcomes without local rule-system reinterpretation | Derived capability smell |
| Presentational logic only (tabs/sections, loading, flash display) | |

---

## 10. Forbidden implementation scope

The following remain **explicitly out of scope** (must not appear in code after lock-review):

- Employee list / search / profile / edit
- Employee deactivate UI (no delivered Application action)
- Department tree UI / department browse UI
- Selector / dropdown UX
- Dependents UI
- Full HR admin panel
- Identity admin / login / auth UX changes
- Eligibility admin UI
- Notification UI changes (beyond shared layout nav addition of کارکنان)
- Request UI changes (beyond shared layout nav addition)
- Backend expansion of any kind
- Console command product-scope changes
- Reopening closed Notification features (P2–P9) or closed Request UI features
- Vue / React / Inertia / SPA
- Separate public routes per mutation

---

## 11. Allowed file ownership

### 11.1 May create or modify — **only after lock-review approval**

| Path | Constraint |
|---|---|
| `app/Modules/Employee/Presentation/Livewire/EmployeeHubPage.php` | **New** — single hub; four mutation methods; Application action injection only |
| `resources/views/livewire/employee/employee-hub-page.blade.php` | **New** — Persian RTL forms for four capabilities; flash + returned id display |
| `app/Modules/Employee/Presentation/Routes/web.php` | **New** — `Route::get('/', EmployeeHubPage::class)->name('employees.hub');` only |
| `app/Modules/Employee/Presentation/Providers/EmployeePresentationServiceProvider.php` | Add `employeeWebRoutePath()` static method only; preserve existing console command registration |
| `routes/web.php` | **Surgical only:** import `EmployeePresentationServiceProvider`; add `employees` prefix group inside existing authenticated middleware group; no other route/home/login changes |
| `resources/views/components/layouts/app.blade.php` | **Nav only:** add **کارکنان** link after **اعلان‌ها**; no badge/logout/brand changes |
| `tests/Feature/Modules/Employee/EmployeeHubUiFlowTest.php` | **New** — feature UI flow tests (repository-equivalent of `tests/Feature/UI/Employee/**`) |

### 11.2 Must not modify

| Path / area | Reason |
|---|---|
| `app/Modules/Employee/Application/**` | No backend expansion |
| `app/Modules/Employee/Domain/**` | No domain changes |
| `app/Modules/Employee/Infrastructure/**` | No infrastructure changes |
| `app/Modules/Identity/**` | Identity out of scope |
| `app/Modules/Notification/**` | Closed Notification UI — do not reopen |
| `app/Modules/Request/**` | Closed Request UI — do not reopen |
| `database/migrations/**` | No schema |
| Notification / Request Livewire, Blade, contracts, locks, closeouts | Untouched except shared layout nav |
| Auth session controllers / login views | Identity/Auth UX excluded |

### 11.3 Must not create

- Additional Livewire pages (list, profile, dependents, etc.)
- API controllers for Employee hub
- New Application contracts / DTOs / services
- `EmployeeReadContract` or read ports
- SPA / Inertia / Vue / React layers

---

## 12. Verification requirements

After lock-review approval, implementation must verify:

| ID | Requirement |
|---|---|
| V-EC-001 | `GET /employees` (`employees.hub`) renders authenticated Employee Hub |
| V-EC-002 | Guest cannot access hub (auth middleware parity) |
| V-EC-003 | Nav label **کارکنان** appears immediately after **اعلان‌ها** |
| V-EC-004 | Nav uses plain `href` to `employees.hub`; active state on `employees.*` |
| V-EC-005 | Exactly four mutation forms/affordances exist — no list/search/profile chrome |
| V-EC-006 | Create Employee delegates only to `CreateEmployeeAction` |
| V-EC-007 | Create Department delegates only to `CreateDepartmentAction` |
| V-EC-008 | Assign Department delegates only to `AssignDepartmentToEmployeeAction` |
| V-EC-009 | Deactivate Department delegates only to `DeactivateDepartmentAction` |
| V-EC-010 | No repository / Eloquent / Domain / `DB::` access in Presentation Livewire |
| V-EC-011 | Backend authorization / validation failures surface correctly without UI remapping |
| V-EC-012 | Success confirmation is flash + returned id (action return only) |
| V-EC-013 | No excluded feature appears (selectors, tree, dependents, Identity UX, etc.) |
| V-EC-014 | Closed Notification and Request features remain unchanged aside from shared layout nav addition |
| V-EC-015 | Architecture / anti-leak surface discipline assertions for `EmployeeHubPage` |

Map contract acceptance criteria `AC-EC-001`–`AC-EC-009` into `EmployeeHubUiFlowTest` (and any narrowly justified companion assertions in that file only).

---

## 13. Explicit non-authorization

This implementation-lock creation task does **NOT** authorize:

- Livewire components
- Blade views
- Routes (including `routes/web.php` edits)
- Layout changes
- Tests
- Application / Domain / Infrastructure changes
- Identity / Notification / Request module changes
- Coding of any kind

Implementation remains unauthorized until:

**implementation-lock → lock-review approval**

---

## 14. Final lock status

**`IMPLEMENTATION_LOCK_CREATED_READY_FOR_REVIEW`**

| Field | Value |
|---|---|
| **Next allowed gate** | `lock-review` |
| **Expected next artifact** | `docs/ui/review/employee/employee-context-ui.lock-review.md` (or repository-equivalent) |
| **Implementation authorized?** | **No** |

---

*Implementation lock only. Next gate: lock-review. No code in this task.*
