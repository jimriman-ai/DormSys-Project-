# Spec03 US4 Batch 1b — Implementation Authorization Decision

**Artifact class:** Implementation Authorization (Authority Map instance)  
**Spec:** `003-employee-context` / catalog `spec03`  
**User story:** US4 — Eligibility Computation (Batch 1b gap fill)  
**Decision date:** 2026-07-11  
**Checkpoint:** `spec03-us4-batch1b-implementation-authorization-decision`

**Canonical map:** `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`  
**Lifecycle:** `.specify/governance/_meta/authority-model.md` §4–§5  
**Execution:** `.specify/governance/execution-policy.md`

**This artifact authorizes implementation execution for the declared scope only. It does not implement code, create Quickstart, or expand beyond Batch 1b gap fill.**

---

## 1. Status

| Field | Value |
| ----- | ----- |
| **Status** | **`SPEC03_US4_BATCH1B_IMPLEMENTATION_AUTHORIZED`** |
| **Coding permitted now?** | **Yes** — only within verbatim `authorized-scope` below |
| **Package meaning** | Governance Review grants Implementation Authorization for Spec03 US4 Batch 1b evidence-gap fill |

---

## 2. Work Item

**Spec03 US4 Eligibility — Batch 1b**

| Field | Value |
| ----- | ----- |
| Full name | Spec03 US4 Eligibility — Batch 1b evidence gap analysis |
| Approach | Gap-only fill of proven missing/defective eligibility supplier items |
| CD | CD-013 (Employee computes; Request enforces) |

---

## 3. Evidence Reviewed

| Artifact | Role |
| -------- | ---- |
| [spec03-us4-batch1b-implementation-authorization-prep.md](./spec03-us4-batch1b-implementation-authorization-prep.md) | Prep — `SPEC03_US4_BATCH1B_IMPLEMENTATION_AUTHORIZATION_PREPARED` |
| [spec03-us4-batch1b-authorization-review.md](./spec03-us4-batch1b-authorization-review.md) | Prep review — `SPEC03_US4_BATCH1B_READY_FOR_AUTHORIZATION_APPROVAL` |
| [spec03-us4-eligibility-feature-analysis.md](./spec03-us4-eligibility-feature-analysis.md) | Feature Analysis — `FEATURE_ANALYSIS_COMPLETED` |
| [spec03-us4-eligibility-feature-analysis.review-decision.md](./spec03-us4-eligibility-feature-analysis.review-decision.md) | `ACCEPTED_FOR_IMPLEMENTATION_AUTHORIZATION_PREP` |
| [spec03-us3-completion-handoff.md](./spec03-us3-completion-handoff.md) | US3 closed; US4 separate |
| [request-dependent-owner-decision-record.md](./request-dependent-owner-decision-record.md) | D-01–D-03 exclusions |
| [completion-wave-plan.md](./completion-wave-plan.md) | Batch 1b sequencing |
| [spec03-post-mvp-authorization.md](./spec03-post-mvp-authorization.md) | Predecessor US4 hold |
| `specs/003-employee-context/spec.md` / `tasks.md` / contracts | Design baseline |
| `.specify/docs/catalog-decisions.md` | CD-013; Authority Map |
| `.specify/docs/spec-catalog.md` | Spec03 inventory |

---

## Authorization Record Fields (authority-model §4)

| Field | Value |
| ----- | ----- |
| `authorization-status` | **`active`** |
| `authorized-by` | Governance Review |
| `effective-date` | **2026-07-11** |
| `supersedes` | US4 / T041+ **hold** in [`spec03-post-mvp-authorization.md`](./spec03-post-mvp-authorization.md) — **only** for the verbatim `authorized-scope` below; does not revoke US1/US2/US3 closed or active scopes |
| `superseded-by` | — |
| `authorized-scope` | See §5 *(verbatim — no inference)* |
| `blocked-scope` | See §7 |
| `blocking-reason` | Gap-only Batch 1b; preserve Request consumer signature + live pending-request bridge; Dependent live path deferred (D-01–D-03); live Allocation binding requires separate IRG/IA; EmployeeRead / UI out of wave |
| `authority-constraints` | Cannot modify AP-\*; cannot override CD-\*; cannot expand beyond `authorized-scope`; cannot break Request eligibility signature; cannot replace live `PendingRequestReadBridge`; cannot authorize Dependent live integration, UI Feature Contracts, Spec04/Spec07 reopen, or live Allocation adapters |
| `lifecycle-reference` | `.specify/governance/_meta/authority-model.md` §5 |

