# Spec03 US4 Eligibility — Feature Analysis (Batch 1b Evidence Gap Package)

**Artifact type:** Feature analysis / evidence gap inventory (non-authorizing)  
**Analysis date:** 2026-07-11  
**Checkpoint:** `spec03-us4-eligibility-feature-analysis`  
**Prior gates:** `SELECTED_NEXT_APPROVED_WORK_ITEM_RECORDED` · `SELECTED_WORK_ITEM_NEXT_GATE_CONFIRMED` (next step = feature analysis)

**This artifact does not** grant Design Approval, Implementation Authorization, Quickstart, Batch Execution Permission, or coding authority.

---

## 1. Status

| Field | Value |
| ----- | ----- |
| **Status** | **`FEATURE_ANALYSIS_COMPLETED`** |

Confirmed next gate was Feature Analysis — no mismatch. No prior accepted US4 Batch 1b feature analysis existed.

---

## 2. Feature Name

**Spec03 US4 Eligibility — Batch 1b evidence gap analysis (governance planning)**

| Field | Value |
| ----- | ----- |
| Spec | `003-employee-context` / catalog `spec03` |
| User story | US4 — Eligibility Computation (P3) |
| Task range under assessment | **T041–T048** |
| Completion Wave batch | **Batch 1b** |
| Governing decisions | CD-013; Completion Wave Plan; Request Dependent owner D-01–D-03 (exclusion only) |

---

## 3. Problem Statement

### Q1. What exact user/business problem does this feature solve?

Request must **enforce** accommodation-submission eligibility (BR-01) without owning eligibility **computation**. Employee must supply a deterministic eligibility result (eligible / ineligible + stable reason codes) so Request can block illegal submissions (CD-013 / FR-007 / SC-004).

Batch 1b’s immediate problem is governance, not greenfield delivery: partial eligibility code already runs in production paths, while `tasks.md` Phase 6 remains open and design contracts drift from runtime. Without an evidence-backed gap inventory, Implementation Authorization would either re-authorize delivered work wholesale or invent scope (including breaking accepted Request consumer behavior).

---

## 4. Scope

### Q2. Exact in-scope behavior (this analysis / eventual Batch 1b fill)

**In scope for this Feature Analysis (governance):**

1. Inventory runtime Employee eligibility supplier vs T041–T048 and design contracts.  
2. Classify each task as **present**, **partial**, **missing**, or **superseded by accepted consumer behavior**.  
3. List **proven missing/defective** candidates only for a future scoped Implementation Authorization.  
4. Preserve accepted Request consumer usage of `EmployeeEligibilityContract`.  
5. Explicitly exclude deferred Request Dependent live integration and related owner decisions.

**In scope for eventual US4 product behavior (when separately authorized — not authorized by this analysis):**

| Behavior | Spec / CD source |
| -------- | ---------------- |
| Compute request eligibility for an employee | FR-007, CD-013 |
| Active employee + no blocking ports → eligible | US4 acceptance §1 |
| Inactive employee → ineligible `employee_inactive` | contract reason codes |
| Pending-request blocking via port | BR-01 partial; runtime already live |
| Active-allocation blocking via port (when port exists / authorized) | contract `active_allocation_exists` |
| Stable reason codes; empty codes when eligible | `EligibilityResultDTO` rules |
| Employee-local tests for supplier scenarios | T048 / SC-004 |

### Q3. Explicitly out of scope

| Out of scope | Reason |
| ------------ | ------ |
| Implementation / coding of T041–T048 under this artifact | Analysis only |
| Implementation Authorization / Quickstart | Separate human gates after review |
| Wholesale greenfield re-delivery of already-present supplier pieces | Prefer gap-only IA |
| Rewriting runtime `EmployeeEligibilityContract` signature to match Wave 1A markdown (`EmployeeId`-only) without consumer migration | Would break Request |
| Reverting live `PendingRequestReadBridge` to `NullPendingRequestReadAdapter` casually | Accepted live integration already present |
| Request Dependent stub replacement / live Dependent snapshot source | Owner D-01 |
| Inventing / mapping `DependentSnapshotReadDTO.eligible` | Owner D-02 |
| Employee Application Dependent read contract | Owner D-03 |
| EmployeeRead (T049–T052), UI / Livewire HR admin | Optional / deferred |
| Spec04 / Spec07 reopen; Allocation live port beyond Null stub authorization | Separate IRG/IA |
| Request enforcement redesign | Request already owns enforcement |
| Voucher eligibility | Different module / CD-016 |

---

## 5. Dependency Assessment

