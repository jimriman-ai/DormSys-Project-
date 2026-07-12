# Next Approved Work Item Selection — After Create Entrypoint Deferral (Evidence-First)

**Artifact type:** Work item selection record (non-authorizing)  
**Decision date:** 2026-07-11  
**Checkpoint:** `next-approved-work-item-selection-after-create-entrypoint-deferral`

This artifact records Evidence-First selection of the next approved work item after Create Entrypoint deferral. It does **not** authorize Feature Analysis, Contract, or Implementation.

---

## 1. Status

`NEXT_APPROVED_WORK_ITEM_SELECTED`

---

## 2. Prior Disposition Acknowledgement

`Request Create Entrypoint Discoverability is deferred pending evidence and is not eligible for automatic reselection.`

Also acknowledged:

- `Request List Detail Navigation` — `REQUEST_LIST_DETAIL_NAVIGATION_CLOSED_AS_SATISFIED` — not reselected
- Return path after deferral: [request-create-entrypoint-discoverability-deferred-decision.md](./request-create-entrypoint-discoverability-deferred-decision.md) → `NEXT_APPROVED_WORK_ITEM_SELECTION`

---

## 3. Selection Method

Selection used the Evidence-First guardrail before lifecycle progression.

Checks applied per candidate: repository evidence, dependency readiness, residual/missing capability, and prior disposition (closed / deferred / failed validation).

Sources consulted:

- `.specify/docs/spec-catalog.md`
- `.specify/docs/catalog-decisions.md`
- `docs/product/product-authorization-next-ui-feature.md`
- `docs/ui/review/governance-next-candidate-triage.md`
- Spec03 Batch 1b / Dependent / completion-wave handoffs
- Prior closed/deferred Request UI selection and closure artifacts

---

## 4. Candidate Review

| Candidate | Evidence basis | Dependency readiness | Prior disposition | Eligibility |
| --------- | -------------- | -------------------- | ----------------- | ----------- |
| `Request Create Entrypoint Discoverability` | Evidence validation: route/page/nav confirmed; gap not confirmed | N/A | `DEFERRED_PENDING_EVIDENCE` / `FEATURE_ANALYSIS_NOT_ALLOWED` | **Rejected** — deferred; no automatic reselection |
| `Request List Detail Navigation` | Closure as satisfied | N/A | `CLOSED_AS_SATISFIED` | **Rejected** — closed |
| Notification P2–P9 / Employee Context UI | Full UI closeout chains | Delivered | Closed / reconciled | **Rejected** — already satisfied |
| `notification-mark-all-as-read` | Deferred in P2/P5/P8/P9 | Backend batch mutation absent | Blocked | **Rejected** — dependency + no product auth for intake |
| Request Show workflow mutations | Frozen `request-show` contract | Workflow UI not authorized | Blocked / frozen | **Rejected** |
| Request Dependent live integration | Owner D-01–D-03 | Blocked (eligible rule + Dependent read) | Deferred | **Rejected** |
| Live `ActiveAllocation` binding | Batch1b residual | Separate IRG required | Deferred residual | **Rejected** — not selection-ready without IRG/owner reopen |
| Spec03 EmployeeRead (T049–T052) | Tasks open; no `EmployeeReadContract` in module | Optional hold; consumers use stubs | Untouched / optional | **Rejected for this selection** — optional follow-on; no current Implementation Authorization or product-core mandate recorded for immediate progression |
| `dormitory-ui` / `workflow-ui` | Prior analyses `NO_VALID_UI_SCOPE` | Application surfaces absent / deferred | Blocked / not ready | **Rejected** |
| `Audit UI` (`audit-ui`) | Product Authorization `AUTHORIZED` for UI governance intake; Spec10 Audit Trail closed as backend context; no Audit UI presentation chain yet | Intake blockers: none for starting evidence/repo-inspection; Application UI-consumable readiness **UNKNOWN** until evidence validation | Not closed/deferred under this slug; distinct from blocked `audit-explorer-ui` / E-03 exclusion | **Eligible** |

---

## 5. Selected Work Item

`Audit UI`

Canonical slug: `audit-ui`

### Evidence basis for selection

- `docs/product/product-authorization-next-ui-feature.md` — **STATUS `AUTHORIZED`** for `audit-ui` UI governance intake only; start gate for intake is repo-inspection / evidence establishment
- Spec catalog / Spec10 closure context: Audit Trail capability exists at program level; Audit UI presentation is not among closed Request/Notification/Employee UI features
- No prior Spec Kit disposition of closed-as-satisfied or deferred-pending-evidence for `Audit UI`

### Why not blocked by prior closure/deferment

- Not `Request Create Entrypoint Discoverability` (deferred)
- Not `Request List Detail Navigation` (closed as satisfied)
- Not a reopen of closed Notification/Employee UI chains
- Product Authorization explicitly names this feature and excludes reopening closed prior UI grants

---

## 6. Next Required Gate

`The selected work item must pass EVIDENCE_VALIDATION before Feature Analysis may begin.`

Alignment note: Product Authorization permits starting `repo-inspection` for `audit-ui` as the evidence-establishment path. Spec Kit **Evidence Validation** is the mandatory gate before Feature Analysis; it must not be skipped. Exact MVF remains `TBD_BY_PRODUCT` until evidence gates complete.

---

## 7. Non-Authorization Statement

`This artifact records work item selection only and does not authorize Feature Analysis, Contract, or Implementation.`

This selection does **not** grant Design Approval, Implementation Authorization, Batch Execution Permission, or coding authority.

---

## 8. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

Only this selection artifact was created:

- `.specify/docs/handoff/next-approved-work-item-selection-after-create-entrypoint-deferral.md`

Historical selection artifacts were **not** overwritten.
