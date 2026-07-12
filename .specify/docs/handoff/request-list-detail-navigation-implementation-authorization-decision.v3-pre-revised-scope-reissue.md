# Implementation Authorization Decision — Request List Detail Navigation (Reissue After Blocker Resolution)

**Artifact class:** Implementation Authorization Decision (Authority Map–aligned decision record)  
**Decision date:** 2026-07-11  
**Checkpoint:** `request-list-detail-navigation-implementation-authorization-decision`  
**Record version:** 3.0.0 (reissue after blocker resolution)

**Canonical map:** `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`  
**Lifecycle:** `.specify/governance/_meta/authority-model.md`  
**Execution:** `.specify/governance/execution-policy.md`

**Historical preservation:** Prior post-clarification decision text preserved at [request-list-detail-navigation-implementation-authorization-decision.v2-pre-reissue.md](./request-list-detail-navigation-implementation-authorization-decision.v2-pre-reissue.md) (`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`). Earlier pre-clarification content on this path recorded `REQUEST_LIST_DETAIL_NAVIGATION_AUTHORIZATION_NEEDS_CLARIFICATION` (superseded; not restored here).

This artifact records an authorization decision only. It does **not** perform implementation. Blocker resolution is **not** automatic approval to implement.

---

## 1. Status

`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`

| Field | Value |
| ----- | ----- |
| Coding permitted now? | **No** |
| `authorization-status` | **Not set / not active** — no Implementation Authorization Record activated |
| Package meaning | Reissue after blocker resolution: denial **reaffirmed**; empty `authorized-scope` / already-satisfied core remains the controlling reason |

---

## 2. Authorization History

Previous decision:  
`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`

Blocker resolution:  
`REQUEST_LIST_DETAIL_NAVIGATION_AUTHORIZATION_BLOCKER_RESOLVED`

| Step | Artifact / status |
| ---- | ----------------- |
| Prior IA (archived) | `request-list-detail-navigation-implementation-authorization-decision.v2-pre-reissue.md` — `IMPLEMENTATION_NOT_AUTHORIZED` |
| Blocker resolution | `request-list-detail-navigation-authorization-blocker-resolution.md` — `AUTHORIZATION_BLOCKER_RESOLVED` |
| This reissue | Canonical path — `IMPLEMENTATION_NOT_AUTHORIZED` (reaffirmed) |

Blocker resolution classified the denial as **already-satisfied core deliverable / empty `authorized-scope`**, not as a missing unexplained gap. It explicitly forbade converting resolution into authorization or inventing residual polish.

---

## 3. Source Governance Chain

| Artifact | Path | Status / role |
| -------- | ---- | ------------- |
| Work Item Selection | `.specify/docs/handoff/next-approved-work-item-selection-request-list-detail-navigation-reactivated.md` | `NEXT_APPROVED_WORK_ITEM_SELECTED` |
| Feature Analysis | `.specify/docs/analysis/request-list-detail-navigation.feature-analysis.md` | `FEATURE_ANALYSIS_COMPLETED` |
| Feature Analysis Review Decision | `.specify/docs/analysis/request-list-detail-navigation.review-decision.md` | `FEATURE_ANALYSIS_REVIEW_ACCEPTED` |
| Feature Analysis Validation | `.specify/docs/handoff/request-list-detail-navigation-feature-analysis-validation.md` | `REQUEST_LIST_DETAIL_NAVIGATION_FEATURE_ANALYSIS_REVALIDATED` |
| Feature Contract Decision | `.specify/docs/handoff/request-list-detail-navigation-feature-contract-decision.md` | `FEATURE_CONTRACT_NOT_REQUIRED` |
| Authorization Review | `.specify/docs/handoff/request-list-detail-navigation-authorization-review.md` | `AUTHORIZATION_REVIEW_READY` |
| Authorization Clarification Decision | `.specify/docs/handoff/request-list-detail-navigation-authorization-clarification-decision.md` | `REQUEST_LIST_DETAIL_NAVIGATION_AUTHORIZATION_CLARIFICATION_RESOLVED` |
| Previous Authorization Decision | `.specify/docs/handoff/request-list-detail-navigation-implementation-authorization-decision.v2-pre-reissue.md` | `REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED` |
| Authorization Blocker Resolution | `.specify/docs/handoff/request-list-detail-navigation-authorization-blocker-resolution.md` | `REQUEST_LIST_DETAIL_NAVIGATION_AUTHORIZATION_BLOCKER_RESOLVED` |
| Catalog / status mirrors | `.specify/docs/catalog-decisions.md`, `.specify/docs/spec-catalog.md` | Authority Map / inventory |
| Authority model | `.specify/governance/_meta/authority-model.md` | Authorization Record lifecycle vocabulary |

