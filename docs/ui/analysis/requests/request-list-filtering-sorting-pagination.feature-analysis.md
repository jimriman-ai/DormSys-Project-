# Request List Filtering / Sorting / Pagination — Feature Analysis

## Feature

P4 — Request List Filtering / Sorting / Pagination

## Input Reviewed

- `docs/ui/analysis/requests/request-list-filtering-sorting-pagination.repo-inspection.md`

## Analysis Outcome

**MIXED_UI_AND_READ_MODEL_GAP**

Repository evidence shows the request list is implemented and functional, but filtering, sorting, and pagination are absent at both the presentation layer and the read/query layer. The current list path uses a fixed query shape (`where employee_id` + `orderByDesc('created_at')` + `get()`), with no component state, URL state, read-contract parameters, or tests for any of the three capabilities. P4 is therefore not a presentation-only enhancement.

## Confirmed Current State

### Route / page / component presence

- Web list route exists: `GET /requests` → `RequestListPage` (`requests.index`), mounted under authenticated middleware in `routes/web.php`.
- API list route exists: `GET /api/requests/mine` → `RequestFlowController::indexMine`, calling the same read contract.
- Livewire full-page component `RequestListPage` exists and renders `request-list-page.blade.php`.
- Initial load uses `wire:init="refreshList"`.

### Current rendered list behavior

- Ready state renders a single HTML table with columns: code, type, status, dormitory id, check-in date, check-out date, submitted date, and a show link.
- Header includes create entrypoint (`route('requests.create')`) and a refresh button.
- UI states: `loading`, `empty`, `ready`, `error`.
- Empty state text is generic (`درخواستی ثبت نشده است` / `هنوز درخواستی برای نمایش وجود ندارد.`) with no filter-specific or pagination-specific empty branch.

### Fixed ordering behavior

- `RequestReadQuery::listByEmployee()` applies `orderByDesc('created_at')` before `get()`.
- No alternate sort path, sort parameter, or sort branch exists in inspected request-list code.
- This is repository evidence that list ordering is fixed today and not user-configurable.

### Filter controls

- **Absent.** No filter UI in the Blade view.
- No filter-related public properties, method parameters, query-string state, or filtering branches in `RequestListPage`.
- Tests assert the list does not show `فیلتر`.

### Sort controls

- **Absent.** No sort UI in the Blade view.
- No sort-related public properties, method parameters, query-string state, or sorting branches in `RequestListPage`.
- Tests assert the list does not show `مرتب‌سازی`.

### Pagination behavior

- **Absent.** `RequestListPage` does not use `WithPagination`.
- No `page`, `perPage`, `paginate()`, `simplePaginate()`, `cursorPaginate()`, `links()`, or pagination component usage in inspected request-list files.
- Query path returns the full employee result set via `get()`.

### Current test coverage shape

- `RequestUiFlowTest`: guest redirect, authenticated render, empty state after `refreshList`, create-then-visible-on-list flow. No filter, sort, pagination, or multi-row ordering assertions.
- `RequestListDetailNavigationUiFlowTest`: row navigation to show; negative assertions for `فیلتر` and `مرتب‌سازی`; no pagination, filter-application, or sort-order assertions.

### Governance baseline (related, not P4-specific)

- No artifact exists for the exact feature name `request-list-filtering-sorting-pagination`.
- Approved `request-list.feature-contract.yaml` records filtering, sorting, and pagination as **out of scope**.
- Approved `request-list.implementation-lock.yaml` records `filter_list`, `sort_list`, and `paginate_list` as **forbidden actions**.

## Gap Analysis by Capability

### Filtering

| Dimension | Finding |
|---|---|
| **Confirmed present** | List renders row fields that could theoretically serve as filter dimensions (type, status, dates, dormitory id, code). Read path returns all rows for the authenticated employee. |
| **Confirmed absent** | Filter UI controls; component filter state; URL/query-string filter state; `RequestReadContract::listByEmployee()` filter parameters; dynamic filter branches in `RequestReadQuery`; filter-specific empty state; filter behavior tests. |
| **Layers affected** | **Presentation** (controls, filter UX, filtered empty state), **component state** (filter values, apply/reset behavior), **query/read behavior** (contract signature and query must accept and apply filter criteria), **test layer** (filter application, empty-filtered-result, and negative assertions currently asserting absence of filter UI). |

Filtering absence is **not** only a missing-control issue. Inspected read-contract and query paths expose no filter inputs and no dynamic filtering logic. Any real filtering capability requires read/query behavior changes in addition to UI and component state.

### Sorting

| Dimension | Finding |
|---|---|
| **Confirmed present** | Fixed descending `created_at` ordering in `RequestReadQuery::listByEmployee()`. Table columns exist that could correspond to sortable display fields. |
| **Confirmed absent** | Sort UI controls; component sort state; URL/query-string sort state; sort parameters on read contract; alternate or dynamic sort branches in query; sort behavior tests; assertions on relative row order across multiple requests. |
| **Layers affected** | **Presentation** (sort controls, sort labels/direction indicators), **component state** (selected column, direction), **query/read behavior** (contract and query must accept sort field/direction and apply them instead of the single hard-coded rule), **test layer** (sort order verification; current tests assert sort UI is not shown). |