### Q4. Dependencies / assumptions

| Dependency / assumption | Status |
| ----------------------- | ------ |
| CD-013 — Employee computes; Request enforces | Accepted (Recorded Assumption) |
| Spec05 Request already consumes `EmployeeEligibilityContract` | Confirmed in code / tests |
| Live `PendingRequestReadPort` → `PendingRequestReadBridge` | Confirmed; preserve |
| Spec03 US1 Employee active/inactive | Delivered |
| Spec03 US3 Dependent | Delivered; **orthogonal** to eligibility supplier |
| Design contracts `employee-eligibility-service.md` / `internal-read-ports.md` | Design baseline; **not** automatic rewrite authority for runtime |
| Active allocation truth from Allocation | Future live binding requires separate IRG/IA; Null stub is Wave design default |
| Completion Wave Batch 1b sequencing | Evidence first → scoped IA only |

### Q5. Depends on Request Dependent live integration?

**No.**

Eligibility (CD-013) is a separate supplier path from FamilyDirect Dependent **snapshot sourcing** (`DependentSnapshotSourceContract` / stub). Owner decisions D-01–D-03 defer Dependent live wiring and forbid inventing Dependent snapshot `eligible`. This analysis does not require replacing `DependentSnapshotSourceStub`, resolving Dependent `eligible` semantics, or creating an Employee Dependent Application read surface.

---

## 6. Evidence Base

### Governance / design

| Artifact | Use |
| -------- | --- |
| `selected-next-approved-work-item.md` | Selected work item; analysis-ready |
| `selected-work-item-next-gate-confirmation.md` | Next step = feature analysis |
| `completion-wave-plan.md` | Batch 1b evidence → scoped IA |
| `spec03-readiness-package.md` | `REQUIRES_EVIDENCE_GAP_ANALYSIS`; partial US4 inventory |
| `spec03-us3-completion-handoff.md` | US4 Hold |
| `request-dependent-owner-decision-record.md` | D-01–D-03 exclusions |
| `specs/003-employee-context/spec.md` | US4, FR-007, SC-004 |
| `specs/003-employee-context/tasks.md` | T041–T048 |
| `contracts/employee-eligibility-service.md` | Design supplier API |
| `contracts/internal-read-ports.md` | Port design (Wave 1A stubs) |
| `.specify/docs/catalog-decisions.md` | CD-013 |
| `.specify/docs/spec-catalog.md` | Spec03 inventory |

### Code / test evidence (supporting; not modified)

| Evidence | Finding |
| -------- | ------- |
| `EmployeeEligibilityContract` | **Present** — runtime: `computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null)` (diverges from design `EmployeeId`-only) |
| `EmployeeEligibilityService` | **Present** — active check + pending-request port; **no** active-allocation check; logic inline (no `EligibilityCalculator`) |
| `EligibilityResultDTO` | **Present** — `reasonCodes` as `list<string>` |
| `EligibilityReasonCode` enum | **Missing** |
| `EligibilityCalculator` | **Missing** |
| `PendingRequestReadPort` | **Present** — string + `excludingRequestId` (diverges from design `EmployeeId`-only) |
| `ActiveAllocationReadPort` | **Missing** |
| `NullActiveAllocationReadAdapter` / `NullPendingRequestReadAdapter` | **Missing** (pending path uses live bridge instead) |
| `PendingRequestReadBridge` + `IntegrationServiceProvider` binding | **Present** — live |
| `EmployeeServiceProvider` | Binds `EmployeeEligibilityContract` → service; does **not** bind allocation port |
| `tests/Feature/Modules/Employee/EmployeeEligibilityContractTest.php` | **Missing** |
| Request tests (`PersonalRequestTest`, `PendingRequestReadPortTest`) | Consume / mock eligibility; support preserving consumer signature |

### Task gap matrix (T041–T048)

| Task | Evidence status | Batch 1b disposition |
| ---- | --------------- | -------------------- |
| **T041** `EligibilityReasonCode` | **Missing** — codes are bare strings in service | Candidate gap (enum or accept string codes formally) |
| **T042** `EligibilityResultDTO` | **Present** | Do **not** re-authorize as greenfield |
| **T043** ports | **Partial** — `PendingRequestReadPort` present; `ActiveAllocationReadPort` missing | Gap only for ActiveAllocation port (and signature alignment decision) |
| **T044** Null adapters | **Partial / superseded** — NullPending not used (live bridge); NullActive missing | Candidate: NullActive only; **do not** force NullPending |
| **T045** `EligibilityCalculator` | **Missing** — logic in Application service | Candidate gap (domain extraction) |
| **T046** contract | **Present** (runtime diverges from markdown) | Prefer consumer-compatible runtime; contract doc sync = editorial/governance, not silent rewrite |
| **T047** service + bind | **Partial** — service present; no ActiveAllocation wiring | Gap: wire ActiveAllocation when port authorized |
| **T048** Employee eligibility feature test | **Missing** as named Spec03 task | Candidate gap — add without weakening Request tests |

