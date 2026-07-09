# Request List Filtering / Sorting / Pagination — Review Decision

## Feature

P4 — Request List Filtering / Sorting / Pagination

## Inputs Reviewed

- `docs/ui/analysis/requests/request-list-filtering-sorting-pagination.repo-inspection.md`
- `docs/ui/analysis/requests/request-list-filtering-sorting-pagination.feature-analysis.md`

## Decision Status

**CONTRACT_REQUIRED_BEFORE_IMPLEMENTATION**

## Decision Summary

P4 is classified as a **mixed UI and read/query gap**, not a presentation-only enhancement. Repository evidence shows filtering, sorting, and pagination are absent at the UI, component-state, and read-query layers, while the current list path uses a fixed query shape (`employee_id` constraint, `orderByDesc('created_at')`, full `get()` result set).

Approved request-list governance artifacts explicitly exclude filtering, sorting, and pagination from scope and forbid the corresponding list actions in the implementation lock. Existing tests negatively assert the absence of filter and sort UI labels.

Implementation is **not authorized** under current governance. Scope and read semantics must be explicitly reopened through a new or revised contract before any P4 work proceeds.

## Evidence Considered

### Current fixed query behavior

- `RequestReadContract::listByEmployee(string $employeeId): array` accepts only an employee id.
- `RequestReadQuery::listByEmployee()` constrains by `employee_id`, applies `orderByDesc('created_at')`, and executes `get()` to return the full result set.
- `RequestFlowController::indexMine()` uses the same unparameterized read path for the API list.

### Absence of filter / sort / pagination in UI and state

- `RequestListPage` has no filter, sort, or page public properties, method parameters, or query-string state.
- `request-list-page.blade.php` has no filter controls, sort controls, or pagination controls.
- `RequestListPage` does not use `WithPagination`.
- No URL parameter handling for filter, sort, or page state exists in inspected request-list routes or component code.

### Pagination absence at query execution level

- No `paginate()`, `simplePaginate()`, `cursorPaginate()`, or pagination metadata path exists in the inspected request-list query.
- Pagination is not merely a missing UI control; the read path returns all rows for the employee.

### Existing contract exclusions

- `docs/ui/contracts/requests/request-list.feature-contract.yaml` records `filtering`, `sorting`, and `pagination` as out of scope.
- The same contract states: `No filters, sorting, pagination, or export behavior exists in implementation.`
- `docs/ui/contracts/requests/request-list-detail-navigation.feature-contract.yaml` also records filtering, sorting, pagination, search, and export as out of scope for that feature boundary.

### Existing lock forbidden actions

- `docs/ui/contracts/requests/request-list.implementation-lock.yaml` records `filtering`, `sorting`, and `pagination` as out of scope.
- The same lock forbids `filter_list`, `sort_list`, and `paginate_list`.
- `docs/ui/contracts/requests/request-list-detail-navigation.implementation-lock.yaml` forbids list filtering/sorting/search/pagination changes within its boundary.

### Current tests that assert absence

- `RequestListDetailNavigationUiFlowTest` includes `assertDontSee('فیلتر')` and `assertDontSee('مرتب‌سازی')`.
- Inspected request-list tests contain no assertions for filter application, sort order across multiple rows, or pagination behavior.

### Feature analysis classification

- Feature analysis outcome: `MIXED_UI_AND_READ_MODEL_GAP`.
- Feature analysis concludes direct UI implementation alone is not supported by evidence as sufficient.

## Governance Conflict Assessment

**P4 conflicts with existing approved request-list governance artifacts.**

### Contract conflict

**Yes.** Approved `request-list.feature-contract.yaml` explicitly excludes filtering, sorting, and pagination from implemented scope. P4 targets exactly those excluded capabilities. Implementing P4 without contract revision would contradict the approved scope boundary.

### Implementation-lock conflict

**Yes.** Approved `request-list.implementation-lock.yaml` forbids `filter_list`, `sort_list`, and `paginate_list`. P4 implementation would require those actions (or equivalent behavior) unless governance is reopened and the lock boundary is reconsidered after contract resolution.

### Test expectation conflict

**Yes.** Existing tests assert the request list does not display filter or sort UI labels. Adding P4 controls would intersect with those negative assertions unless governed test expectations are updated as part of an approved scope change.

## Scope Classification

**Mixed UI and read/query change**

Evidence basis:

- **Presentation gap:** no filter, sort, or pagination controls; no filtered-empty or pagination-specific empty branches.
- **Component state gap:** no filter values, sort selection, or page state in `RequestListPage`.
- **Read/query gap:** unparameterized read contract; fixed ordering; full result-set retrieval via `get()`; no dynamic filter, sort, or paginated query branches.

P4 is not UI-only. It is also not unresolved at the classification level; the mixed gap is supported by inspected code paths in both presentation and read layers.

## Contract Requirement Assessment

**A contract is required before implementation.**

Reasons:

- **Changed query semantics:** filtering, sorting, and pagination each alter which rows are returned, in what order, and in what slice. That is read-model behavior beyond the current fixed `listByEmployee()` contract.
- **State/URL behavior:** no inspected request-list code governs how filter, sort, or page state is held, defaulted, reset, or synchronized to URL. These rules must be defined before implementation.
- **Pagination semantics:** current query returns the full employee set. Pagination requires explicit agreement on page size, boundary behavior, and whether pagination metadata is part of the read response.
- **Sort/filter interaction rules:** which fields are filterable, which columns are sortable, default sort relative to the current `orderByDesc('created_at')` baseline, and apply/reset behavior are not recorded in approved artifacts.
- **Existing approved scope boundaries:** the approved request-list contract and lock currently define P4 capabilities as out of scope and forbidden. A new or revised contract is required to reopen that boundary deliberately.

No P4-specific contract artifact exists today under `docs/ui/` for the exact feature name `request-list-filtering-sorting-pagination`.

## Lock Assessment

**The current implementation lock would need reconsideration after contract resolution.**

The approved `request-list.implementation-lock.yaml` forbids `filter_list`, `sort_list`, and `paginate_list`. If a contract authorizes P4, the lock boundary must be revisited to permit the governed actions and to align forbidden/permitted behavior with the new contract. This review decision does not modify the lock.

## Direct Implementation Assessment

**Not authorized. Blocked pending contract/governance update.**

Implementation may not proceed under current approved request-list contract and lock boundaries. P4 requires explicit governance reopening before contract drafting, lock reconsideration, and implementation authorization.

## Open Questions

No additional repository evidence is required to reach this decision status. The gap classification and governance conflict are sufficiently supported by the reviewed inspection and feature analysis.

The following questions remain for **contract drafting**, not for reversing this decision:

1. Whether P4 scope includes the shared API path (`indexMine`) or is limited to the web `RequestListPage` surface.
2. Which filter dimensions, sortable columns, default sort, and page-size rules are in scope.
3. Whether filter/sort/page state is URL-synchronized or component-local only.
4. How filtered-empty results differ from the generic no-requests empty state.
5. Whether P4 is delivered as a new contract artifact or as a revision/amendment to the approved request-list contract boundary.

## Recommended Next Step

**Draft contract**

A P4-specific contract (or an explicitly governed revision to request-list scope) must be drafted to define in-scope capabilities, read/query semantics, state behavior, empty-state rules, API boundary, and the relationship to existing request-list governance before lock reconsideration or implementation.
