# Implementation Authorization Decision — Request List Detail Navigation (Reissue After Revised Residual Scope Review)

**Artifact class:** Implementation Authorization Decision (Authority Map–aligned decision record)  
**Decision date:** 2026-07-11  
**Checkpoint:** `request-list-detail-navigation-implementation-authorization-decision`  
**Record version:** 4.0.0 (reissue after revised residual scope authorization review)

**Canonical map:** `.specify/docs/catalog-decisions.md` § `## Governance Decision Authority Map`  
**Lifecycle:** `.specify/governance/_meta/authority-model.md`  
**Execution:** `.specify/governance/execution-policy.md`

**Historical preservation:**

- Prior post–blocker-resolution denial: [request-list-detail-navigation-implementation-authorization-decision.v3-pre-revised-scope-reissue.md](./request-list-detail-navigation-implementation-authorization-decision.v3-pre-revised-scope-reissue.md)
- Earlier archived denial: [request-list-detail-navigation-implementation-authorization-decision.v2-pre-reissue.md](./request-list-detail-navigation-implementation-authorization-decision.v2-pre-reissue.md)

This artifact records an authorization decision only. It does **not** perform implementation. Authorization Review readiness and owner residual-scope acceptance are **not** automatic approval to implement.

---

## 1. Final Authorization Status

`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`

| Field | Value |
| ----- | ----- |
| Coding permitted now? | **No** |
| `authorization-status` | **Not set / not active** — no Implementation Authorization Record activated |
| Package meaning | Reissue after revised residual scope review: denial **reaffirmed**; owner In Scope maps to already-satisfied core; no safe new `authorized-scope` without inventing polish |

---

## 2. Authorization Basis

| Step | Status / artifact |
| ---- | ----------------- |
| Previous denial state | `REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED` (v3 — empty `authorized-scope` / already-satisfied core) |
| Denial analysis outcome | `REQUEST_LIST_DETAIL_NAVIGATION_AUTHORIZATION_DENIAL_ANALYZED` — scope revision required before grant reconsideration |
| Scope revision acceptance | `REQUEST_LIST_DETAIL_NAVIGATION_SCOPE_REVISION_ACCEPTED` — owner `D-01 = APPROVE_RESIDUAL_SCOPE` |
| Authorization review readiness | `REQUEST_LIST_DETAIL_NAVIGATION_AUTHORIZATION_REVIEW_READY` — revised-scope review; recommendation `READY_FOR_IMPLEMENTATION_AUTHORIZATION_DECISION` (readiness only) |

Controlling revised-scope inputs:

- `.specify/docs/handoff/request-list-detail-navigation-scope-revision-owner-decision.md`
- `.specify/docs/handoff/request-list-detail-navigation-authorization-review-revised-scope.md`

Supporting chain: Work Item Selection → Feature Analysis → Feature Analysis Review → Feature Contract Decision (`FEATURE_CONTRACT_NOT_REQUIRED`) → prior IA / Clarification / Denial Analysis.

---

## 3. Authorization Decision Question

`Can implementation proceed within the approved revised residual scope without exceeding governance boundaries?`

**Answer:** **No.**

Owner-accepted In Scope (list→detail navigation, read-only detail presentation, existing request data, no mutation / no new business behavior) is the **same core capability** already evidenced as present and closeout-recorded. Speculative UI polish remains **Out of Scope**. Granting Implementation Authorization would either activate coding with an empty new change set or require inventing polish — both exceed safe governance boundaries.

---

## 4. Authorized Objective

Not activated.

**Remaining blocker (scope / implementation safety):** Under the approved revised residual In Scope, no **new** safe implementable surface remains. Feature Analysis, Feature Analysis Review, Clarification, and prior IA evidence show Request List **مشاهده** → `requests.show`, ownership on show, navigation tests, and UI closeout already complete. Owner `D-01` affirms residual-gap product intent and lifecycle continuation, but does not define a distinct residual deliverable beyond that already-satisfied core, and forbids speculative polish. Therefore coding cannot proceed without exceeding governance boundaries.

---

## 5. Authorized Scope

**Activated `authorized-scope`:** **None.**

### Allowed (historical / owner In Scope — not activated for new coding)

- Request List to Request Detail navigation
- read-only detail rendering
- use of existing approved data
- required supporting changes strictly necessary for this capability

### Not allowed

- mutations
- create/edit/update flows
- workflow transitions
- approval actions
- notification changes
- allocation changes
- Employee integration changes
- Dependent integration changes
- new business rules / new domain behavior
- write-side behavior
- unrelated UI improvements
- speculative UI polish
- inventing residual UX to fill `authorized-scope`
- treating review readiness or `D-01` alone as Implementation Authorization

---

## 6. Required Quality Gates

Not applicable to new implementation (none authorized).

Informational only if a **future** Authorization Record activates a distinct, verbatim residual change set:

- applicable feature tests for that residual only
- architecture checks (`composer run arch`) if Presentation/module boundaries affected
- PHPStan (`php vendor/bin/phpstan analyse --no-progress` or `composer run phpstan`) if PHP changes
- Pint if PHP changes

Those gates are **not** activated by this decision.

---

## 7. Implementation Constraints

- minimal change only
- no speculative refactoring
- no scope expansion
- preserve existing governance boundaries

These constraints remain binding for any future separately authorized residual; they do not authorize work under this decision.

---

## 8. Next Allowed Governance Step

`Resolve remaining authorization blocker`

Remaining blocker disposition options (non-authorizing; owner/product/architecture):

1. **Close or defer** `Request List Detail Navigation` as already satisfied for core list→detail navigation (no Implementation Authorization / no Implementation Execution Task), **or**  
2. **Explicitly select and verbatim-scope** a residual discoverability item **distinct** from the already-delivered core and **not** speculative polish excluded by Out of Scope — then return through Authorization Review / IA for that residual only.

Until a safe new `authorized-scope` exists, do **not** create an Implementation Execution Task for `Request List Detail Navigation`.

---

## 9. Explicit Non-Implementation Statement

`This artifact records authorization status only and does not perform implementation.`

---

## 10. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

Artifacts written for this reissue:

- `.specify/docs/handoff/request-list-detail-navigation-implementation-authorization-decision.md` (this record)
- `.specify/docs/handoff/request-list-detail-navigation-implementation-authorization-decision.v3-pre-revised-scope-reissue.md` (archived prior decision; history preservation)

---

## Document Control

- Version: 4.0.0  
- Status: **`REQUEST_LIST_DETAIL_NAVIGATION_IMPLEMENTATION_NOT_AUTHORIZED`**  
- Work item: `Request List Detail Navigation`  
- Coding permitted: **No**  
- `authorization-status`: **not active**  
- Owner: Governance Review  
- Last Updated: 2026-07-11  
- Checkpoint: `request-list-detail-navigation-implementation-authorization-decision`