**Invariant note:** Spec03 US3 Implementation Authorization ([spec03-implementation-authorization-us3.md](./spec03-implementation-authorization-us3.md)) remains terminal/complete for T035–T040 and is **not** expanded by this record. This record is the active Spec03 Implementation Authorization for **US4 Batch 1b gap fill only**.

**Activation log:** `catalog-decisions.md` Change Log **§ 2.8.4**.

---

## 4. Decision Summary

| Field | Value |
| ----- | ----- |
| **Decision** | Implementation authorization **granted** |
| **Status** | **`SPEC03_US4_BATCH1B_IMPLEMENTATION_AUTHORIZED`** |
| **Rationale** | Prep review `READY_FOR_AUTHORIZATION_APPROVAL`; scope limited to Batch 1b; boundaries/tests/non-goals explicit; no unresolved architecture/authority blocker; Decision Rules 1–6 satisfied |

**Implementation may begin only within the approved scope.**

---

## 5. Approved Scope (verbatim)

| ID | Authorized deliverable |
| -- | ---------------------- |
| **T041** | Create `EligibilityReasonCode` enum in `app/Modules/Employee/Domain/Enums/EligibilityReasonCode.php` per design reason codes (`employee_inactive`, `active_allocation_exists`, `pending_request_exists`) |
| **T043-AA** | Create `ActiveAllocationReadPort` in `app/Modules/Employee/Application/Contracts/Ports/ActiveAllocationReadPort.php` only — **do not** recreate or re-authorize `PendingRequestReadPort` |
| **T044-NA** | Create `NullActiveAllocationReadAdapter` in `app/Modules/Employee/Infrastructure/Adapters/NullActiveAllocationReadAdapter.php` — always returns `false` — **do not** create/force `NullPendingRequestReadAdapter` or reverse live pending bridge |
| **T045** | Implement `EligibilityCalculator` in `app/Modules/Employee/Domain/Services/EligibilityCalculator.php` — evaluate employee active + port checks; return stable reason codes |
| **T047-AA** | Gap-fill `EmployeeEligibilityService`: use calculator + ActiveAllocation check for `active_allocation_exists`; bind `ActiveAllocationReadPort` → `NullActiveAllocationReadAdapter` in `EmployeeServiceProvider` — **preserve** `computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null)` and existing live `PendingRequestReadPort` binding |
| **T048** | Feature test `tests/Feature/Modules/Employee/EmployeeEligibilityContractTest.php` — active eligible; inactive → `employee_inactive`; mock ActiveAllocation `true` → `active_allocation_exists`; mock/bind PendingRequest → `pending_request_exists` |
| **DOC-OPT** | **Optional** editorial sync of `specs/003-employee-context/contracts/employee-eligibility-service.md` and/or `internal-read-ports.md` to accepted runtime signatures (`string` + `excludingRequestId`) — documentation only; **not** a runtime API rewrite; may be deferred without blocking core T041/T043-AA/T044-NA/T045/T047-AA/T048 DoD |

**Accepted consumer truth (mandatory):**

- Keep runtime `EmployeeEligibilityContract::computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null)`.
- Keep matching `PendingRequestReadPort` signature and live `PendingRequestReadBridge` binding.
- Enum/DTO changes must preserve Request-compatible eligibility outcomes.

**Not authorized as greenfield recreate:** T042 `EligibilityResultDTO`; full T043/T044 Pending path; T046 signature rewrite to `EmployeeId`-only.

---

## 6. Allowed Boundaries

| Layer | Allowed |
| ----- | ------- |
| Employee Domain | `EligibilityReasonCode`; `EligibilityCalculator`; domain exceptions only if required by calculator |
| Employee Application | `ActiveAllocationReadPort`; gap-fill `EmployeeEligibilityService`; DTO reason typing only if needed for enum **without** breaking Request consumers |
| Employee Infrastructure | `NullActiveAllocationReadAdapter`; `EmployeeServiceProvider` ActiveAllocation → Null bind only |
| Spec03 contract markdown | DOC-OPT editorial only |
| Employee tests | `EmployeeEligibilityContractTest` (+ minimal unit tests for calculator/enum if required) |
| Pint / PHPStan | Fixes **only** on files touched for this `authorized-scope` |

