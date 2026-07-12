# Next Approved Work Item After Request Dependent Decision Closure

**Artifact type:** Governance sequencing / next-step determination (non-authorizing)  
**Decision date:** 2026-07-11  
**Checkpoint:** `next-approved-work-item-after-request-dependent-decision`

**This artifact does not** grant Design Approval, Implementation Authorization, Integration Implementation Authorization, Batch Execution Permission, or coding authority.

---

## 1. Status

| Field | Value |
| ----- | ----- |
| **Status** | **`NEXT_APPROVED_WORK_ITEM_IDENTIFIED`** |

---

## 2. Closed Governance Path

The following chain is complete for the Spec03 US3 → Request Dependent decision path:

| Step | Artifact / outcome |
| ---- | ------------------ |
| 1 | Spec03 readiness package → `READY_FOR_US3_IMPLEMENTATION_AUTHORIZATION` |
| 2 | Spec03 US3 Implementation Authorization → `SPEC03_US3_IMPLEMENTATION_AUTHORIZED` |
| 3 | Authorization review / approval |
| 4 | Implementation T035–T040 |
| 5 | Tests / quality gate → `SPEC03_US3_IMPLEMENTATION_COMPLETE` |
| 6 | Completion handoff → `SPEC03_US3_COMPLETED` |
| 7 | Request Dependent IRG → `REQUEST_DEPENDENT_INTEGRATION_BLOCKED_BY_MISSING_DECISION` |
| 8 | Decision package → `REQUEST_DEPENDENT_DECISION_PACKAGE_REQUIRES_OWNER_INPUT` |
| 9 | Owner decision request → `AWAITING_OWNER_DECISION` |
| 10 | Owner decision record → **`REQUEST_DEPENDENT_OWNER_DECISION_RECORDED`** |

Recorded owner decisions (binding for current scope):

- D-01 `DEFER_LIVE_INTEGRATION`
- D-02 `ELIGIBLE_REQUIRES_EXPLICIT_PRODUCT_RULE`
- D-03 `NO_EMPLOYEE_DEPENDENT_READ_SURFACE_NOW`

Approved current behavior: **`DependentSnapshotSourceStub` remains active.** Live Request ↔ Employee Dependent integration is deferred; no Integration IA is allowed for that path now.

Owner decision next step (verbatim intent): proceed with other approved work that does **not** require Request Dependent live integration; do not re-run that IRG until future Product/Architecture inputs exist.

---

## 3. Open Candidate Work Items

From Completion Wave Plan, Spec03 readiness package, US3 completion handoff, tasks.md, and catalog:

| ID | Candidate | Evidence source |
| -- | --------- | --------------- |
| **C1** | Spec03 US4 Eligibility — evidence gap analysis then scoped IA (Batch 1b) | `completion-wave-plan.md` Batch 1b; readiness `REQUIRES_EVIDENCE_GAP_ANALYSIS`; tasks T041–T048 open |
| **C2** | Spec03 Phase 7 EmployeeRead (T049–T052) | Optional follow-on in wave plan / readiness |
| **C3** | Spec03 Phase 8 polish | After authorized slices only |
| **C4** | Request Dependent live integration (stub replacement) | Deferred by owner decision D-01/D-02/D-03 |
| **C5** | Employee Application Dependent read surface (for future IRG) | Explicitly not authorized now (D-03) |
| **C6** | UI Feature Contracts / UI Feature Execution | Deferred until product-core readiness; audit `REQUIRES_SPEC_COMPLETION_BEFORE_UI` |
| **C7** | Spec04 Auth/UI readiness / Spec06 hygiene / Workflow | Later waves; Spec04 backend / Spec07 closed — do not reopen |

---

## 4. Evaluation Matrix

| Candidate | Allowed now? | Depends on Request Dependent live integration? | Required gate / authorization state | Recommendation |
| --------- | ------------ | ---------------------------------------------- | ----------------------------------- | -------------- |
| **C1 US4 evidence gap analysis** | **Allowed** (governance planning only) | **No** — Employee eligibility supplier / CD-013 completeness; Request already consumes `EmployeeEligibilityContract`; must not break consumer; live Dependent stub path is orthogonal | Readiness: `REQUIRES_EVIDENCE_GAP_ANALYSIS`. Implementation still **Hold** until separate scoped IA after gaps proven | **Next** |
| C1 US4 implementation (T041–T048 wholesale) | **Not allowed yet** | No for Employee-local gaps; must not invent Dependent `eligible` or reopen Deferred Dependent IRG | Needs evidence package + separate Implementation Authorization with verbatim gap scope | Block until gap analysis + IA |
| C2 EmployeeRead | Not assumed | No | Hold; optional; no active IA | Defer unless gap analysis proves product-core need |
| C3 Polish | Not yet | No | After authorized delivery | Later |
| C4 Live Dependent integration | **Blocked / deferred** | **Yes** | Owner D-01; IRG not PASS; no Integration IA | Do not reopen |
| C5 Dependent read surface | **Blocked** | Enables future IRG only | Owner D-03 | Do not invent |
| C6 UI | Deferred | No | Feature-contract path not started | Out of wave |
| C7 Spec04/06/07/Workflow | Out of Batch 1b | N/A | Spec04 backend closed; Spec07 closed; Workflow deferred | Do not reopen closed Specs |

---

## 5. Next Approved Work Item

