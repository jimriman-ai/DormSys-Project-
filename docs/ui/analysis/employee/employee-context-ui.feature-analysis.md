# Employee Context UI — Feature Analysis

## Feature

| Field | Value |
|---|---|
| **Canonical feature slug** | `employee-context-ui` |
| **Feature title** | Employee Context UI |
| **Domain** | Employee |
| **Source specification** | `specs/003-employee-context` |
| **Analysis date** | 2026-07-10 |
| **Gap classification** | `UI_PRESENTATION_GAP` (greenfield Livewire/Blade over existing Application mutations) |
| **Product authorization** | `docs/product/product-authorization-next-ui-feature.md` — **`AUTHORIZED`** |

## Analysis objective

Define a governance-ready MVF boundary for Employee Context UI that:

1. Consumes **only already delivered** Employee Application capabilities evidenced in repo-inspection.
2. Does **not** convert missing backend capabilities into UI requirements.
3. Stays within product authorization (and its exclusions).
4. Prepares a clear contract-phase boundary without drafting a contract or lock.

---

## Inputs considered

| Input | Role |
|---|---|
| `docs/product/product-authorization-next-ui-feature.md` | Authoritative intake + exclusions |
| `docs/ui/analysis/employee/employee-context-ui.repo-inspection.md` | Primary repository truth |
| `.specify/governance/_meta/authority-model.md` | Authorization ≠ inference |
| `specs/003-employee-context/spec.md` | FR / FR-EX / OA-03-* |
| `specs/003-employee-context/plan.md` | Phase F / R-15 deferral (historical, not requirement) |
| `specs/003-employee-context/tasks.md` | US3+ hold; Livewire deferred (historical) |

---

## 1. Confirmed business capabilities available for UI consumption

Evidence source: repo-inspection §§3–7 and cited Application paths.

### 1.1 Mutations (Application actions — delivered)

| Capability | Application surface | Evidence |
|---|---|---|
| Create employee (Identity attachment) | `CreateEmployeeAction` | Repo-inspection §3.2; requires `IdentityUserReadContract::userExists` |
| Create department | `CreateDepartmentAction` | Repo-inspection §3.2 |
| Deactivate department | `DeactivateDepartmentAction` | Repo-inspection §3.2 |
| Assign department to employee | `AssignDepartmentToEmployeeAction` | Repo-inspection §3.2; rejects inactive department |

Mutation policy keys exist: `employee.create`, `employee.department.create`, `employee.department.deactivate`, `employee.department.assign` (`MutationCapabilityCatalog` — repo-inspection §7).

### 1.2 Point reads (repository — delivered, limited)

| Capability | Surface | Evidence |
|---|---|---|
| Load employee by id | `EmployeeRepositoryContract::findById` | Repo-inspection §3.3 |
| Load employee by identity id | `findByIdentityId` / `findEmployeeIdByIdentityUserId` | Repo-inspection §3.3 |
| Load department by id | `DepartmentRepositoryContract::findById` | Repo-inspection §3.3 |

These support **post-mutation confirmation / detail-by-id** only — **not** browse/list UX.

### 1.3 Upstream Identity reads (available, constrained)

| Capability | Surface | Evidence |
|---|---|---|
| Validate identity exists at create | `IdentityUserReadContract::userExists` | Repo-inspection §3.2 / CreateEmployeeAction |
| Optional single-user summary | `findUserSummary` | Repo-inspection §6 / Identity contract |
| Active actor check for mutations | `isUserActive` via `EmployeeMutationAuthorizationGate` | Repo-inspection §7 |

### 1.4 Present but not recommended as HR admin MVF surfaces

| Capability | Why not MVF UI by default |
|---|---|
| `EmployeeEligibilityContract` | Supplier API for Request eligibility (CD-013); not an HR CRUD admin surface in product purpose |
| Artisan console commands | Existing presentation; UI feature replaces discoverability for web, not a contract requirement to remove console |

---

## 2. Possible MVF UI scope (existing Application capabilities only)

### 2.1 Gap statement

| Fact | Evidence |
|---|---|
| No Employee web routes / Livewire / Blade | Repo-inspection §§4–5 |
| No layout nav to Employee/HR | `app.blade.php` has requests + notifications only |
| Backend mutations exist via Application + Artisan | Repo-inspection §§3.2, 5 |