**Illustrative paths:**

- `app/Modules/Employee/Domain/Enums/EligibilityReasonCode.php`
- `app/Modules/Employee/Domain/Services/EligibilityCalculator.php`
- `app/Modules/Employee/Application/Contracts/Ports/ActiveAllocationReadPort.php`
- `app/Modules/Employee/Infrastructure/Adapters/NullActiveAllocationReadAdapter.php`
- `app/Modules/Employee/Application/Services/EmployeeEligibilityService.php`
- `app/Modules/Employee/Infrastructure/Providers/EmployeeServiceProvider.php`
- `tests/Feature/Modules/Employee/EmployeeEligibilityContractTest.php`
- Optionally: Spec03 eligibility/port contract markdown (DOC-OPT)

---

## 7. Explicit Non-Goals / Blocked Scope

| Blocked item | Rule |
| ------------ | ---- |
| T042 greenfield recreate | Already present |
| Recreating/re-authorizing `PendingRequestReadPort` as Null | Live bridge preserved |
| Replacing `PendingRequestReadBridge` / `IntegrationServiceProvider` pending bind | Forbidden |
| Changing public eligibility method to `EmployeeId`-only Wave 1A form | Would break Request |
| T049–T052 EmployeeRead | Hold |
| Request Dependent stub replacement / live Dependent integration | Owner D-01 |
| Inventing `DependentSnapshotReadDTO.eligible` | Owner D-02 |
| Employee Dependent Application read contract | Owner D-03 |
| Live Allocation Integrations / Spec07 reopen | Separate IRG/IA |
| UI / Livewire / Feature Contracts / Quickstart / Implementation Lock as UI path | Out of Batch 1b |
| Spec04 reopen; Request enforcement redesign; Voucher eligibility | Different scopes |
| Wholesale “complete all Phase 6 checkboxes” beyond §5 | Forbidden inference |
| Any `app/Modules/Request/**` behavioral change | Consumer preserve only |

---

## 8. Required Tests and Quality Gates

| Gate | Requirement |
| ---- | ----------- |
| Feature | `EmployeeEligibilityContractTest` scenarios in §5 T048 |
| Regression | Request eligibility / pending-request tests remain green |
| Consumer | Runtime signature unchanged |
| Bridge | Live `PendingRequestReadPort` binding unchanged |
| PHPStan | `php vendor/bin/phpstan analyse --no-progress` on touched Employee paths — zero errors |
| Pint | `php vendor/bin/pint` on touched files |
| Architecture | No Dependent live wiring; no cross-module Domain/Infrastructure leakage |
| SC-004 | Deterministic eligibility fixture results |

---

## 9. Next Allowed Governance Step

**Implementation may begin only within the approved scope.**

Execution order (execution-policy):

1. Implement verbatim `authorized-scope` (§5).  
2. Satisfy §8 quality gates.  
3. Record Batch 1b implementation review / completion handoff when DoD met.  
4. HALT auto-progression — EmployeeRead, Dependent live IRG reopen, and UI remain separately gated.

**Not next without further authority:** Quickstart as a substitute for DoD; Feature Contract; Request Dependent live integration; live Allocation adapter.

---

## Decision Rules Checklist

| Rule | Result |
| ---- | ------ |
| 1. Scope limited to Spec03 US4 Batch 1b | **Pass** |
| 2. Authorization review ready for approval | **Pass** — `SPEC03_US4_BATCH1B_READY_FOR_AUTHORIZATION_APPROVAL` |
| 3. Allowed boundaries explicit | **Pass** |
| 4. Tests / quality gates explicit | **Pass** |
| 5. No unresolved blocker | **Pass** |
| 6. No forbidden dependency/scope smuggled | **Pass** |

---

## 10. No-Change Confirmation

**No application, test, UI, contract, or implementation files were modified.**

(This decision activates governance authority only. Application/test/contract **code** changes occur only in a subsequent implementation batch under this `authorized-scope`.)

---

## Document Control

- Version: 1.0.0  
- Status: **`SPEC03_US4_BATCH1B_IMPLEMENTATION_AUTHORIZED`**  
- `authorization-status`: **`active`**  
- Work item: Spec03 US4 Eligibility — Batch 1b  
- Owner: Governance Review  
- Last Updated: 2026-07-11  
- Catalog Change Log: § 2.8.4
