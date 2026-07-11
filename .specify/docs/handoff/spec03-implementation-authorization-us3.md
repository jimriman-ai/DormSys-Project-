# Spec03 US3 Implementation Authorization

**Artifact class:** Implementation Authorization (Authority Map instance)  
**Spec:** `003-employee-context` / catalog `spec03`  
**User story:** US3 — Dependent Records  
**Decision date:** 2026-07-11  

**Canonical map:** `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`  
**Lifecycle:** `.specify/governance/_meta/authority-model.md` §4–§5  
**Execution:** `.specify/governance/execution-policy.md`  
**Preconditions:**  
[`.specify/docs/handoff/completion-wave-plan.md`](./completion-wave-plan.md) ·  
[`.specify/docs/handoff/spec03-readiness-package.md`](./spec03-readiness-package.md) ·  
[`.specify/docs/handoff/spec03-post-mvp-authorization.md`](./spec03-post-mvp-authorization.md)

This artifact is **governance authorization preparation**. It does **not** implement US3, modify application/test/contract code, replace Request Dependent integration, create UI artifacts, or reopen other Specs.

---

## Final package status

| Field | Value |
| ----- | ----- |
| **STATUS** | **`SPEC03_US3_IMPLEMENTATION_AUTHORIZED`** |
| **Meaning** | Governance Review activated this Implementation Authorization for verbatim scope T035–T040 |
| **Coding permitted now?** | **Yes** — only for `authorized-scope` T035–T040 under execution-policy Pre-Execution Requirements; all `blocked-scope` remains HALT |

Clarification outcome alternative (not selected): `AUTHORIZATION_REQUIRES_CLARIFICATION` — **not applicable**; US3 T035–T040 scope and evidence gaps are unambiguous.

---

## Authorization Record Fields (authority-model §4)

| Field | Value |
| ----- | ----- |
| `authorization-status` | **`active`** |
| `authorized-by` | Governance Review |
| `effective-date` | **2026-07-11** |
| `supersedes` | US3 / T035+ **hold** in [`spec03-post-mvp-authorization.md`](./spec03-post-mvp-authorization.md) — **US3 hold only**; does not reopen or revoke US1/US2 closed scope |
| `superseded-by` | — |
| `authorized-scope` | **T035, T036, T037, T038, T039, T040** *(verbatim — no inference)* |
| `blocked-scope` | T041–T048 (US4); T049–T052 (EmployeeRead); T053–T058 except quality on US3-touched files if separately noted at review; Request adapter / stub replacement; UI / Livewire HR admin; Spec04 / Spec07 reopen; cross-module live integration; Workflow activation |
| `blocking-reason` | Product-core Batch 1 is Employee-internal Dependent only; Request live wiring requires Integration Readiness Gate + separate Integration Implementation Authorization; US4 requires separate evidence-gap IA |
| `authority-constraints` | Cannot modify AP-\*; cannot override CD-\*; cannot expand Spec03 beyond T035–T040; cannot bypass review gates; cannot replace accepted Request contracts; cannot authorize UI Feature Contracts |
| `lifecycle-reference` | `.specify/governance/_meta/authority-model.md` §5 |

**Invariant note (I1):** This record is the sole `active` Spec03 Implementation Authorization for US3. Prior Wave 1B authorization is **complete** (terminal for T027–T034) and is not reactivated by this record.

---

## 1. Authorization Decision

| Field | Value |
| ----- | ----- |
| **Decision** | Spec03 **US3 Dependent** Implementation Authorization **activated** |
| **Package status** | **`SPEC03_US3_IMPLEMENTATION_AUTHORIZED`** |
| **Implementation execution** | **Permitted** for verbatim `authorized-scope` T035–T040 only |
| **Rationale** | Completion Wave Batch 1; readiness package confirms US3 missing and Employee-internal; CD-009 Dependent ∈ Employee; design artifacts exist in `specs/003-employee-context/`; post-MVP US3 hold superseded for T035–T040 only |
| **Activation** | Governance Review · effective-date **2026-07-11** · Change Log `catalog-decisions.md` § 2.8.3 · checkpoint `spec03-us3-implementation-authorization` |

**Activated permissions:**

- US3 hold lifted for **T035–T040 only**
- Employee-module Dependent domain, persistence, Application actions, provider binding, and required Employee tests permitted
- Request `DependentSnapshotSourceStub` and all cross-module live wiring remain **unauthorized**

---

## 2. Scope

### Authorized scope (verbatim)

