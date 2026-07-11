# Spec03 Readiness Package — Employee Context (Completion Wave Batch 1)

**Artifact type:** Readiness / planning (non-authorizing)  
**Spec:** `003-employee-context` / catalog `spec03`  
**Decision date:** 2026-07-11  
**Companion:** `.specify/docs/handoff/completion-wave-plan.md`

**This package does not authorize implementation.**  
It records evidence for the next Implementation Authorization draft.

---

## Spec03 Readiness Status

| Field | Value |
| ----- | ----- |
| **Catalog status (informational)** | MVP Implemented — Wave 1A; Wave 1B Completed (US2); US3+ hold |
| **Evidence-backed status** | **`PARTIALLY_COMPLETE`** |
| **Batch 1 readiness** | **`READY_FOR_US3_IMPLEMENTATION_AUTHORIZATION`** |
| **US4 readiness** | **`REQUIRES_EVIDENCE_GAP_ANALYSIS`** (partial code present; not assumed complete or missing wholesale) |
| **Request live Dependent wiring** | **`INTEGRATION_NOT_READY`** until US3 supplier exists + IRG PASS |

---

## 1. Current Spec03 Status

| Wave / story | Task range | Authorization evidence | Implementation evidence |
| ------------ | ---------- | ---------------------- | ----------------------- |
| US1 Employee + Identity | T001–T026a | Closed — frozen (post-MVP) | Employee entity/model/repo/actions; BT-01–BT-05 path |
| US2 Department | T027–T034 | Authorized + complete (`spec03-post-mvp-authorization.md`) | Department entity/model/repo/actions; `DepartmentTest` |
| US3 Dependent | T035–T040 | **Hold — not authorized** | **Missing** (see §B) |
| US4 Eligibility | T041–T048 | **Hold — not authorized** | **Partial** (see §A/§B) |
| Phase 7 EmployeeRead | T049–T052 | Hold | **Missing** |
| Phase 8 Polish | T053–T058 | Quality after authorized slices | Partial / deferred Livewire |

Authoritative hold statement: `.specify/docs/handoff/spec03-post-mvp-authorization.md` — T035+ not authorized; do not start without new IA.

---

## 2. Completed Scope (Confirmed)

### US1 — Employee Profile with Identity Attachment

- Employee aggregate with immutable `identity_id` (CD-012)
- Validation via Identity read contract (no Identity Infrastructure imports)
- Persistence: `employee_employees`
- Create path + boundary tests delivered per Wave 1A close

### US2 — Department & Organizational Structure

- Department aggregate create/deactivate
- Employee department assignment with inactive-department guard (R-17)
- Persistence: `employee_departments`
- Wave 1B checkpoint complete

### Foundational artifacts already present (support US3)

- `DependentId` VO
- `DependentRelationship` enum
- Spec contracts / data-model for Dependent (design artifacts exist)

### Downstream Request (context only — do not modify in Batch 1)

- Request owns **Dependent snapshots** (CD-009); FamilyDirect uses `DependentSnapshotSourceContract`
- Live source is stubbed: `DependentSnapshotSourceStub` (“until spec03 US3 is authorized”)
- Request already consumes `EmployeeEligibilityContract` for enforcement (CD-013)

---

## 3. Remaining Scope

| Slice | Tasks | Product-core impact | Batch |
| ----- | ----- | ------------------- | ----- |
| **US3 Dependent** | T035–T040 | Unblocks live Dependent ownership + future IRG for Family source | **Batch 1** |
| **US4 Eligibility** | T041–T048 | Aligns CD-013 supplier completeness | Batch 1b (evidence-first) |
| **EmployeeReadContract** | T049–T052 | Downstream summary reads | Optional follow-on |
| **Polish** | T053–T058 | Quality for newly delivered slices | After authorized work |
| **Live Request Dependent adapter** | (not in Spec03 tasks.md as Employee work) | Replaces stub | Batch 2 — IRG + Integration IA |

---

## 4. US3 / US4 Boundaries

### US3 — Dependent Records (P2) — Batch 1 target

**Owns (Employee):**

- Dependent entity lifecycle under Employee aggregate scope (CD-009)
- Persistence table `employee_dependents` (FK to `employee_employees` only)
- Add / update / list within Employee Application services
- Employee-local feature tests

**Does not own:**

- Request snapshot persistence (`request_dependent_snapshots`)
- FamilyDirect submission orchestration
- `request_id` on Dependent (deferred in data-model)
- Livewire HR admin UI
- Any Request provider binding changes

**Independent test (spec):** Add/update/list dependents for an employee; Request module not required.

### US4 — Eligibility Computation (P3) — Batch 1b candidate

**Owns (Employee):**

- `EmployeeEligibilityContract` computation (CD-013)
- Domain eligibility rules + internal read ports
- Stub/null adapters until live Allocation/Request ports are IRG-authorized

**Does not own:**

- Request submission enforcement (already Request-owned)
- Allocation overlap truth (Allocation-owned when live)
- Replacing Request’s existing eligibility gateway without separate authorization

