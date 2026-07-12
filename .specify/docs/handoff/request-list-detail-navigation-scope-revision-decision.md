# Scope Revision Decision — Request List Detail Navigation

**Artifact type:** Scope-governance revision decision (non-authorizing)  
**Decision date:** 2026-07-11  
**Checkpoint:** `request-list-detail-navigation-scope-revision-decision`

This artifact records whether a valid residual scope can be explicitly defined after authorization denial. It does **not** authorize implementation, create a Feature Contract, or start execution.

---

## 1. Status

`REQUEST_LIST_DETAIL_NAVIGATION_SCOPE_REVISION_REQUIRES_OWNER_INPUT`

---

## 2. Current State

`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`

---

## 3. Reason Scope Revision Was Required

From [request-list-detail-navigation-authorization-denial-analysis.md](./request-list-detail-navigation-authorization-denial-analysis.md) §6–§7:

> Is scope revision required before authorization can be reconsidered **toward a grant**? | **Yes** — authorization cannot be reconsidered as `IMPLEMENTATION_AUTHORIZED` until the governing scope is updated through the proper governance path (e.g. explicit residual selection with verbatim scope, or a new work item), because current approved artifacts provide no safe new `authorized-scope`

> Next Governance Step: `Return to scope-governance revision before authorization reconsideration`

Primary denial basis (same analysis §4): **already-satisfied core deliverable / empty `authorized-scope`**. Residual polish must not be invented.

---

## 4. Residual Deliverable Assessment

`Residual deliverable cannot be confirmed without owner/product/architecture input`

### Answers to required questions

| # | Question | Answer |
| - | -------- | ------ |
| 1 | Does a real residual deliverable still exist? | **Not evidenced** in approved Feature Analysis / Review / Validation / Clarification / Contract Decision / IA artifacts. Core list→`requests.show` navigation is already present and closeout-recorded. |
| 2 | Can that residual deliverable be stated as a precise implementation scope? | **No** from current artifacts alone without inventing polish (forbidden). |
| 3 | Is the residual scope strictly read-only navigation/view behavior? | **N/A** — no residual scope defined. |
| 4 | Does residual avoid mutation / workflow / approvals / notifications / allocation / new business rules / write-side APIs? | **N/A** — no residual scope defined; those remain excluded for any future residual. |
| 5 | Does residual require a feature contract before future authorization review? | **Cannot be determined** until a residual is explicitly selected and scoped. |
| 6 | Can authorization be reconsidered after this revision, or is additional input still required? | **Additional owner/product/architecture input is still required** before any grant-oriented reconsideration. |

Evidence-backed note (not a residual definition): historical core deliverable is already satisfied; speculative polish (whole-row click, stronger CTA, mobile/a11y affordances) was previously listed only as **undefined ambiguity**, not as approved residual requirements.

---

## 5. Revised Scope

`No revised scope defined`

#### In Scope

None. No evidence-backed residual In Scope items are available without inventing scope.

#### Out of Scope

Explicitly exclude (preserved from prior governance; any future residual must continue to exclude):

- create/edit/update behavior
- approval actions
- workflow/state transitions
- comments
- attachments
- notifications
- allocation/assignment changes
- new domain logic
- write-side behavior
- speculative UI polish
- mutation logic
- Request Dependent / EmployeeRead / Allocation reopen
- re-implementing already-delivered list→detail navigation

---

## 6. Contract Requirement Assessment

`Contract requirement cannot be determined from current artifacts`

No residual scope is defined; therefore whether a Feature Contract would be required for a future residual cannot be decided until that residual is explicitly selected and scoped. This artifact does **not** create a Feature Contract.

---

## 7. Authorization Reconsideration Readiness

`Not ready for Authorization Review; owner/product/architecture input required`

---

## 8. Next Governance Step

`Await owner/product/architecture decision`

Required owner/product/architecture decision (non-authorizing options):

1. **Close or defer** `Request List Detail Navigation` as already satisfied for core list→detail navigation (no Implementation Authorization), **or**  
2. **Explicitly select and verbatim-scope** a residual discoverability item distinct from the closed core deliverable (then return to Feature Analysis / Contract Decision / Authorization Review as appropriate for that residual only).

Do not invent residual scope in the meantime.

---

## 9. Explicit Non-Authorization

`Implementation remains unauthorized until a new valid authorization decision is recorded.`

---

## 10. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

Only this scope-revision artifact was created:

- `.specify/docs/handoff/request-list-detail-navigation-scope-revision-decision.md`

---

## Traceability

| Artifact | Role |
| -------- | ---- |
| `request-list-detail-navigation-authorization-denial-analysis.md` | Controlling source — scope revision required before grant reconsideration |
| `request-list-detail-navigation-implementation-authorization-decision.md` | Denial — empty `authorized-scope` / already-satisfied core |
| Clarification / Blocker Resolution | No residual invention; resolution ≠ approval |
| Feature Analysis / Review / Validation | Core navigation already present |
| Feature Contract Decision | `FEATURE_CONTRACT_NOT_REQUIRED` for prior closed core classification |
| Work Item Selection (reactivated) | Lifecycle entry only |

---

## Document Control

- Version: 1.0.0  
- Status: **`REQUEST_LIST_DETAIL_NAVIGATION_SCOPE_REVISION_REQUIRES_OWNER_INPUT`**  
- Work item: `Request List Detail Navigation`  
- Owner: Governance Review  
- Last Updated: 2026-07-11  
- Checkpoint: `request-list-detail-navigation-scope-revision-decision`
