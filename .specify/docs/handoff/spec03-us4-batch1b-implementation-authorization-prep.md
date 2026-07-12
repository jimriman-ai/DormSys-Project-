# Spec03 US4 Batch 1b — Implementation Authorization Preparation

**Artifact type:** Implementation Authorization **Preparation** (non-authorizing)  
**Spec:** `003-employee-context` / catalog `spec03`  
**User story:** US4 — Eligibility Computation (Batch 1b evidence-gap fill)  
**Prepared:** 2026-07-11  
**Checkpoint:** `spec03-us4-batch1b-implementation-authorization-prep`

**Canonical map:** `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`  
**Lifecycle:** `.specify/governance/_meta/authority-model.md` §4–§5  
**Execution:** `.specify/governance/execution-policy.md`

**This artifact prepares a future Implementation Authorization record. It does NOT activate Implementation Authorization, permit coding, create Quickstart, or create an Implementation Lock.**

Convention reference (active IA pattern, not this prep): [spec03-implementation-authorization-us3.md](./spec03-implementation-authorization-us3.md).

---

## 1. Status

| Field | Value |
| ----- | ----- |
| **Status** | **`SPEC03_US4_BATCH1B_IMPLEMENTATION_AUTHORIZATION_PREPARED`** |
| **Coding permitted now?** | **No** |
| **`authorization-status` (Authority Map)** | **Not set** — no Authorization Record activated by this document |
| **Package meaning** | Candidate scope, boundaries, tests, and blockers are ready for human IA approval |

---

## 2. Work Item

**Spec03 US4 Eligibility — Batch 1b evidence gap analysis**

| Field | Value |
| ----- | ----- |
| Completion Wave | Batch 1b |
| Approach | Gap-only fill of proven missing/defective eligibility supplier items |
| Not | Wholesale greenfield re-delivery of already-present T042 / full T046 / live pending-request path |

---

## 3. Governing Decision Reference

| Artifact | Value |
| -------- | ----- |
| Review decision | [spec03-us4-eligibility-feature-analysis.review-decision.md](./spec03-us4-eligibility-feature-analysis.review-decision.md) |
| Review status | `FEATURE_ANALYSIS_REVIEW_ACCEPTED` |
| Decision | **`ACCEPTED_FOR_IMPLEMENTATION_AUTHORIZATION_PREP`** |
| Feature analysis | [spec03-us4-eligibility-feature-analysis.md](./spec03-us4-eligibility-feature-analysis.md) (`FEATURE_ANALYSIS_COMPLETED`) |
| Upstream selection | [selected-next-approved-work-item.md](./selected-next-approved-work-item.md) |
| US3 closed | [spec03-us3-completion-handoff.md](./spec03-us3-completion-handoff.md) (`SPEC03_US3_COMPLETED`) |
| Dependent path deferred | [request-dependent-owner-decision-record.md](./request-dependent-owner-decision-record.md) (D-01–D-03) |
| Program plan | [completion-wave-plan.md](./completion-wave-plan.md) |
| CD-013 | `.specify/docs/catalog-decisions.md` |

---

## 4. Candidate Scope

### Q1. Exact authorized candidate scope

**Proposed verbatim `authorized-scope` for a future IA record** (candidates — finalize at approval; no inference beyond this list):

| ID | Candidate item | Notes |
| -- | -------------- | ----- |
| **T041** | `EligibilityReasonCode` enum | Prefer enum over indefinite string-only interim (review R3) |
| **T043-partial** | `ActiveAllocationReadPort` only | Do **not** re-authorize existing `PendingRequestReadPort` |
| **T044-partial** | `NullActiveAllocationReadAdapter` only | Do **not** force `NullPendingRequestReadAdapter` / replace live bridge |
| **T045** | `EligibilityCalculator` domain service | Extract active + port rules from Application service (review R4) |
| **T047-partial** | Wire ActiveAllocation into `EmployeeEligibilityService` + bind Null adapter in `EmployeeServiceProvider` | Preserve runtime method signature and live pending-request binding |
| **T048** | `tests/Feature/Modules/Employee/EmployeeEligibilityContractTest.php` | Employee-module supplier coverage; do not weaken Request tests |
| **DOC-optional** | Editorial sync of Spec03 eligibility/port contract markdown to accepted runtime signatures | Documentation only; not a runtime API rewrite |

**Accepted consumer truth (binding constraint if IA is later granted) — review R1:**

- Keep `EmployeeEligibilityContract::computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null)`
- Keep matching `PendingRequestReadPort` string + `excludingRequestId` form
- Do **not** restore Wave 1A design-doc `EmployeeId`-only signature in a way that breaks Request