**Boundary rule for this package:** Do **not** assume US4 must be fully re-implemented. Inventory gaps against evidence and against **accepted runtime consumer** usage before drafting IA.

---

## 5. Missing Domain Capability

| Capability | Status | Evidence |
| ---------- | ------ | -------- |
| `Dependent` domain entity | **Missing** | No `Domain/Entities/Dependent.php` |
| Dependent invariants / ownership rules | **Missing** | Spec/data-model define; no entity behavior |
| `EligibilityCalculator` domain service | **Missing / incomplete vs contract** | No `Domain/Services/EligibilityCalculator.php`; logic currently in Application service |
| `EligibilityReasonCode` enum | **Missing vs contract** | Reason codes appear as strings in service |
| `ActiveAllocationReadPort` | **Missing** | Not present under Ports |
| Dependent read surface for cross-module snapshot source | **Missing** | Required before IRG can pass for Request live adapter |

---

## 6. Missing Persistence / Application Layers

### US3 (confirmed missing)

| Layer | Missing items (tasks.md) |
| ----- | ------------------------ |
| Persistence | Migration `employee_dependents`; `DependentModel`; `EmployeeModel::dependents()` |
| Application | `DependentRepositoryContract` + repo; `AddDependentAction`; `UpdateDependentAction` |
| Provider | Bind `DependentRepositoryContract` |
| Tests | `tests/Feature/Modules/Employee/DependentTest.php` |

### US4 (evidence: partial — do not wholesale re-authorize as greenfield)

| Item | Evidence status |
| ---- | --------------- |
| `EmployeeEligibilityContract` | **Present** — signature uses `string` + optional `excludingRequestId` (diverges from written contract doc `EmployeeId`-only API) |
| `EmployeeEligibilityService` | **Present** — active check + pending-request port; **no** active-allocation check |
| `EligibilityResultDTO` | **Present** |
| `PendingRequestReadPort` | **Present** — live bridge exists under `app/Integrations/Request/` |
| `ActiveAllocationReadPort` + Null adapter | **Missing** |
| `NullPendingRequestReadAdapter` | **Not used** — live bridge instead (do not casually revert) |
| `EmployeeEligibilityContractTest` (Employee module) | **Missing** as named Spec03 task; Request tests mock/call eligibility |
| `EmployeeReadContract` / summary DTO / service | **Missing** (Phase 7) |

**Rule:** Prefer documenting accepted consumer-facing eligibility behavior over “restoring” the Wave 1A contract text in a way that breaks Request. Any contract text sync is editorial/governance — **do not replace runtime contracts** in Batch 1.

---

## 7. Contract Impact on Request Module

| Concern | Impact | Batch 1 action |
| ------- | ------ | -------------- |
| `DependentSnapshotSourceContract` | Remains stub-backed until IRG + Integration IA | **Do not modify** |
| FamilyDirect flows | Continue using stub fixtures in tests | **Do not modify** |
| CD-009 ownership | Unchanged — Employee owns Dependent aggregate; Request owns snapshots | Preserve |
| `EmployeeEligibilityContract` | Already consumed by Request | **Do not break**; US4 work must preserve consumer compatibility |
| `PendingRequestReadPort` / bridge | Live integration already present | **Do not reverse** without separate IRG/IA |
| New Employee Dependent read API | Needed later so Request adapter can map thin | Design within Employee; expose only via accepted Application contract before IRG |

**Explicit prohibition:** Batch 1 Spec03 US3 implementation **must not** modify Request integration, replace the stub, or change Request contracts.

---

## 8. Required Tests (when authorized)

### US3 (Batch 1)

- Feature: add / list / update dependent under employee
- Reject orphan dependent (no parent employee)
- `NationalCode` validation when provided
- Ownership scoped to Employee context
- Architecture: no Request/Allocation Infrastructure imports; no cross-module FK beyond Employee tables

### US4 (Batch 1b — only for authorized gaps)

- Active employee eligible (given current port bindings)
- Inactive → `employee_inactive`
- Pending-request blocking (mock or bridge-consistent)
- Active-allocation blocking **only if** `ActiveAllocationReadPort` is authorized into scope
- Do not weaken Request eligibility tests

### Integration (Batch 2 — after IRG)

- Narrow adapter tests: stub → live mapping for `findSnapshotForDependent`
- No invented eligibility/business rules in adapter

---

## 9. Required Authorization Artifacts

| Artifact | Purpose | When |
| -------- | ------- | ---- |
| **Spec03 US3 Implementation Authorization** | `authorization-status: active` (or `partial`); `authorized-scope` verbatim `T035–T040` (or equivalent enumerated list); `blocked-scope` includes Request adapter work / UI / Spec04/07 reopen | **Next step** |
| Supersession note vs `spec03-post-mvp-authorization.md` | Record that US3 hold is lifted only for declared scope | With IA create |
| Spec03 US4 Implementation Authorization (optional) | Only after gap analysis; scope = proven missing tasks only | Batch 1b |
| Integration Readiness Gate record | Request → Dependent snapshot capability → Employee Application contract → thin mapping | After US3 DoD |
| Integration Implementation Authorization | Live adapter coding | Only if IRG = READY |

