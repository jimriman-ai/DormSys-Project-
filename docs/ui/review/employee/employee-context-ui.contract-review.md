# Employee Context UI — Contract Review

## Feature

| Field | Value |
|---|---|
| **Feature code** | `employee-context-ui` |
| **Feature title** | Employee Context UI |
| **Domain area** | employee |
| **Reviewed contract** | `docs/ui/contracts/employee/employee-context-ui.feature-contract.yaml` |
| **Contract version** | `0.1.0` |
| **Governance gate** | `contract-review` |
| **Review date** | 2026-07-10 |

---

## 1. Decision summary

| Field | Result |
|---|---|
| **Verdict** | **Contract approved** |
| **Final status** | **`CONTRACT_APPROVED_FOR_IMPLEMENTATION_LOCK`** |
| **Implementation authorized?** | **No** — implementation-lock drafting is the next authorized step only |
| **Contract revision required?** | **No** |
| **Blocking corrections** | **None** |
| **Material governance violations** | **None** |

The feature contract faithfully encodes the approved review-decision MVF: single Employee Hub, shared nav **کارکنان** after **اعلان‌ها**, and exactly four UI capabilities bound to the four delivered Application actions. Exclusions, RD-EC-001–005, anti-leak posture, and backend-authoritative authorization are preserved. Ambiguities on route naming, optional point-read, Livewire specificity, and form-validation boundary are resolved below for the implementation-lock without requiring contract revision.

---

## 2. Inputs reviewed

| Artifact | Role |
|---|---|
| `docs/ui/contracts/employee/employee-context-ui.feature-contract.yaml` | Primary — contract under review |
| `docs/ui/review/employee/employee-context-ui.review-decision.md` | Authoritative MVF / RD-EC baseline |
| `docs/ui/analysis/employee/employee-context-ui.feature-analysis.md` | Supporting analysis |
| `docs/ui/analysis/employee/employee-context-ui.repo-inspection.md` | Repository truth (Application surfaces; no Employee web UI) |
| `docs/product/product-authorization-next-ui-feature.md` | Product authorization (`AUTHORIZED`) |
| `docs/architecture/ui/UI-ANTI-LEAK-CONTRACT.md` | Thin UI / anti-leak governance |
| Existing UI contract-review conventions (e.g. notification P7/P9) | Gate disposition / structure |

**Missing required inputs:** None.

---

## 3. Contract fidelity review

### 3.1 Review-decision MVF match

| Approved MVF element | Contract encoding | Result |
|---|---|---|
| Employee Hub Page (single) | `approved_mvf.surfaces.employee_hub_page`; `presentation.hub.type: single_page`; `route.constraint` one public hub route | **Pass** |
| HR / Employee navigation | `hr_employee_navigation_entry`; `presentation.layout_nav` | **Pass** |
| Create Employee | `ui_capabilities.create_employee` → `CreateEmployeeAction` | **Pass** |
| Create Department | `ui_capabilities.create_department` → `CreateDepartmentAction` | **Pass** |
| Assign Department | `ui_capabilities.assign_department` → `AssignDepartmentToEmployeeAction` | **Pass** |
| Deactivate Department | `ui_capabilities.deactivate_department` → `DeactivateDepartmentAction` | **Pass** |

No fifth UI capability or out-of-MVF surface is introduced.

### 3.2 Route, nav, and presentation

| Requirement | Contract evidence | Result |
|---|---|---|
| Single hub page | `presentation.hub`; RD-EC-002 encoding | **Pass** |
| `employees.hub` / `GET /employees` | `route.entrypoint` | **Pass** (see CR-EC-001) |
| Nav label **کارکنان** | `layout_nav.label` | **Pass** |
| Immediately after **اعلان‌ها** | `PLACE-EC-001` order positions 1–3 | **Pass** |
| Plain `href`; no `wire:navigate` | `TRANS-EC-001` | **Pass** |
| Active state `employees.*` | `ACTIVE-EC-001` | **Pass** |
| Persian RTL hub | `locale: fa`, `direction: rtl` | **Pass** |

### 3.3 Application bindings only (no backend expansion)

| Capability | Bound action | Result |
|---|---|---|
| Create Employee | `CreateEmployeeAction::execute` | **Pass** |
| Create Department | `CreateDepartmentAction::execute` | **Pass** |
| Assign Department | `AssignDepartmentToEmployeeAction::execute` | **Pass** |
| Deactivate Department | `DeactivateDepartmentAction::execute` | **Pass** |