The fixed `orderByDesc('created_at')` is direct repository evidence that sorting is **not configurable today**. Sorting is therefore a mixed gap: the built-in order exists, but user-directed sorting does not.

### Pagination

| Dimension | Finding |
|---|---|
| **Confirmed present** | Full result-set retrieval and table rendering for all employee requests. Generic empty state when the full set is empty. |
| **Confirmed absent** | Pagination UI; `WithPagination` on component; page/per-page state; URL page state; paginated query execution (`paginate`, `simplePaginate`, `cursorPaginate`); pagination links/components; pagination behavior tests. |
| **Layers affected** | **Presentation** (page controls, page-size UX if applicable), **component state** (current page, per-page), **query/read behavior** (contract and query must limit/slice result sets and return pagination metadata), **test layer** (page navigation, boundary rows, empty page behavior). |

Pagination absence is **not** only a missing-control issue. The query uses `get()` and returns the complete employee result set. Pagination requires read/query behavior changes, not merely adding UI around the existing full list.

## Architectural Scope Assessment

P4 crosses beyond presentation scope.

Evidence supporting this assessment:

1. **Read contract is unparameterized.** `RequestReadContract::listByEmployee(string $employeeId): array` accepts only an employee id. No filter, sort, page, cursor, or per-page arguments exist in the inspected signature.
2. **Query behavior is fixed.** `RequestReadQuery` constrains by `employee_id`, orders by `created_at` descending, and returns all rows. There is no inspected path for dynamic filter, sort, or paginated execution.
3. **API shares the same read path.** `RequestFlowController::indexMine()` uses the same unparameterized `listByEmployee()` call, so web list changes that alter read semantics may affect API consumers unless scope is explicitly bounded.
4. **Component has no state hooks.** `RequestListPage` has no properties or methods for filter, sort, or page state, and no URL synchronization for those dimensions.
5. **UI precedent does not close the read-model gap.** The inspected notification inbox list follows the same structural pattern (init refresh, table, empty/ready states) and also lacks filter, sort, and pagination controls. That precedent supports UI patterns but does not provide an in-repo paginated/filterable read-model example under `app/Modules/`.
6. **No shared filter/pagination Blade components were found** under `resources/views/components/`, and no `WithPagination` usage was found under inspected `app/Modules/` files.

P4 therefore spans presentation, component interaction state, and read/query semantics. It is not evidenced as a thin UI layer over an already-parameterized list query.

## Governance Implication

Current evidence indicates **a contract is likely required before implementation**.

Reasons:

- **Query semantics would change.** Filter, sort, and pagination each alter what rows are returned, in what order, and in what slice. That is read-model behavior, not render-only work.
- **URL/state behavior is undefined and currently absent.** No inspected request-list code handles query-string or URL state for filters, sort, or page. Contract-level decisions are likely needed for persistence, defaults, and shareability of list state.
- **UX interaction rules are ungoverned for P4.** Which fields are filterable, which columns are sortable, default sort, page size, and apply/reset behavior are not recorded in an approved P4 artifact.
- **Empty-state behavior under filters is absent.** Only a generic no-requests empty state exists. Filtered-empty behavior is not evidenced and would need explicit governance if in scope.
- **Test expectations conflict with addition.** Existing tests negatively assert absence of filter and sort UI labels. Approved request-list contract and lock explicitly exclude filtering, sorting, and pagination and forbid `filter_list`, `sort_list`, and `paginate_list`.

Direct UI implementation alone is **not** supported by evidence as sufficient. More evidence is **not** required to classify the gap type, but governance resolution is required before implementation because approved artifacts currently forbid the feature capabilities P4 would introduce.

## Risks of Premature Implementation

1. **Read-model changes without contract clarity** could introduce incompatible list semantics across web UI and `indexMine` API if both consume an expanded read contract without an agreed boundary.
2. **Violating approved request-list lock** by implementing `filter_list`, `sort_list`, or `paginate_list` while those actions remain forbidden in `request-list.implementation-lock.yaml`.
3. **UI-only pagination or filtering over the full in-memory list** would sidestep the evidenced `get()` query shape and could create performance or authority-boundary drift if done in the component without backend read support.
4. **Test regression from existing negative assertions** (`assertDontSee('فیلتر')`, `assertDontSee('مرتب‌سازی')`) if controls are added without updating governed expectations.
5. **Ambiguous empty-state behavior** if filters return zero rows but the generic empty state implies the employee has no requests at all.
6. **Undefined default sort** if UI sort is added without resolving the relationship to the current fixed `orderByDesc('created_at')` baseline.

## Recommended Next Governance Step

**Review decision**

Evidence is sufficient to classify P4 as a mixed UI and read-model gap and to determine that approved governance currently excludes and forbids the target capabilities. The next step is a review decision to establish disposition (contract required, scope boundaries, relationship to existing request-list contract/lock, and whether API read semantics are in scope) before contract drafting or implementation.