**Present / do-not-reauthorize-as-greenfield:**

- T042 `EligibilityResultDTO` (already present)
- Existing `EmployeeEligibilityContract` / `EmployeeEligibilityService` skeleton (gap-fill only)
- Live `PendingRequestReadBridge` + `IntegrationServiceProvider` pending binding

---

## 5. Allowed Boundaries

### Q2. Allowed implementation boundaries (if later authorized)

| Layer | Allowed if IA activated |
| ----- | ----------------------- |
| Employee Domain | `EligibilityReasonCode`; `EligibilityCalculator`; related domain exceptions only if required by calculator |
| Employee Application | Port interface `ActiveAllocationReadPort`; gap-fill `EmployeeEligibilityService` to call ActiveAllocation + calculator; DTO reason typing only if required by enum without breaking consumers |
| Employee Infrastructure | `NullActiveAllocationReadAdapter`; `EmployeeServiceProvider` bind ActiveAllocation → Null adapter |
| Spec03 contract markdown (optional) | Editorial alignment to accepted runtime signatures only |
| Employee tests | `EmployeeEligibilityContractTest` (+ minimal unit tests for calculator/enum if needed) |

**Path classes (illustrative):**

- `app/Modules/Employee/Domain/Enums/EligibilityReasonCode.php`
- `app/Modules/Employee/Domain/Services/EligibilityCalculator.php`
- `app/Modules/Employee/Application/Contracts/Ports/ActiveAllocationReadPort.php`
- `app/Modules/Employee/Infrastructure/Adapters/NullActiveAllocationReadAdapter.php`
- `app/Modules/Employee/Application/Services/EmployeeEligibilityService.php` (gap fill only)
- `app/Modules/Employee/Infrastructure/Providers/EmployeeServiceProvider.php` (ActiveAllocation bind only)
- `tests/Feature/Modules/Employee/EmployeeEligibilityContractTest.php`
- Optionally: `specs/003-employee-context/contracts/employee-eligibility-service.md`, `internal-read-ports.md` (editorial)

---

## 6. Explicit Non-Goals

### Q3. Outside scope

| Non-goal | Reason |
| -------- | ------ |
| Coding under this prep artifact | Prep only |
| Active Implementation Authorization | Pending separate IA activation |
| T042 greenfield recreate | Already present |
| Replacing `PendingRequestReadBridge` / forcing NullPending | Accepted live path |
| Changing public eligibility signature away from accepted consumer form | Would break Request (R1) |
| T049–T052 EmployeeRead | Review R5 exclude |
| Request Dependent stub replacement / live Dependent integration | Owner D-01 |
| `DependentSnapshotReadDTO.eligible` invention | Owner D-02 |
| Employee Dependent Application read contract | Owner D-03 |
| Live Allocation port binding / Spec07 reopen | Separate IRG/IA (R2) |
| UI / Livewire / Feature Contracts / Quickstart / Implementation Lock | Out of Batch 1b backend path |
| Spec04 reopen; Request enforcement redesign; Voucher eligibility | Different scopes |
| Wholesale Phase 6 checkbox completion without gap evidence | Forbidden by Completion Wave |

---

## 7. Expected Impacted Areas

### Q4. Likely files / modules (inspection only — not modified now)

| Area | Likely touch |
| ---- | ------------ |
| `app/Modules/Employee/Domain/` | New enum + calculator |
| `app/Modules/Employee/Application/Contracts/Ports/` | New ActiveAllocation port |
| `app/Modules/Employee/Application/Services/EmployeeEligibilityService.php` | Wire calculator + ActiveAllocation |
| `app/Modules/Employee/Infrastructure/Adapters/` | NullActive adapter |
| `app/Modules/Employee/Infrastructure/Providers/EmployeeServiceProvider.php` | Bind ActiveAllocation → Null |
| `tests/Feature/Modules/Employee/` | New eligibility contract test |
| Spec03 contracts markdown (optional) | Editorial sync |
| **Must not touch** | `app/Modules/Request/**` (except unchanged consumer compatibility); Dependent snapshot stub; live Allocation Integrations; UI |

Existing preserved surfaces (verify, do not reverse):

- `app/Integrations/Request/PendingRequestReadBridge.php`
- `app/Providers/IntegrationServiceProvider.php` pending bind
- Request tests consuming `EmployeeEligibilityContract`

---

## 8. Required Tests and Quality Gates

### Q6. If authorization is later granted