**Gap type:** greenfield **presentation** for already-delivered Employee mutations — `UI_PRESENTATION_GAP`.

### 2.2 Recommended MVF (evidence-bounded)

MVF should be a **thin Livewire HR mutation console** that delegates exclusively to existing Application actions, plus minimal discoverability and point-read confirmation.

| MVF element | Justification from evidence | Backend prerequisite? |
|---|---|---|
| Authenticated Employee web route group + Livewire pages | Presentation absent; Request/Notification establish pattern | No |
| Layout nav link (e.g. HR/Employee) to Employee entry route | Discoverability gap analogous to P7; layout exists | No |
| Create-employee form → `CreateEmployeeAction` | Action delivered | No |
| Create-department form → `CreateDepartmentAction` | Action delivered | No |
| Deactivate-department control → `DeactivateDepartmentAction` | Action delivered | No |
| Assign-department form → `AssignDepartmentToEmployeeAction` | Action delivered | No |
| Post-success point display via `findById` | Repository point lookup delivered | No |
| UUID / id text inputs for `identity_id`, `employee_id`, `department_id` | No list APIs; manual id entry is the only evidence-supported selector pattern | No |
| Thin UI: no repository/Eloquent in Livewire; delegate to Application | UI Anti-Leak / product purpose | No |
| Persian RTL presentation | Project constitution / product purpose | No |
| UI flow tests for authorized surfaces | DoD / prior UI feature precedent | No |

### 2.3 Explicit MVF interaction model (no invented list UX)

Because list/search/paginate APIs are **absent** (repo-inspection §3.3, §9):

- MVF **must not** require employee/department index pages.
- Selectors for assign/deactivate **must** use **explicit UUID entry** (or equivalent non-list transport), not browse pickers that imply new read models.
- Identity attachment on create uses **manual Identity UUID entry** + backend `userExists` validation — **not** Identity Livewire admin (product exclusion).

This keeps MVF as **UI-only over existing actions**, consistent with product authorization: do not expand Employee backend without separate authorization.

### 2.4 Optional MVF subset (for review-decision)

If review-decision needs a narrower first slice, evidence still supports a **subset** without inventing backend:

1. Create employee + layout nav + routes, **or**
2. Department create + assign + deactivate only, **or**
3. Full four-mutation MVF (recommended default above).

Choosing among these is a **review-decision** concern, not a missing-evidence block.

---

## 3. Features that require backend prerequisites

Do **not** include in UI contract MVF unless a separate product/backend authorization is issued.

| Desired UI idea | Missing evidence | Prerequisite |
|---|---|---|
| Employee list / search / pagination | No list on `EmployeeRepositoryContract`; no `EmployeeReadContract` | New Application read/list (or `EmployeeReadContract` + list query) |
| Department list / tree browser / parent picker UI | No department list API | New Application list/read |
| Dependent add/list/update screens | No dependents migration/actions (US3 hold) | US3 backend delivery |
| Edit employee profile after create | No update Application action | New Application mutation |
| Deactivate/activate employee from UI | Domain methods only; no Application action | New Application mutation |
| Identity user picker / directory | Identity admin UI excluded; no list API evidenced for UI | Identity admin feature and/or Identity list read authorization |
| Role-specific HR Gate beyond active principal | Gate is active-identity-only today | Product/security decision + Identity permission model |
| Eligibility admin console | Exists as supplier; not HR CRUD | Separate product decision if ever desired |

**Rule applied:** missing capabilities are **prerequisites**, not UI requirements (task rule + product exclusion on backend expansion).

---

## 4. Forbidden scope

From product authorization §3 Excluded scope + spec FR-EX + repo-inspection:

| Forbidden | Authority |
|---|---|
| Identity login/session UX (OA-02-01) | Product auth; spec FR-EX-004 |
| Identity Livewire admin (T035–T037) | Product auth |
| Request / Allocation / Lottery / Voucher / Dormitory / Audit explorer / Notification residual UI | Product auth |
| Request Show workflow mutations | Product auth |
| Reopening closed P2–P9 / request UI features | Product auth |
| Expanding Employee backend under this UI feature without separate authorization | Product auth |
| Converting Phase F / R-15 deferral text into mandatory full HR suite | Analysis rule; deferral is historical |
| UI-side business authority (eligibility rules, identity immutability, department inactive rules) | UI Anti-Leak; backend already owns these in actions |
| Direct repository/Eloquent/DB access from Livewire/Blade | Architecture / Anti-Leak |
| Inventing list/browse UX that requires undelivered read APIs | Task rule + repo-inspection gaps |