IA instance path pattern (Authority Map): `.specify/docs/handoff/<spec>-implementation-authorization.md`.

---

## A) Confirmed Existing Capability

1. Employee create + immutable `identity_id` (US1).
2. Department create/deactivate + assignment (US2).
3. Dependent foundational VOs/enums (not full aggregate).
4. Spec03 design artifacts for US3/US4 (`spec.md`, `plan.md`, `tasks.md`, `data-model.md`, contracts/).
5. Partial eligibility supplier used by Request (`EmployeeEligibilityContract` + service + pending-request port/bridge).
6. Request snapshot model + stub source (consumer side ready for future thin adapter).

---

## B) Missing Capability

1. Full Dependent domain + persistence + Application CRUD (US3 T035–T040) — **confirmed missing**.
2. Dependent feature tests — **confirmed missing**.
3. Employee-owned Dependent read API suitable for Request snapshot sourcing — **confirmed missing** (blocks IRG).
4. US4 items not evidenced: `ActiveAllocationReadPort`, `EligibilityCalculator`, Employee-local eligibility feature test, `EmployeeReadContract` — **confirmed missing or incomplete**.
5. Alignment between Spec03 contract markdown and runtime eligibility signatures — **documentation/governance debt** (not automatic rewrite authority).

---

## C) Required Governance Steps

1. Accept Completion Wave Plan (`COMPLETION_WAVE_READY`).
2. Draft and issue **Spec03 US3 Implementation Authorization** with verbatim scope T035–T040; explicitly block Request adapter, UI, Spec04/07 reopen.
3. HALT coding until IA `authorization-status` is `active` or `partial` covering intended tasks.
4. After US3 review gate PASS: run Integration Readiness Gate for Request Dependent live source (separate artifacts).
5. Only if IRG READY: issue Integration Implementation Authorization for thin adapter.
6. Separately: US4 evidence gap analysis → scoped IA (do not bundle blindly into US3 IA).

---

## D) Required Implementation Steps (after authorization only)

### Batch 1 — US3 (Employee-internal)

1. Migration `employee_dependents` per `data-model.md` (no `request_id`).
2. `Dependent` entity + `DependentModel` + relations.
3. Repository contract + Eloquent repository.
4. `AddDependentAction` / `UpdateDependentAction` (+ list/query as required by tests).
5. Provider binding.
6. `DependentTest` feature coverage.
7. Pint + PHPStan on touched Employee paths.
8. **Stop** — do not wire Request.

### Batch 1b — US4 (only authorized gaps)

1. Inventory runtime vs `contracts/employee-eligibility-service.md` and Request consumer.
2. Implement only authorized missing pieces (e.g., allocation port stub if required).
3. Preserve Request-compatible method signatures unless a separately authorized contract change exists (**none in this package**).

### Batch 2 — Integration (IRG-gated)

1. Prove Employee Application contract answers `findSnapshotForDependent` needs without new business rules.
2. Thin adapter + binding via approved composition root pattern.
3. Narrow integration tests.

---

## E) Definition of Done

### US3 Batch 1 DoD

- [ ] Active Implementation Authorization covers T035–T040
- [ ] All T035–T040 complete per tasks.md criteria
- [ ] US3 acceptance scenarios pass (add/update/list; ownership)
- [ ] PHPStan level 8 clean for Employee paths touched
- [ ] Pint applied to touched files
- [ ] No Request/Allocation/Dormitory module edits
- [ ] No UI Feature Contract / Livewire feature delivery claimed
- [ ] Review gate report produced; HALT for human batch approval

### Integration DoD (later)

- [ ] IRG outcome `READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION`
- [ ] Integration IA issued
- [ ] Live adapter thin; stub replaced only under that IA
- [ ] FamilyDependent path verified without inventing Employee rules in Request

### Spec03 product-core “wave complete” (aspirational — not Batch 1 alone)

- US3 DoD + (optional) authorized US4 gaps closed + IRG live Dependent source accepted
- Spec03 UI / Phase F still deferred unless separately product-authorized

---

## Recommended Next Authorization Step

**Issue:** Spec03 US3 Implementation Authorization  
**Scope (verbatim recommendation):** `T035`, `T036`, `T037`, `T038`, `T039`, `T040`  
**Blocked-scope (recommended):** Request adapter/stub replacement; UI/Livewire HR admin; Spec04/Spec07 reopen; US4/Phase 7 unless listed in a separate record  
**Authority actor:** Governance Review (per Authority Map)  
**Predecessor:** `.specify/docs/handoff/spec03-post-mvp-authorization.md` (US3 hold)  
**Does not authorize:** coding before the IA file exists with `authorization-status: active|partial`

---

## Confirmation Constraints Honored

- No assumption that US4 must be fully built from scratch
- Existing contracts not replaced by this package
- Request integration not modified
- Future cross-module replacement requires Integration Readiness Gate
- Spec04 / Spec07 / closed Specs not reopened

---

## Document Control

- Version: 1.0.0
- Status: Readiness package — non-authorizing
- Owner: DormSys Architecture / Governance Review
- Last Updated: 2026-07-11