Supporting closeout evidence (not reopened): `docs/ui/closeouts/requests/request-list-detail-navigation.closeout.yaml`.

---

## 4. Authorized Objective

`Implement only the approved Request List Detail Navigation capability within the defined read/presentation boundary.`

**Application under this reissue:** The approved capability (navigation from Request list to Request read/detail destination) is already evidenced as present. Resolved blocker explanation does **not** create a new implementable surface. Therefore this objective does **not** activate coding permission or `authorization-status: active` / `partial`.

---

## 5. Authorized Scope

**Activated `authorized-scope`:** **None.**

| Element | Decision |
| ------- | -------- |
| Allowed changes | **None** for new implementation under this work item |
| Allowed files/components (historical evidence only; not a change set) | List Blade **مشاهده** → `requests.show`; existing show destination; existing navigation UI flow test — already delivered |
| Required tests for new authorized behavior | **None** — no new behavior authorized |
| Required validation for new work | **N/A** — no new Implementation Authorization Record |

Only scope supported by approved governance artifacts is the already-satisfied core list→detail read navigation. Residual polish is not present in approved artifacts and is not authorized.

---

## 6. Forbidden Scope

Explicit non-goals:

- Request mutation
- workflow changes
- approval changes
- notifications
- Employee integration
- Dependent integration
- Allocation changes
- unrelated UI work
- architecture refactoring
- inventing residual UX polish without separate selection + verbatim residual scope
- reopening frozen Request Show beyond existing read destination consumption
- treating blocker resolution or this reissue as Implementation Authorization

---

## 7. Quality Gates

Not applicable to new implementation (none authorized).

Informational only for a **future** separately selected residual item under a different Authorization Record:

- applicable feature tests for that residual only
- architecture checks (`composer run arch`) if Presentation/module boundaries affected
- PHPStan (`php vendor/bin/phpstan analyse --no-progress` or `composer run phpstan`) if PHP changes
- Pint if PHP changes

Those gates are **not** activated by this decision.

---

## 8. Completion Criteria

For the approved core intent under this reactivation chain, minimum completion evidence is treated as **already recorded** by prior UI closeout + revalidated Feature Analysis (list affordance, show destination, navigation tests).

No new implementation completion package is authorized or required under this reissue.

Future residual work requires separate selection, Authorization Record, and its own completion evidence.

---

## 9. Final Decision

`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`

**Why blocker resolution is insufficient to authorize implementation:** The resolved blocker was explanatory completeness of an already-correct denial (empty `authorized-scope` / already-satisfied core). It did not add missing scope, remove a governance conflict, or create a safe new `authorized-scope`. Reinterpreting resolution as approval is forbidden.

---

## 10. Next Allowed Governance Step

`Resolve remaining authorization blockers`

For any **future coding** path: separately select and verbatim-scope a residual discoverability item (if desired). Until then, do **not** create an Implementation Execution Task for `Request List Detail Navigation` under this chain.

Core intent under the current reactivation remains **already satisfied** with **no** Implementation Authorization.

---

## 11. Explicit Non-Implementation Statement

This artifact records an authorization decision only and does not perform implementation.

---

## 12. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

Artifacts written for this reissue:

- `.specify/docs/handoff/request-list-detail-navigation-implementation-authorization-decision.md` (this record)
- `.specify/docs/handoff/request-list-detail-navigation-implementation-authorization-decision.v2-pre-reissue.md` (archived prior decision; history preservation)

---

## Document Control

- Version: 3.0.0  
- Status: **`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`**  
- Work item: `Request List Detail Navigation`  
- Coding permitted: **No**  
- `authorization-status`: **not active**  
- Owner: Governance Review  
- Last Updated: 2026-07-11  
- Checkpoint: `request-list-detail-navigation-implementation-authorization-decision`