---

## 5. Dependency risks

| Risk | Severity | Evidence | Mitigation for contract phase |
|---|---|---|---|
| No list APIs → poor HR UX if browse expected | Medium | Repo-inspection §9 | Contract MVF = id-entry mutation forms; defer lists |
| Identity UUID hard to obtain without Identity admin | Medium | Product excludes Identity admin; `findUserSummary` is point-only | Document UUID entry; do not pull Identity admin into scope |
| Auth is “active principal,” not HR role | Medium / unknown | `EmployeeMutationAuthorizationGate` | Contract may consume existing mutation gate; do not invent role matrix in UI |
| Dependents expected by business purpose wording (“related Employee-owned records”) | Low for MVF | Dependents absent | Explicitly out of MVF; requires US3 backend auth later |
| Greenfield routes/pages increase surface area | Low | No existing Employee UI | Follow Request/Notification Livewire + middleware precedents in later lock |
| Eligibility confusion | Low | Eligibility contract exists | Keep out of HR MVF unless review-decision adds it deliberately |

**No intake blocker** remains for proceeding to review-decision / contract drafting of the evidence-bounded MVF.

---

## 6. Recommended governance boundary for contract phase

### 6.1 In-contract (proposed)

1. **Classification:** successor/greenfield **presentation** feature on Employee module; consumes existing Application actions.
2. **In scope:**
   - Authenticated Employee Livewire presentation for the four delivered mutations (or review-decision subset).
   - Layout discoverability link to Employee entry route.
   - Point-read confirmation after mutations via existing `findById` (presentation mapping only).
   - Manual UUID/id transport for identity/employee/department references.
   - Error/success surfacing from backend exceptions/mutation policy without UI reinterpretation.
3. **Out of scope:**
   - All §3 backend-prerequisite features.
   - All §4 forbidden items.
   - Eligibility admin UI.
   - Console command removal/changes (unless lock later allows incidental test-only touch).
4. **Architecture constraints:**
   - Thin Livewire → Application actions / contracts only.
   - No Employee Infrastructure imports in Presentation.
   - No Identity Infrastructure imports (CD-012 / BT-05 posture).
   - No new Employee Application APIs in this feature unless separately authorized.

### 6.2 Decisions deferred to review-decision (not blocked)

| Decision ID (working) | Question |
|---|---|
| RD-EC-001 | Full four-mutation MVF vs narrower first slice |
| RD-EC-002 | Single hub page vs separate routes per mutation |
| RD-EC-003 | Layout label/copy (Persian) and nav placement |
| RD-EC-004 | Whether post-create detail panel is required or success flash + id display is enough |
| RD-EC-005 | Whether any additional capability flags must be backend-delivered for action visibility (vs always showing forms and letting mutation policy fail) |

---

## Feature analysis status

**`READY_FOR_REVIEW_DECISION`**

Analysis is complete for governance progression. MVF can be bounded to existing Application mutations without inventing backend list/dependent/update requirements.

---

## Proposed contract scope boundary

**One-line boundary:**

> Authenticated Persian RTL Livewire HR presentation that discovers Employee Context UI via layout nav and executes only the already-delivered Application mutations — create employee, create department, deactivate department, assign department — using explicit UUID/id inputs and point-read confirmation; no list/read-model expansion, no dependents, no Identity admin/login, no other modules.

---

## Blocking decisions

**None** that prevent the next gate.

Open items above are **review-decision** choices inside the evidence-bounded MVF, not missing product authorization or missing repository evidence for a viable UI-only slice.

---

## Next governance gate

**`review-decision`**

Expected next artifact (not created here):

`docs/ui/review/employee/employee-context-ui.review-decision.md`

(or repository-equivalent under `docs/ui/review/` / `docs/ui/decisions/` per project convention)

---

## Explicit non-actions

This analysis did **not**:

- Create a feature contract or implementation lock
- Write or modify application/backend code
- Expand scope beyond product authorization
- Convert missing list/dependent/update capabilities into UI requirements

---

*Feature analysis only. Next gate: review-decision.*
