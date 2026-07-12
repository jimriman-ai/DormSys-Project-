# Implementation Authorization Decision — Request List Detail Navigation

**Artifact class:** Implementation Authorization Decision (Authority Map–aligned decision record)  
**Decision date:** 2026-07-11  
**Checkpoint:** `request-list-detail-navigation-implementation-authorization-decision`

**Canonical map:** `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`  
**Lifecycle:** `.specify/governance/_meta/authority-model.md`  
**Execution:** `.specify/governance/execution-policy.md`

**Supersedes:** prior content of this path that recorded `REQUEST_LIST_DETAIL_NAVIGATION_AUTHORIZATION_NEEDS_CLARIFICATION` (pre-clarification). Clarification is now resolved.

This artifact records the formal authorization decision only. It does **not** perform implementation.

---

## 1. Status

`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`

| Field | Value |
| ----- | ----- |
| Coding permitted now? | **No** |
| `authorization-status` | **Not set / not active** — no Implementation Authorization Record activated |
| Package meaning | New implementation is **not** authorized; approved core list→detail navigation is already satisfied per revalidated evidence |

---

## 2. Source Governance Chain

| Artifact | Path | Status / role |
| -------- | ---- | ------------- |
| Work Item Selection | `.specify/docs/handoff/next-approved-work-item-selection-request-list-detail-navigation-reactivated.md` | `NEXT_APPROVED_WORK_ITEM_SELECTED` |
| Feature Analysis | `.specify/docs/analysis/request-list-detail-navigation.feature-analysis.md` | `FEATURE_ANALYSIS_COMPLETED` |
| Review Decision | `.specify/docs/analysis/request-list-detail-navigation.review-decision.md` | `FEATURE_ANALYSIS_REVIEW_ACCEPTED` |
| Feature Analysis Validation | `.specify/docs/handoff/request-list-detail-navigation-feature-analysis-validation.md` | `REQUEST_LIST_DETAIL_NAVIGATION_FEATURE_ANALYSIS_REVALIDATED` |
| Feature Contract Decision | `.specify/docs/handoff/request-list-detail-navigation-feature-contract-decision.md` | `FEATURE_CONTRACT_NOT_REQUIRED` |
| Authorization Review | `.specify/docs/handoff/request-list-detail-navigation-authorization-review.md` | `AUTHORIZATION_REVIEW_READY` |
| Clarification Resolution | `.specify/docs/handoff/request-list-detail-navigation-authorization-clarification-decision.md` | `REQUEST_LIST_DETAIL_NAVIGATION_AUTHORIZATION_CLARIFICATION_RESOLVED` |
| Catalog / status mirrors | `.specify/docs/catalog-decisions.md`, `.specify/docs/spec-catalog.md` | Authority Map / inventory |
| Authority model | `.specify/governance/_meta/authority-model.md` | Authorization Record lifecycle vocabulary |

Supporting closeout evidence (not reopened): `docs/ui/closeouts/requests/request-list-detail-navigation.closeout.yaml`.

---

## 3. Authorized Objective

`Implement only the approved Request List Detail Navigation capability within the defined presentation/read boundary.`

**Decision application of that objective:** Under the resolved clarification, the approved capability (discoverable list→Request detail/read destination) is **already present** in the repository. Therefore this objective does **not** activate a new Implementation Authorization Record or coding permission.

---

## 4. Authorized Scope

**Activated `authorized-scope`:** **None.**

| Element | Decision |
| ------- | -------- |
| Required navigation entrypoint from Request list | Already evidenced as present; **not** authorized as new work |
| Required read/detail destination wiring | Already evidenced (`requests.show`); **not** authorized as new work |
| Required presentation changes for navigation | **None authorized** — no remaining surface under clarification |
| Required tests for authorized new behavior | **None authorized** — no new behavior authorized |

Clarification resolution (binding for this decision): no residual discoverability polish is defined in approved artifacts; residual polish must not be invented to create scope.

---

## 5. Forbidden Scope

Explicitly forbidden (non-goals):

- Request creation changes
- Request mutation changes
- Approval workflow changes
- Notification changes
- Employee integration changes
- Dependent integration changes
- Allocation changes
- Workflow redesign
- unrelated UI improvements
- architectural refactoring outside required scope
- inventing residual UX polish without separate selection + verbatim residual scope
- reopening frozen Request Show beyond existing read destination consumption
- treating this artifact as `authorization-status: active` / `partial`

---

## 6. Required Quality Gates

Not applicable to new implementation (none authorized).

Informational only — if a **future** separately selected residual item is later authorized under a different Authorization Record, expected gates would include:

- applicable feature tests for that residual only
- architecture checks (`composer run arch`) if Presentation/module boundaries are affected
- PHPStan (`php vendor/bin/phpstan analyse --no-progress` or `composer run phpstan`) if PHP changes
- Pint if PHP changes

Those gates are **not** activated by this decision.

---

## 7. Completion Evidence Required

For **this** work item under the current reactivation chain, completion evidence for the approved core intent is treated as **already recorded** by prior UI closeout + revalidated Feature Analysis evidence (list affordance, show destination, navigation tests).

No additional implementation completion package is authorized or required under this decision.

Any future residual item would require its own selection, clarification (if needed), Authorization Record, and completion evidence — not covered here.

---

## 8. Implementation Decision

`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`

Implementation Authorization is **not** granted because authorization requirements for a safe new `authorized-scope` are not satisfied: the clarification-resolved evidence shows the approved core capability is already delivered, and no residual scope exists in approved artifacts.

---

## 9. Next Allowed Governance Step

`Resolve remaining authorization blockers`

Remaining blocker for any future coding path: a **new** work item must be explicitly selected and verbatim-scoped if residual discoverability polish is desired. Until then, do **not** create an Implementation Execution Task for `Request List Detail Navigation` under this chain.

This reactivated work item’s core intent is closed as **already satisfied** with **no** Implementation Authorization.

---

## 10. Explicit Non-Implementation Statement

This artifact records the authorization decision only and does not perform implementation.

---

## 11. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

Only this authorization decision artifact was written/updated:

- `.specify/docs/handoff/request-list-detail-navigation-implementation-authorization-decision.md`

---

## Document Control

- Version: 2.0.0  
- Status: **`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`**  
- Work item: `Request List Detail Navigation`  
- Coding permitted: **No**  
- `authorization-status`: **not active**  
- Owner: Governance Review  
- Last Updated: 2026-07-11  
- Checkpoint: `request-list-detail-navigation-implementation-authorization-decision`