| Expansion check | Contract evidence | Result |
|---|---|---|
| No new Application APIs / contracts / DTOs / capability flags / migrations | `mutations.constraints`; `existing_capabilities.not_introduced`; `EX-EC-009` | **Pass** |
| No Domain / Infrastructure changes authorized | `forbidden_changes.backend`; ownership | **Pass** |
| UUID/id text inputs where selectors unavailable | `INPUT-EC-001`; per-action input notes | **Pass** |
| Flash + returned id confirmation | `CONFIRM-EC-001`; per-action `success_confirmation.mode` | **Pass** (point-read clarified in CR-EC-002) |
| Backend authorization authoritative | `AUTH-EC-001`; RD-EC-005 | **Pass** |
| No new capability flags | RD-EC-005; `AUTH-EC-001.rejected` | **Pass** |

### 3.4 RD-EC-001 through RD-EC-005 preservation

| ID | Preserved in contract? | Result |
|---|---|---|
| RD-EC-001 Four-mutation MVF | `review_decision_resolutions` + four `ui_capabilities` | **Pass** |
| RD-EC-002 Single hub | Single route + hub hosts all forms | **Pass** |
| RD-EC-003 Nav label/placement | **کارکنان** after **اعلان‌ها** | **Pass** |
| RD-EC-004 Flash + id | `CONFIRM-EC-001`; profile panels rejected | **Pass** |
| RD-EC-005 No new capability flags | Forms visible; backend decides legality | **Pass** |

### 3.5 Exclusions preservation

All review-decision exclusions **EX-EC-001 through EX-EC-014** are present in the contract `exclusions` list, including employee listing/search/profile editing, department tree, selectors/dropdowns, dependents, full HR admin, Identity/Auth UX, backend expansion, reopening closed Notification (P2–P9) and Request UI features, undelivered employee deactivate Application action, eligibility admin UI, and console command product-scope changes.

| Closed-feature non-touch | Contract evidence | Result |
|---|---|---|
| Notification P2–P9 | `EX-EC-010`; `forbidden_changes.closed_features` | **Pass** |
| Closed Request UI | `EX-EC-011`; `forbidden_changes.closed_features` | **Pass** |

### 3.6 Anti-leak / thin UI

| Requirement | Contract evidence | Result |
|---|---|---|
| Thin Livewire → Application only | `anti_leak_rules`; mutation constraints | **Pass** |
| No Domain/Infrastructure in Presentation | Explicit forbidden imports | **Pass** |
| No business-authoritative UI logic | Forbidden smells + AUTH/CONFIRM rules | **Pass** |
| Implementation not authorized by contract | `implementation_authorized: false`; governance | **Pass** |

### 3.7 Blocking conflicts

**None.** Non-blocking clarifications are resolved in §4 for lock consumption; they do not require `NEEDS_CONTRACT_REVISION`.

---

## 4. CR-EC-001 to CR-EC-004 resolutions

### CR-EC-001 — Route convention

**Decision: Accept `employees.hub` / `GET /employees` as frozen for implementation-lock.**

| Check | Finding |
|---|---|
| Path convention | Plural module path `/employees` matches Request (`/requests`) and Notification (`/notifications`) entry patterns |
| Name convention | Existing UI entry pages often use `.index` (`requests.index`, `notifications.index`). Contract chose `.hub` because the surface is a multi-form hub, **not** a list index (list UX is excluded) |
| Adjustment required? | **No** — `.hub` is an acceptable deliberate name for this non-list MVF |

**Lock instruction:** Register one authenticated Livewire route named `employees.hub` at `GET /employees` under Employee Presentation route registration (same class of module web routing as Request/Notification). Do not add per-mutation public routes. Do not rename to `employees.index` unless a future governance revision explicitly supersedes this freeze (not authorized here).

---

### CR-EC-002 — Optional point-read

**Decision: Flash + returned id only by default. No Presentation repository injection. No new read contracts. Separate post-mutation `findById` point-read is not available without backend expansion.**

| Rule | Resolution |
|---|---|
| Presentation/Livewire inject `EmployeeRepositoryContract` / `DepartmentRepositoryContract` | **Forbidden** |
| Default success confirmation | **Flash + returned identifier(s)** from the Application action return value |
| Display fields already present on the action return (e.g. entity id from `Employee` / `Department` returned by `execute`) | **Allowed** as part of flash + returned-id confirmation — non-authoritative display only; not a profile/edit surface |
| Separate optional point-read via `EmployeeRepositoryContract::findById` / `DepartmentRepositoryContract::findById` | **Not authorized for this feature** — repository contracts are not an approved Presentation consumption surface; `EmployeeReadContract` / UI read Application service **does not exist**; creating one would be backend expansion |
| New Application read contracts / DTOs for confirmation | **Forbidden** |

**Lock instruction:** Implement confirmation strictly as flash + ids (and optional non-authoritative display of values already returned by the mutation action). Do not inject repositories into Livewire. Do not add Application read ports for this feature. Treat contract YAML `optional_point_read` lines as **superseded by this review** for lock/implementation purposes (interpretation freeze; contract file revision not required for approval).

---

### CR-EC-003 — Livewire specificity

**Decision: Freeze implementation target as Livewire 3 + Blade within the existing Laravel UI architecture.**

