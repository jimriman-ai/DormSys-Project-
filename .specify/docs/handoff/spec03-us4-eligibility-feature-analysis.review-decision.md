# Spec03 US4 Eligibility — Feature Analysis Review Decision

**Artifact type:** Feature Analysis Review Decision (non-authorizing for coding)  
**Decision date:** 2026-07-11  
**Checkpoint:** `spec03-us4-eligibility-feature-analysis-review-decision`  
**Prior check:** `FEATURE_ANALYSIS_REVIEW_DECISION_MISSING`

**This artifact does not** grant Implementation Authorization, Quickstart, implementation lock, Feature Contract drafting authority for UI, or coding permission.

---

## 1. Status

| Field | Value |
| ----- | ----- |
| **Status** | **`FEATURE_ANALYSIS_REVIEW_ACCEPTED`** |

---

## 2. Feature Name

**Spec03 US4 Eligibility — Batch 1b evidence gap analysis (governance planning)**

| Field | Value |
| ----- | ----- |
| Spec | `003-employee-context` / catalog `spec03` |
| User story | US4 — Eligibility Computation (P3) |
| Completion Wave | Batch 1b |
| Tasks under assessment | T041–T048 (gap-only; not wholesale greenfield) |

---

## 3. Reviewed Artifact

| Artifact | Role |
| -------- | ---- |
| [spec03-us4-eligibility-feature-analysis.md](./spec03-us4-eligibility-feature-analysis.md) | Primary — `FEATURE_ANALYSIS_COMPLETED` |
| [spec03-us4-eligibility-feature-analysis-handoff.md](./spec03-us4-eligibility-feature-analysis-handoff.md) | Short handoff |
| [selected-next-approved-work-item.md](./selected-next-approved-work-item.md) | Work item selection |
| [selected-work-item-next-gate-confirmation.md](./selected-work-item-next-gate-confirmation.md) | Gate confirmation |
| [completion-wave-plan.md](./completion-wave-plan.md) | Batch 1b = evidence → scoped IA (not UI Feature Contract) |
| [request-dependent-owner-decision-record.md](./request-dependent-owner-decision-record.md) | D-01–D-03 independence constraints |
| `.specify/docs/catalog-decisions.md` | CD-013 |
| `.specify/docs/spec-catalog.md` | Spec03 inventory |

No separate repo-inspection artifact was required beyond the Feature Analysis code/evidence tables; those findings are accepted as the inspection basis for this review.

---

## 4. Review Findings

### Q1. Clearly named and scoped?

**Yes.** Exact work item name and Batch 1b / T041–T048 framing are consistent across selection, gate confirmation, and Feature Analysis.

### Q2. Problem statement evidence-backed?

**Yes.** CD-013 / FR-007 / SC-004 plus demonstrated partial runtime supplier and open Phase 6 tasks justify gap-only Batch 1b (not greenfield rewrite).

### Q3. In-scope narrow enough for next stage?

**Yes.** Analysis separates (a) governance inventory already done from (b) proven gap candidates for a future scoped IA. Present items (T042 DTO; partial T046/T047; live pending-request bridge) are correctly marked do-not-reauthorize-as-greenfield.

### Q4. Out-of-scope explicit enough?

**Yes.** Excludes Dependent live integration, Dependent `eligible` invention, Dependent read surface, EmployeeRead, UI, Spec04/07 reopen, Request enforcement redesign, and casual NullPending reversion.

### Q5. Depends on Request Dependent live integration?

**No.** Confirmed independent — see §5.

### Q6. Surfaces identified at analysis level?

**Yes.** Backend Application/Domain/Null ActiveAllocation adapter surfaces identified; UI/navigation/data tables correctly marked untouched; Request consumer preserve; Integrations pending bridge preserve.

### Q7. Risks acceptable for next gate?

**Yes**, after review dispositions of R1–R5 below. Residual risk is limited to IA drafting precision (verbatim `authorized-scope`), which is the purpose of the next gate.

### Open-question dispositions (R1–R5)

| ID | Disposition for IA prep |
| -- | ----------------------- |
| **R1** | **Accepted consumer truth:** runtime `computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null)` and matching `PendingRequestReadPort` signature remain authoritative. Spec03 contract markdown may be synced **editorially only** under IA if listed; **must not** force `EmployeeId`-only rewrite that breaks Request. |
| **R2** | **Include in Batch 1b IA candidate scope:** `ActiveAllocationReadPort` + `NullActiveAllocationReadAdapter` + service wiring for `active_allocation_exists`. Live Allocation binding remains **out of scope** (separate IRG/IA). |
| **R3** | **Include T041** `EligibilityReasonCode` enum in IA candidate scope (prefer enum over indefinite string-only interim). |
| **R4** | **Include T045** `EligibilityCalculator` in IA candidate scope (domain extraction of rules). |
| **R5** | **Exclude** Phase 7 EmployeeRead (T049–T052) from Batch 1b IA unless a future separate decision expands scope. |