### Proven missing / defective candidates (for review → future IA)

1. `EligibilityReasonCode` enum (or explicit acceptance of string reason codes as runtime truth).  
2. `EligibilityCalculator` domain service extracting rules from Application service.  
3. `ActiveAllocationReadPort` + `NullActiveAllocationReadAdapter` + service check for `active_allocation_exists`.  
4. `EmployeeEligibilityContractTest` (Employee-module supplier coverage).  
5. Optional editorial sync of Spec03 contract markdown to accepted runtime signatures (`string` + `excludingRequestId`) — **documentation only**; not a license to break Request.

**Not proven as Batch 1b mandatory rewrite:** replacing live pending-request bridge; changing public eligibility method signature; Dependent snapshot work; EmployeeRead.

---

## 7. Conceptual Surfaces Touched

### Q6. UI / backend / data / navigation (analysis only)

| Surface | Touched conceptually? | Notes |
| ------- | --------------------- | ----- |
| UI / Livewire / navigation | **No** | Backend supplier only; UI deferred |
| Employee Application eligibility API | **Yes** | Existing contract/service; possible gap fills |
| Employee Domain eligibility calculator / reason enum | **Yes** | Missing vs design |
| Employee Infrastructure Null ActiveAllocation adapter | **Yes** | Missing |
| Integrations `PendingRequestReadBridge` | **Preserve** | Do not reverse without separate IRG/IA |
| Request enforcement / HTTP | **Consumer preserve** | No Request redesign in Batch 1b |
| Persistence / new tables | **No** | Eligibility is compute-only |
| Dependent / snapshot tables | **No** | Excluded by owner decisions |

---

## 8. Risks / Open Questions

### Q7. Unresolved before review decision

| ID | Question / risk | Impact |
| -- | --------------- | ------ |
| **R1** | Should runtime signature (`string` + `excludingRequestId`) be declared **accepted consumer truth**, with contract markdown updated editorially only? | Avoids breaking Request if IA wrongly “restores” Wave 1A `EmployeeId`-only API |
| **R2** | Is `ActiveAllocationReadPort` + Null adapter in Batch 1b IA scope, or deferred until Allocation live IRG? | Null enables SC-004 mock tests without live Allocation; live Allocation binding remains IRG-gated |
| **R3** | Are string `reasonCodes` acceptable interim, or must T041 enum land in first IA? | Affects authorized-scope size |
| **R4** | Is domain `EligibilityCalculator` required for DoD, or acceptable to keep Application orchestration if tests cover behavior? | Architecture purity vs minimal gap fill |
| **R5** | Exact Phase 7 (EmployeeRead) inclusion remains deferred clarification per Completion Wave — not assumed in Batch 1b | Keep out unless review expands |

None of R1–R5 reopen Request Dependent live integration.

---

## 9. Recommended Next Gate

### Q8. Next gate after this analysis?

**review decision**

Human governance reviews this evidence gap package and records:

- which proven gaps enter a future Implementation Authorization `authorized-scope`  
- which items remain Hold / deferred  
- confirmation that runtime consumer compatibility is preserved  

**Not next:** implementation, Quickstart, Implementation Authorization issuance (authorization **request** may follow review), contract drafting as a blank-slate rewrite, or Dependent IRG reopen.

---

## Explicit Non-Authorizations

- No coding / implementation under this analysis  
- No Implementation Authorization activated by this document  
- No Quickstart  
- No Request Dependent live integration  
- No Dependent `eligible` invention  
- No Employee Dependent Application read contract  
- No Spec04 / Spec07 reopen  

---

## No-Change Confirmation

**No application, test, UI, contract, or implementation files were modified.**

---

## Document Control

- Version: 1.0.0  
- Status: **`FEATURE_ANALYSIS_COMPLETED`**  
- Feature: Spec03 US4 Eligibility — Batch 1b evidence gap analysis (governance planning)  
- Recommended next gate: **review decision**  
- Owner: DormSys Architecture / Governance Review  
- Last Updated: 2026-07-11  
- Upstream: [selected-work-item-next-gate-confirmation.md](./selected-work-item-next-gate-confirmation.md)