| Task | Summary |
| ---- | ------- |
| **T035** | Migration `employee_dependents` per `data-model.md` (`employee_id` FK → `employee_employees`; **no** `request_id`) |
| **T036** | `Dependent` entity + `DependentModel` + `EmployeeModel::dependents()` / `belongsTo` |
| **T037** | `DependentRepositoryContract` + Eloquent `DependentRepository` |
| **T038** | `AddDependentAction` + `UpdateDependentAction` (`NationalCode` when provided) |
| **T039** | Feature test `tests/Feature/Modules/Employee/DependentTest.php` |
| **T040** | Bind `DependentRepositoryContract` in `EmployeeServiceProvider` |

### Module / path boundaries (on activation)

| Allowed path class | Examples |
| ------------------ | -------- |
| Employee Domain | `app/Modules/Employee/Domain/Entities/Dependent.php` (+ exceptions if required by entity rules) |
| Employee Persistence | `database/migrations/modules/employee/*_create_employee_dependents_table.php`; `DependentModel`; relation updates on `EmployeeModel` |
| Employee Application | `DependentRepositoryContract`; repository; `AddDependentAction`; `UpdateDependentAction` |
| Employee Provider | `EmployeeServiceProvider` binding for Dependent repository only |
| Employee Tests | `tests/Feature/Modules/Employee/DependentTest.php` (+ minimal Unit tests if required by T038/T039) |

### Explicitly out of authorized scope

- T041+ (US4 Eligibility)
- T049+ (EmployeeReadContract)
- Phase F / Livewire HR admin / any UI Feature Contract
- Any `app/Modules/Request/**` change
- Any `app/Integrations/**` Dependent-related adapter
- Spec04, Spec05 redesign, Spec07, Workflow

---

## 3. Allowed Changes

When `authorization-status` is `active` for this scope, implementers **may**:

1. Create Dependent **domain entity** and supporting domain types strictly required by T035–T040 / `data-model.md`.
2. Create **required persistence** (`employee_dependents` migration, model, intra-Employee relations only).
3. Create **Employee-owned Dependent Application capability** (repository contract/impl, add/update actions, list/query as needed for T039).
4. Bind repository in **Employee** service provider only.
5. Add **required Employee-module tests** for Dependent ownership and validation.
6. Apply Pint / PHPStan fixes **only** on files touched for T035–T040.
7. Follow existing Spec03 design: `spec.md` US3, `data-model.md` §4, `tasks.md` Phase 5 — **no redesign**.

CD-009 preserved: Dependent lifecycle owned by Employee; Request continues to own snapshots only.

---

## 4. Forbidden Changes

Implementers **must not**:

| Forbidden | Reason |
| --------- | ------ |
| Replace `DependentSnapshotSourceStub` or bind live Request Dependent source | Cross-module; IRG + Integration IA required |
| Modify Request contracts, actions, providers, or FamilyDirect flows | Out of scope; do not modify Request integration |
| UI / Livewire / Blade / Feature Contract work | UI deferred; Phase F not authorized |
| US4 eligibility completion (T041–T048) | Separate evidence-gap IA |
| `EmployeeReadContract` (T049–T052) | Not in authorized-scope |
| Cross-module live integration (Allocation, Request, Dormitory, etc.) | IRG-gated; not Batch 1 |
| Add `request_id` on Dependent | Deferred per data-model |
| Reopen Spec04 / Spec07 / closed Specs | Completion Wave non-goal |
| Expand or replace accepted Application contracts used by Request | Do not replace contracts |
| Cross-module FKs or Eloquent across module boundaries | AP-04 / CD-012 style boundaries |
| Skip tasks or invent scope beyond T035–T040 | Scope lock |

---

## 5. Dependencies

| Dependency | Status | Gate |
| ---------- | ------ | ---- |
| Spec03 US1 Employee aggregate | Complete | Prerequisite satisfied |
| Spec03 US2 Department | Complete | Not required for Dependent; no reopen |
| `DependentId` / `DependentRelationship` | Present | Reuse; do not redesign |
| `data-model.md` Dependent section | Present | Binding design input |
| Spec03 post-MVP hold (US3) | Active hold until this IA activates | Supersedes US3 hold only on activation |
| Completion Wave Plan | `COMPLETION_WAVE_READY` | Program context |
| Spec03 Readiness Package | `READY_FOR_US3_IMPLEMENTATION_AUTHORIZATION` | Evidence basis |
| Request Dependent stub | Present | **Must remain** during US3 |
| Spec04 / Spec07 | Closed | Do not reopen |
| Nomination Record | N/A | Resuming held Spec03 scope — not a next-spec Case C trigger |

---