### Approved gap candidates for Implementation Authorization preparation

Proposed IA `authorized-scope` **candidates** (verbatim list to be finalized in the IA record — not coding authority now):

1. T041 — `EligibilityReasonCode`  
2. T043 (ActiveAllocation portion only) — `ActiveAllocationReadPort`  
3. T044 (NullActive only) — `NullActiveAllocationReadAdapter`  
4. T045 — `EligibilityCalculator`  
5. T047 (gap fill) — wire ActiveAllocation into `EmployeeEligibilityService` + Employee provider bind for ActiveAllocation → Null adapter  
6. T048 — `EmployeeEligibilityContractTest`  
7. Optional editorial sync of eligibility/port contract markdown to accepted runtime signatures (documentation only)

**Explicitly not in Batch 1b IA candidates:**

- T042 greenfield recreate  
- Replacing live `PendingRequestReadBridge` / forcing NullPending  
- Changing public eligibility method signature away from accepted consumer form  
- T049–T052 EmployeeRead  
- Request Dependent stub replacement / Dependent `eligible` / Dependent Application read surface  
- Live Allocation IRG/adapter  

---

## 5. Request Dependent Independence Confirmation

This feature **does not** require:

| Concern | Required? |
| ------- | --------- |
| Request-to-Employee live Dependent integration | **No** |
| `DependentSnapshotReadDTO.eligible` semantics resolution | **No** |
| Employee Dependent Application read contract | **No** |

Independence basis: CD-013 eligibility supplier path is orthogonal to FamilyDirect Dependent snapshot sourcing; owner decisions D-01–D-03 remain binding exclusions. Feature Analysis §5 accepted.

---

## 6. Decision

| Field | Value |
| ----- | ----- |
| **Decision** | **`ACCEPTED_FOR_IMPLEMENTATION_AUTHORIZATION_PREP`** |

**Not selected:** `ACCEPTED_FOR_CONTRACT` — Completion Wave Batch 1b and Spec03 design already use existing Spec03 Application contracts; this is backend gap-fill IA prep, **not** a UI Feature Contract path.

---

## 7. Next Allowed Governance Step

**Prepare Spec03 US4 Batch 1b Implementation Authorization** (draft / request record) with verbatim `authorized-scope` limited to the approved gap candidates in §4, `blocked-scope` covering Dependent live path, EmployeeRead, UI, Spec04/07 reopen, and live Allocation binding.

Exact next action phrasing: **create Implementation Authorization preparation artifact for Spec03 US4 Batch 1b** (human activation still required before coding).

**Do not** create a UI Feature Contract as the next step.  
**Do not** start implementation, Quickstart, or implementation lock under this review.

---

## Q1–Q9 Summary

| Q | Answer |
| - | ------ |
| Q1 Named/scoped | Yes |
| Q2 Problem evidence-backed | Yes |
| Q3 Scope narrow enough | Yes |
| Q4 Out-of-scope explicit | Yes |
| Q5 Dependent live integration | No — independent |
| Q6 Surfaces identified | Yes |
| Q7 Risks acceptable | Yes (R1–R5 disposed) |
| Q8 Decision | `ACCEPTED_FOR_IMPLEMENTATION_AUTHORIZATION_PREP` |
| Q9 Next step | Prepare Spec03 US4 Batch 1b Implementation Authorization |

---

## 8. Explicit Non-Authorizations

- **No implementation / coding** of T041–T048  
- **No active Implementation Authorization** until a separate IA record is created and activated  
- **No Quickstart**  
- **No implementation lock**  
- **No UI Feature Contract** drafting under this decision  
- **No** backend/API changes until IA is active  
- **No** Request Dependent live integration, Dependent `eligible` invention, or Dependent read contract  
- **No** Spec04 / Spec07 reopen  

---

## 9. No-Change Confirmation

**No application, test, UI, contract, or implementation files were modified.**

---

## Document Control

- Version: 1.0.0  
- Status: **`FEATURE_ANALYSIS_REVIEW_ACCEPTED`**  
- Decision: **`ACCEPTED_FOR_IMPLEMENTATION_AUTHORIZATION_PREP`**  
- Feature: Spec03 US4 Eligibility — Batch 1b evidence gap analysis (governance planning)  
- Next step: Prepare Spec03 US4 Batch 1b Implementation Authorization  
- Owner: DormSys Architecture / Governance Review  
- Last Updated: 2026-07-11  
- Reviewed: [spec03-us4-eligibility-feature-analysis.md](./spec03-us4-eligibility-feature-analysis.md)
