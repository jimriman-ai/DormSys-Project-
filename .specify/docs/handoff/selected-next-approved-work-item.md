# Selected Next Approved Work Item

**Artifact type:** Governance recording / next-step clarification (non-authorizing)  
**Recorded:** 2026-07-11  
**Checkpoint:** `selected-next-approved-work-item`

**This artifact does not** grant Design Approval, Implementation Authorization, Integration Implementation Authorization, Batch Execution Permission, or coding authority.

---

## 1. Status

| Field | Value |
| ----- | ----- |
| **Status** | **`SELECTED_NEXT_APPROVED_WORK_ITEM_RECORDED`** |

The selected work item’s **next allowed governance step** (feature analysis / evidence gap package) is open now. Implementation and Implementation Authorization gates for US4 remain closed until that analysis completes.

---

## 2. Selected Work Item

**Spec03 US4 Eligibility — Batch 1b evidence gap analysis (governance planning)**

Alternate short name (same item): Completion Wave **Batch 1b** — Spec03 US4 eligibility evidence assessment before any scoped Implementation Authorization.

Task range under assessment (not yet authorized for coding): **T041–T048** (`specs/003-employee-context/tasks.md` Phase 6).

---

## 3. Source Evidence

| Source | What it establishes |
| ------ | ------------------- |
| [next-approved-work-item-after-request-dependent-decision.md](./next-approved-work-item-after-request-dependent-decision.md) | Status `NEXT_APPROVED_WORK_ITEM_IDENTIFIED`; names this exact Batch 1b evidence gap analysis item; next action = prepare US4 evidence gap analysis package |
| [completion-wave-plan.md](./completion-wave-plan.md) | Execution order: Batch 1b = “Spec03 US4 evidence + scoped completion”; evidence assessment first; IA only for proven gaps; `COMPLETION_WAVE_READY` |
| [spec03-readiness-package.md](./spec03-readiness-package.md) | US4 readiness = `REQUIRES_EVIDENCE_GAP_ANALYSIS`; partial eligibility code present |
| [spec03-us3-completion-handoff.md](./spec03-us3-completion-handoff.md) | US3 closed; US4 Hold; requires separate evidence-gap Implementation Authorization before coding |
| [request-dependent-owner-decision-record.md](./request-dependent-owner-decision-record.md) | Live Dependent integration deferred; proceed with other approved work that does not require that path |
| [spec03-post-mvp-authorization.md](./spec03-post-mvp-authorization.md) | T041+ still Hold without separate authorization |
| `.specify/docs/spec-catalog.md` | Spec03 Employee Context remains partially complete (US3+ historically held; US3 now closed under separate IA) |
| `.specify/docs/catalog-decisions.md` | Authority Map — Implementation Authorization is not inferred from catalog ordering alone |
| `specs/003-employee-context/tasks.md` | Phase 6 US4 T041–T048 still open checkboxes |

---

## 4. Independence Confirmation

This work item does **not** require:

| Deferred / blocked Dependent concern | Why US4 Batch 1b analysis is independent |
| ------------------------------------ | ---------------------------------------- |
| Request-to-Employee live Dependent integration | Targets Employee **eligibility** supplier (CD-013), not `DependentSnapshotSourceContract` / stub replacement |
| `eligible` semantics resolution (`DependentSnapshotReadDTO.eligible`) | Owner D-02; eligibility gap analysis must not invent Dependent snapshot `eligible` meaning |
| Employee Dependent Application read contract | Owner D-03; not needed to inventory eligibility gaps vs accepted `EmployeeEligibilityContract` consumer behavior |

Request already consumes `EmployeeEligibilityContract`. Analysis must prefer accepted runtime consumer behavior over rewriting Request or inventing Dependent snapshot fields.

---

## 5. Current Governance State

| Dimension | State |
| --------- | ----- |
| **Overall** | **analysis-ready** |
| Feature analysis / evidence gap package | **Open** — next allowed step |
| Review decision (post-analysis) | Not yet — awaits completed gap package |
| Implementation Authorization | **Not ready** — Hold until proven gaps listed |
| Quickstart | **Not ready** — no US4 IA / execution batch open |
| Implementation / coding | **Not ready** — no active US4 `authorized-scope` |

Readiness label from Spec03 package: **`REQUIRES_EVIDENCE_GAP_ANALYSIS`**.

---

## 6. Next Allowed Governance Step

**feature analysis**

Exact action: prepare the **Spec03 US4 Eligibility evidence gap analysis package** (readiness/review handoff artifact) that inventories runtime evidence vs T041–T048 and eligibility/port contracts, lists proven missing/defective items only, preserves Request consumer compatibility, and excludes deferred Dependent live-integration work (owner D-01–D-03).

Halt after that package for human review before any Implementation Authorization request.

---

## Q1–Q5 Answers

### Q1. Exact name of the selected next approved work item?

**Spec03 US4 Eligibility — Batch 1b evidence gap analysis (governance planning)**

### Q2. What repository evidence supports that selection?

Primary: `next-approved-work-item-after-request-dependent-decision.md` (`NEXT_APPROVED_WORK_ITEM_IDENTIFIED`). Supporting: Completion Wave Plan Batch 1b sequencing; Spec03 readiness `REQUIRES_EVIDENCE_GAP_ANALYSIS`; US3 completion handoff US4 Hold; owner decision to proceed with non–Dependent-integration work.

### Q3. Why independent from Request Dependent live integration?

US4 Batch 1b assesses Employee eligibility supplier gaps (CD-013). It does not replace `DependentSnapshotSourceStub`, resolve Dependent snapshot `eligible`, or create an Employee Dependent read surface.

### Q4. Exact next allowed governance step?

**feature analysis** (US4 evidence gap analysis package).

### Q5. What is explicitly not authorized yet?

See §7.

---

## 7. Explicit Non-Authorizations

For this selected work item, the following are **not** yet allowed:

- **No implementation** (no coding of T041–T048 or related eligibility changes)  
- **No Implementation Authorization** issued yet (gap package must come first)  
- **No Quickstart** for US4 execution  
- **No wholesale contract rewrite / replacement** of runtime `EmployeeEligibilityContract` as automatic Batch 1b authority  
- **No** Request Dependent stub replacement, Integration IA, or Dependent read-contract invention  
- **No** mapping/deriving `DependentSnapshotReadDTO.eligible` from Employee data  
- **No** Spec04/Spec07 reopen, UI Feature Contracts, or EmployeeRead (T049–T052) assumed into this step  

This recording is selection + sequencing only.

---

## 8. No-Change Confirmation

**No application, test, UI, contract, or implementation files were modified.**

---

## Document Control

- Version: 1.0.0  
- Status: **`SELECTED_NEXT_APPROVED_WORK_ITEM_RECORDED`**  
- Selected work item: Spec03 US4 Eligibility — Batch 1b evidence gap analysis (governance planning)  
- Next allowed step: **feature analysis**  
- Owner: DormSys Architecture / Governance Review  
- Last Updated: 2026-07-11  
- Upstream: [next-approved-work-item-after-request-dependent-decision.md](./next-approved-work-item-after-request-dependent-decision.md)