**Spec03 US4 Eligibility — Batch 1b evidence gap analysis (governance planning)**

Exact framing from repository evidence:

- Completion Wave Plan: Batch 1b = “Spec03 US4 evidence + scoped completion (optional / separate IA)” — evidence assessment first; authorize only proven missing/defective items.
- Spec03 readiness: US4 readiness = `REQUIRES_EVIDENCE_GAP_ANALYSIS` (partial code present; not assumed complete or missing wholesale).
- US3 completion handoff / post-MVP hold: US4 remains Hold until separate evidence-gap Implementation Authorization.
- Owner decision record: does **not** authorize US4 coding, but directs proceeding with other approved work that does not require live Dependent integration — Batch 1b matches that directive under the already-accepted Completion Wave Plan (`COMPLETION_WAVE_READY`).

**Not** the next item: Request Dependent live wiring, Employee Dependent Application read contract, or US4 coding without a new IA.

Clarification note (non-blocking per wave plan): exact US4 / Phase 7 inclusion in the eventual IA scope may still be refined when drafting Batch 1b authorization; that does **not** block starting the evidence gap analysis itself.

---

## 6. Next Governance Action

**Prepare readiness / review artifact: Spec03 US4 Eligibility evidence gap analysis package.**

That package should (planning only):

1. Inventory runtime evidence vs `specs/003-employee-context/tasks.md` T041–T048 and `contracts/employee-eligibility-service.md` / ports contracts.  
2. Prefer accepted Request consumer behavior over rewriting runtime contracts.  
3. List proven missing/defective items only (do not wholesale greenfield-authorize open checkboxes).  
4. Explicitly exclude Request Dependent stub replacement, Dependent `eligible` invention, and new Employee Dependent read surface (owner D-01–D-03).  
5. Halt for human Implementation Authorization before any US4 coding.

Do **not** issue US4 Implementation Authorization in this step.  
Do **not** re-run Request Dependent IRG.

---

## Q1–Q5 Answers (evidence-backed)

### Q1. What work items remain open?

US4 (T041–T048) hold; EmployeeRead (T049–T052) optional hold; polish; deferred Request Dependent live integration; deferred Employee Dependent Application read surface; deferred UI Feature Execution; later-wave Spec04 Auth/UI, Spec06 hygiene, Workflow (not Batch 1b).

### Q2. Which remaining work item is next allowed?

**Spec03 US4 Batch 1b evidence gap analysis** (governance), as sequenced by Completion Wave Plan after US3 DoD and after Request Dependent decision closure.

### Q3. Does it depend on Request Dependent live integration?

**No.** US4 targets Employee eligibility supplier fidelity (CD-013). Request already enforces via `EmployeeEligibilityContract`. Owner deferral of Dependent snapshot live source does not block eligibility gap analysis. US4 work must preserve Request consumer compatibility and must not map or invent Dependent snapshot `eligible`.

### Q4. Exact next governance action?

**Prepare readiness/review artifact** (US4 evidence gap analysis). Not Integration IA, not Quickstart for deferred integration, not US4 coding IA yet.

### Q5. Explicitly not allowed next?

See §7.

---

## 7. Explicit Non-Allowed Actions

- Do **not** reopen Request Dependent live integration.  
- Do **not** re-run Request Dependent IRG until Product/Architecture reopen conditions are met.  
- Do **not** issue Integration Implementation Authorization for deferred Dependent work.  
- Do **not** create Quickstart for deferred Request Dependent integration.  
- Do **not** invent an Employee Application Dependent read / provider contract (D-03).  
- Do **not** map, derive, or implement `DependentSnapshotReadDTO.eligible` from Employee data (D-02).  
- Do **not** replace `DependentSnapshotSourceStub`.  
- Do **not** auto-issue US4 Implementation Authorization without evidence gap package.  
- Do **not** reopen Spec04 backend Phases 1–4 or Spec07.  
- Do **not** start UI Feature Contracts / UI Feature Execution under this sequencing decision.  
- Do **not** treat this document as Implementation Authorization or Batch Execution Permission.

---

## 8. No-Change Confirmation

**No application, test, UI, contract, or implementation files were modified.**

---

## Traceability

| Artifact | Role |
| -------- | ---- |
| `completion-wave-plan.md` | Program sequence — Batch 1b after US3; Batch 2 IRG closed by owner deferral |
| `spec03-readiness-package.md` | US4 = `REQUIRES_EVIDENCE_GAP_ANALYSIS` |
| `spec03-us3-completion-handoff.md` | US3 closed; US4 hold |
| `request-dependent-owner-decision-record.md` | `REQUEST_DEPENDENT_OWNER_DECISION_RECORDED` |
| `spec03-post-mvp-authorization.md` | T041+ still require separate authorization |
| `.specify/docs/spec-catalog.md` | Spec03 partially complete / US3+ historical hold notes |
| `.specify/docs/catalog-decisions.md` | Authority Map — do not infer IA from ordering alone |

---

## Document Control

- Version: 1.0.0  
- Status: **`NEXT_APPROVED_WORK_ITEM_IDENTIFIED`**  
- Next work item: Spec03 US4 Eligibility Batch 1b evidence gap analysis  
- Next action: Prepare US4 evidence gap analysis readiness/review artifact  
- Owner: DormSys Architecture / Governance Review  
- Last Updated: 2026-07-11