| Gate | Expectation |
| ---- | ----------- |
| Feature | `EmployeeEligibilityContractTest` — active eligible (Null ActiveAllocation false); inactive → `employee_inactive`; mock ActiveAllocation true → `active_allocation_exists`; mock/bind PendingRequest true → `pending_request_exists` |
| Regression | Existing Request eligibility / pending-request tests remain green; no signature break |
| Consumer preserve | Runtime `computeRequestEligibility(string, ?string)` unchanged |
| Bridge preserve | Live `PendingRequestReadPort` binding unchanged |
| Static analysis | `php vendor/bin/phpstan analyse --no-progress` on touched Employee paths — zero errors |
| Formatting | `php vendor/bin/pint` on touched files |
| Architecture | No new cross-module Domain/Infrastructure leakage; no Request Dependent wiring |
| SC-004 | Deterministic fixture scenarios for eligibility API |

---

## 9. Risks / Open Questions / Blockers

### Q5 / Q7. Preconditions and risks before approval

**Preconditions already true (evidence-backed):**

- Spec03 US3 complete; US4 hold remains until separate IA  
- Feature Analysis complete; Review accepted for IA prep  
- Partial eligibility supplier already present and consumed by Request  
- Request Dependent live integration deferred (independent path)  
- Design artifacts exist under `specs/003-employee-context/`  

**Risks / items approval must confirm:**

| ID | Item | Severity |
| -- | ---- | -------- |
| **P1** | Final IA must use **verbatim** `authorized-scope` enumeration (Authority Map I7/I9 pattern) — no “do all open Phase 6 checkboxes” inference | High |
| **P2** | Optional DOC editorial sync must not be mistaken for runtime contract replacement authority | Medium |
| **P3** | `EligibilityResultDTO` currently uses `list<string>` reason codes — enum introduction must preserve Request-compatible outcomes | Medium |
| **P4** | ActiveAllocation Null enables tests but does **not** authorize live Allocation truth | Medium |
| **P5** | Concurrent US3 IA remains active for Dependent only — US4 IA must not conflict or expand US3 `authorized-scope` | Low |
| **P6** | Catalog Change Log / supersession note vs `spec03-post-mvp-authorization.md` US4 hold required when IA activates | Process |

**Blockers to coding now:** No active US4 Implementation Authorization record exists. This prep does not remove that HALT.

---

## 10. Pending Approval Decision

### Q8. Approval checkpoint remaining

**Implementation is NOT authorized.**

Pending human governance action:

1. Create and **activate** a Spec03 US4 Batch 1b Implementation Authorization record (Authority Map fields: `authorization-status`, `authorized-by`, `effective-date`, verbatim `authorized-scope`, `blocked-scope`, `blocking-reason`, constraints).  
2. Record catalog Change Log entry on activation (pattern: US3 § 2.8.3).  
3. Supersede US4 hold in `spec03-post-mvp-authorization.md` **only** for the declared scope.  

Until that activation:

| Check | Result |
| ----- | ------ |
| Coding of candidate gaps | **HALT** |
| Quickstart / Implementation Lock | **Not allowed** under this prep |
| Feature Contract | **Not the next gate** (explicitly rejected by review) |

**Proposed `blocked-scope` for future IA** (draft for approver):

- T042 greenfield; full T043/T044 Pending Null reversion; T046 signature rewrite to `EmployeeId`-only; T049–T052; Request Dependent stub replacement; Dependent `eligible` / Dependent read surface; live Allocation Integrations; UI / Feature Contracts; Spec04 / Spec07 reopen; wholesale Phase 6 remaining items not listed in §4

---

## Draft Authority Map Fields (for future IA only — not active)

| Field | Prepared value (inactive) |
| ----- | ------------------------- |
| `authorization-status` | *(pending)* — must become `active` or `partial` only after human approval |
| `authorized-scope` | See §4 candidate list — verbatim at activation |
| `blocked-scope` | See §10 draft |
| `blocking-reason` | Gap-only Batch 1b; preserve Request consumer + live pending bridge; Dependent live path deferred; live Allocation separate IRG |
| `authority-constraints` | Cannot override CD-\*; cannot break Request eligibility signature; cannot authorize Dependent live integration; cannot authorize UI Feature Contracts |

---

## 11. No-Change Confirmation

**No application, test, UI, contract, or implementation files were modified.**

---

## Document Control

- Version: 1.0.0  
- Status: **`SPEC03_US4_BATCH1B_IMPLEMENTATION_AUTHORIZATION_PREPARED`**  
- Work item: Spec03 US4 Eligibility — Batch 1b evidence gap analysis  
- Governing decision: `ACCEPTED_FOR_IMPLEMENTATION_AUTHORIZATION_PREP`  
- Next pending gate: **Activate Spec03 US4 Batch 1b Implementation Authorization** (human)  
- Owner: DormSys Architecture / Governance Review  
- Last Updated: 2026-07-11