| Allowed | Forbidden |
|---|---|
| Livewire 3 component(s) for Employee Hub | SPA architecture |
| Blade view(s) for hub rendering | Inertia |
| Module Presentation registration patterns consistent with Request/Notification | Vue / React (or other alternate presentation stacks) as the hub implementation |
| Shared layout Blade nav edit | Replacing Livewire with controllers-only HTML forms as a different stack (not required; Livewire is the frozen target) |

Contract phrasing “Livewire (or equivalent presentation)” is **resolved** to **Livewire 3 + Blade only** for lock and implementation.

---

### CR-EC-004 — Form validation boundary

**Decision: UI may assist with syntactic/primitive checks only. Business and authorization meaning remain backend-authoritative.**

#### Allowed UI validation / assistance

- Required-field presence hints
- Primitive format checks
- UUID shape checks (syntactic)
- Date / integer shape checks (syntactic)
- Empty / null normalization for optional text fields (transport hygiene only)

#### Forbidden UI validation / authority

- Identity existence authority (`userExists` / unknown identity) — Application / Identity read contract only
- Department active/inactive authority — Application action only
- Employee eligibility rules — out of MVF; not UI authority
- Permission / capability calculation or role mirroring — backend mutation policy / gate only
- Business state derivation (lifecycle meaning, readiness, derived capabilities)

Failures from Application actions must surface via thin UI feedback without semantic remapping into a local rule system.

---

## 5. Approved contract constraints for implementation-lock

The implementation-lock must encode and enforce:

1. **Single** Employee Hub Livewire 3 + Blade page at `employees.hub` (`GET /employees`)
2. Shared layout nav item **کارکنان** immediately after **اعلان‌ها**; plain `href`; `employees.*` active state
3. **Exactly four** mutation affordances bound only to:
   - `CreateEmployeeAction`
   - `CreateDepartmentAction`
   - `AssignDepartmentToEmployeeAction`
   - `DeactivateDepartmentAction`
4. Explicit UUID/id text inputs (no list/search/tree/dropdown selectors)
5. Success confirmation = flash + returned id (action return only; no repository injection; no new read contracts)
6. Backend-authoritative mutation auth; no new capability flags; no UI role mirroring
7. Full exclusion set EX-EC-001–014 (including no Notification/Request reopen; no backend expansion)
8. Anti-leak: thin Livewire → Application only; no Domain/Infrastructure/Eloquent in Presentation
9. CR-EC-001–004 resolutions above as lock-binding constraints
10. Feature-specific UI tests (paths pinned at lock) covering hub render, nav, four mutations, guest denial, and exclusion/anti-leak surface discipline

---

## 6. Required implementation-lock focus areas

| Focus | Lock must pin |
|---|---|
| **Allowed files** | Employee Presentation Livewire hub class + Blade; Employee web route registration for `employees.hub`; `resources/views/components/layouts/app.blade.php` (nav only); designated feature UI test file(s) |
| **Forbidden files** | Employee Application/Domain/Infrastructure; Identity presentation/Application changes for this feature; Notification/Request presentation beyond shared layout nav; any list/profile/dependents surfaces |
| **Component naming** | Exact Livewire class / view / route registration paths |
| **Form field map** | Property names ↔ Application `execute` parameters per capability |
| **Feedback pattern** | Flash + returned id; error surfacing pattern (e.g. existing thin-UI feedback trait if used elsewhere) |
| **Validation** | Encode CR-EC-004 allowed vs forbidden boundary |
| **Point-read** | Explicitly forbid repository injection; confirm flash+id-only per CR-EC-002 |
| **Stack** | Livewire 3 + Blade only (CR-EC-003) |
| **Tests** | Map AC-EC-001–009 to concrete tests; guest/auth; nav order; four mutations; exclusions |
| **Non-goals** | Reaffirm no backend expansion and no closed-feature reopening |

---

## 7. Explicit non-authorization

This contract-review:

- **Does not** authorize implementation or any application code changes
- **Does not** create an implementation-lock (next gate only)
- **Does not** authorize creating Livewire components, Blade views, or routes in this task
- **Does not** authorize modifying Application, Domain, Infrastructure, Identity, Notification, or Request code
- **Does not** expand MVF or introduce backend requirements
- **Does not** revise the feature-contract file (approval without revision)
- **Does not** reopen closed Notification or Request UI features

---

## 8. Final status

**`CONTRACT_APPROVED_FOR_IMPLEMENTATION_LOCK`**

| Field | Value |
|---|---|
| **Next allowed gate** | `implementation-lock` |
| **Expected next artifact** | `docs/ui/locks/employee/employee-context-ui.implementation-lock.yaml` (or repository-equivalent under `docs/ui/locks/employee/`) |
| **Implementation authorized?** | **No** until lock + lock-review approval complete |

---

*Contract review only. Next gate: implementation-lock (separate task).*