## 6. Test Requirements

On activation, US3 DoD requires:

| Requirement | Detail |
| ----------- | ------ |
| Feature coverage | `DependentTest` — add, list, update |
| Ownership | Dependent requires parent Employee; reject orphan |
| Validation | `NationalCode` when provided |
| Boundary | No Request/Allocation Infrastructure imports in Employee Dependent paths |
| Architecture | No Identity Infrastructure imports (BT-05 regression if Employee files added) |
| Quality | PHPStan level 8 clean for touched Employee paths; Pint on touched files |
| Independence | Tests must not require Request module or live Dependent snapshot adapter |

Request / FamilyDirect tests remain on stub fixtures; **do not** change them under this authorization.

---

## 7. Integration Readiness Gate Requirement

| Question | Answer for this authorization |
| -------- | ----------------------------- |
| Does US3 Employee-internal work require IRG? | **No** |
| Does replacing Request Dependent stub require IRG? | **Yes** — **after** US3 DoD; **not authorized here** |
| May this IA issue Integration Implementation Authorization? | **No** |

**Post-US3 (separate program step — not this record):**

```text
Consumer: Request Module
Required capability: DependentSnapshotSourceContract::findSnapshotForDependent(employeeId, sourceDependentId)
Accepted provider contract: Employee Application Dependent read surface (must exist; may be added only under a future authorized scope if missing from T035–T040)
Mapping: thin adapter only
IRG outcomes: READY_FOR_INTEGRATION_IMPLEMENTATION_AUTHORIZATION | INTEGRATION_AUTHORIZATION_BLOCKED
```

Until IRG PASS + Integration Implementation Authorization:

- `DependentSnapshotSourceStub` remains authoritative binding
- No `app/Integrations/` Dependent bridge under this US3 IA

Pattern reference: `.specify/governance/patterns/integration-readiness-gate.md`  
Template (future): `.specify/templates/integration-implementation-authorization-template.md`

---

## 8. Definition of Done

### Package DoD (this artifact — preparation)

- [x] Authorized-scope enumerated verbatim (T035–T040)
- [x] Blocked-scope and forbidden changes explicit
- [x] IRG posture recorded
- [x] Dependencies and test requirements recorded
- [x] Relationship to post-MVP US3 hold documented
- [x] Package status = `READY_FOR_IMPLEMENTATION_AUTHORIZATION`

### Implementation DoD (only after `authorization-status: active`)

- [ ] T035–T040 complete per `specs/003-employee-context/tasks.md`
- [ ] US3 acceptance scenarios pass (add/update/list; ownership)
- [ ] Required tests green
- [ ] PHPStan L8 + Pint on touched Employee paths
- [ ] No Request / Integrations / UI files modified
- [ ] No Spec04 / Spec07 / US4 scope delivered under this IA
- [ ] Batch review gate report produced; HALT for human approval before next batch

### Explicit non-claims

This authorization (even when activated) does **not** claim:

- Spec03 fully complete
- Request Family Dependent live integration complete
- UI readiness for Employee / Dependent surfaces
- US4 eligibility complete

---

## Activation Checklist (Governance Review)

| Step | Status |
| ---- | ------ |
| 1. Confirm package readiness / no new blockers | **PASS** — 2026-07-11 |
| 2. Set `authorization-status` → `active` | **DONE** |
| 3. Set `authorized-by` / `effective-date` | **DONE** — Governance Review · 2026-07-11 |
| 4. Change Log entry (`authority-model` §5 create) | **DONE** — `catalog-decisions.md` § 2.8.3 |
| 5. Execution of T035–T040 under Pre-Execution Requirements | **ALLOWED** (next step — not performed by this review) |

Review artifact: [`spec03-implementation-authorization-review.md`](./spec03-implementation-authorization-review.md)

---

## References

- `specs/003-employee-context/spec.md` — US3
- `specs/003-employee-context/tasks.md` — Phase 5 T035–T040
- `specs/003-employee-context/data-model.md` — Dependent
- CD-009 — Dependent ∈ Employee
- `.specify/docs/handoff/completion-wave-plan.md`
- `.specify/docs/handoff/spec03-readiness-package.md`
- `.specify/docs/handoff/spec03-post-mvp-authorization.md`

---

## Document Control

- Version: 1.1.0
- Status: **`SPEC03_US3_IMPLEMENTATION_AUTHORIZED`** (`authorization-status: active`)
- Owner: DormSys Architecture / Governance Review
- Last Updated: 2026-07-11
- Change: Governance Review activation — `pending_activation` → `active`; scope T035–T040 unchanged
