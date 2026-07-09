# Request List Filtering / Sorting / Pagination — Repository Inspection

## Feature
P4 — Request List Filtering / Sorting / Pagination

## Scope of Inspection
Inspected repository surfaces:
- request web routes in `app/Modules/Request/Presentation/Routes/web.php` and `routes/web.php`
- request API routes in `app/Modules/Request/Presentation/Routes/requests.php` and `routes/api.php`
- request list Livewire component in `app/Modules/Request/Presentation/Livewire/RequestListPage.php`
- request list Blade view in `resources/views/livewire/request/request-list-page.blade.php`
- request list read-contract, service, query, and response mapping paths
- request-related feature tests in `tests/Feature/Modules/Request/`
- concrete list-view precedent inspected in `resources/views/livewire/notification/notification-inbox-page.blade.php`
- request governance artifacts under `docs/ui/contracts/requests/`

## Confirmed Existing Request List Surfaces

### Routes
- Web request list route:
  - `app/Modules/Request/Presentation/Routes/web.php`
  - `Route::get('/', RequestListPage::class)->name('requests.index');`
- Web route mounting:
  - `routes/web.php`
  - authenticated middleware group mounts `Route::prefix('requests')->group(RequestPresentationServiceProvider::requestWebRoutePath());`
- API request list route:
  - `app/Modules/Request/Presentation/Routes/requests.php`
  - `Route::get('/mine', [RequestFlowController::class, 'indexMine'])->name('requests.flow.mine');`
- API route mounting:
  - `routes/api.php`
  - authenticated middleware group mounts `Route::prefix('requests')->group(RequestPresentationServiceProvider::requestRoutePath());`

### Page / Component Classes
- `app/Modules/Request/Presentation/Livewire/RequestListPage.php`
  - Livewire full-page component for the web request list
  - `render()` returns `view('livewire.request.request-list-page')`
  - `refreshList()` loads request data

### Blade Views
- `resources/views/livewire/request/request-list-page.blade.php`
  - request list page view

### Query / Data Source Paths
- `app/Modules/Request/Presentation/Livewire/RequestListPage.php`
  - `refreshList()` resolves employee id through `RequestPrincipalEmployeeResolver::requireEmployeeId()`
  - `refreshList()` calls `RequestReadContract::listByEmployee($employeeId)`
  - response rows are mapped through `RequestApiResponseFactory::serializeSummary()` and local `mapContractRow()`
- `app/Modules/Request/Application/Contracts/RequestReadContract.php`
  - declares `listByEmployee(string $employeeId): array`
- `app/Modules/Request/Application/Services/RequestReadService.php`
  - `listByEmployee()` delegates directly to `RequestReadQueryPort::listByEmployee()`
- `app/Modules/Request/Infrastructure/Queries/RequestReadQuery.php`
  - `listByEmployee()` queries `RequestModel`
  - applies `where('employee_id', $employeeId)`
  - applies `orderByDesc('created_at')`
  - executes `get()`
- `app/Modules/Request/Presentation/Http/Controllers/RequestFlowController.php`
  - `indexMine()` exposes API list data by calling `RequestReadContract::listByEmployee($employeeId)`

### Tests
- `tests/Feature/Modules/Request/RequestUiFlowTest.php`
- `tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php`

## Current Request List Behavior

### Rendering
- The list view uses `wire:init="refreshList"` to trigger initial loading.
- The page header title is `درخواست‌های من`.
- Header actions currently include:
  - create entrypoint to `route('requests.create')`
  - refresh button wired to `refreshList`
- The ready-state view renders a single HTML table.
- Rendered table columns in `resources/views/livewire/request/request-list-page.blade.php`:
  - `کد`
  - `نوع`
  - `وضعیت`
  - `شناسه خوابگاه`
  - `تاریخ ورود`
  - `تاریخ خروج`
  - `تاریخ ثبت`
  - `مشاهده`
- Row data is rendered from mapped keys:
  - `code`
  - `type`
  - `status`
  - `dormitory_id`
  - `check_in_date`
  - `check_out_date`
  - `submitted_at`
  - `id` is used for row key and show-link route parameter

### Filtering
- No filtering control was found in `RequestListPage`.
- No filter-related public property, method parameter, query-string state, or filtering branch was found in `app/Modules/Request/Presentation/Livewire/RequestListPage.php`.
- No filter UI control was found in `resources/views/livewire/request/request-list-page.blade.php`.
- `tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php` includes `->assertDontSee('فیلتر')`.
- `docs/ui/contracts/requests/request-list.feature-contract.yaml` records `filtering` as out of scope.
- `docs/ui/contracts/requests/request-list.implementation-lock.yaml` records `filter_list` as a forbidden action.

### Sorting
- No sorting control was found in `RequestListPage`.
- No sort-related public property, method parameter, query-string state, or sorting branch was found in `app/Modules/Request/Presentation/Livewire/RequestListPage.php`.
- No sort UI control was found in `resources/views/livewire/request/request-list-page.blade.php`.
- `tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php` includes `->assertDontSee('مرتب‌سازی', escape: false)`.
- `docs/ui/contracts/requests/request-list.feature-contract.yaml` records `sorting` as out of scope.
- `docs/ui/contracts/requests/request-list.implementation-lock.yaml` records `sort_list` as a forbidden action.

### Pagination
- No pagination control was found in `RequestListPage`.
- `RequestListPage` does not use `WithPagination`.
- No `page`, `perPage`, `paginate()`, `simplePaginate()`, `cursorPaginate()`, `links()`, or pagination component usage was found in inspected request-list files.
- `RequestReadQuery::listByEmployee()` executes `get()` and returns the full result set for the employee query.
- `docs/ui/contracts/requests/request-list.feature-contract.yaml` records `pagination` as out of scope and states `No filters, sorting, pagination, or export behavior exists in implementation.`
- `docs/ui/contracts/requests/request-list.implementation-lock.yaml` records `paginate_list` as a forbidden action.

### Empty States
- `RequestListPage` sets `uiState` to:
  - `loading`
  - `empty`
  - `ready`
  - `error`
- The empty state in `resources/views/livewire/request/request-list-page.blade.php` renders:
  - title: `درخواستی ثبت نشده است`
  - description: `هنوز درخواستی برای نمایش وجود ندارد.`
- The inspected request list view does not render a filter-specific empty state, filtered-result empty state, or pagination-empty branch.

### URL / State Handling
- No filter state, sort state, page state, or query-string synchronization was found in `RequestListPage`.
- No URL parameter handling for filtering, sorting, or pagination was found in inspected request-list routes or component code.

## Supporting Backend / Query Evidence
- `app/Modules/Request/Application/Contracts/RequestReadContract.php` exposes only:
  - `listByEmployee(string $employeeId): array`
  - no filter, sort, page, cursor, or per-page arguments
- `app/Modules/Request/Application/Services/RequestReadService.php` passes through the same unparameterized read call
- `app/Modules/Request/Infrastructure/Queries/RequestReadQuery.php`
  - constrains by `employee_id`
  - applies fixed ordering via `orderByDesc('created_at')`
  - returns all rows via `get()`
- `app/Modules/Request/Presentation/Http/Controllers/RequestFlowController.php`
  - `indexMine()` returns the same unparameterized list data from `listByEmployee($employeeId)`

Observed constraints from inspected code:
- current read-contract signature does not expose filter inputs
- current read-contract signature does not expose sort inputs
- current read-contract signature does not expose pagination inputs
- current query path applies one built-in ordering rule: descending `created_at`

## Existing UI Pattern Evidence
- `resources/views/livewire/notification/notification-inbox-page.blade.php` is a concrete list-view precedent with the same structural pattern:
  - `wire:init="refreshList"`
  - page header with refresh action
  - loading / error / empty / ready render states
  - plain HTML table in ready state
  - `x-ui.empty-state` for empty state
- In the inspected notification list view, no filter controls, sort controls, or pagination controls were found.
- No shared Blade component files matching filter or pagination naming patterns were found under `resources/views/components/`.
- No `WithPagination` trait usage or Eloquent pagination method usage was found under inspected `app/Modules/` files.

## Test Evidence
- `tests/Feature/Modules/Request/RequestUiFlowTest.php`
  - covers guest redirect from `/requests`
  - covers authenticated render of the request list page
  - covers empty-state Livewire render after `refreshList()`
  - covers create-through-UI then request visibility on the list
  - does not contain assertions for filtering behavior
  - does not contain assertions for sorting behavior
  - does not contain assertions for pagination behavior
  - does not assert relative ordering of multiple request rows
- `tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php`
  - covers row-level navigation to request show
  - includes negative assertions that request list does not show `فیلتر`
  - includes negative assertions that request list does not show `مرتب‌سازی`
  - does not contain pagination assertions
  - does not contain filter-application assertions
  - does not contain sort-order assertions across multiple rows

## Governance Artifact Check
- No existing artifact was found for the exact feature name `request-list-filtering-sorting-pagination` under `docs/ui/`.
- Related request-list governance artifacts exist:
  - `docs/ui/contracts/requests/request-list.feature-contract.yaml`
  - `docs/ui/contracts/requests/request-list.implementation-lock.yaml`
  - `docs/ui/contracts/requests/request-list-detail-navigation.feature-contract.yaml`
  - `docs/ui/contracts/requests/request-list-detail-navigation.implementation-lock.yaml`
- In the approved request-list contract and implementation lock:
  - filtering is recorded out of scope
  - sorting is recorded out of scope
  - pagination is recorded out of scope

## Gaps Observed From Inspection
- No request-list filter controls were found.
- No request-list sort controls were found.
- No request-list pagination controls were found.
- No request-list component properties or methods were found for filter state, sort state, or page state.
- No request-list read-contract parameters were found for filtering, sorting, or pagination.
- No request-list query implementation was found that applies dynamic filtering.
- No request-list query implementation was found that applies dynamic sorting.
- No request-list query implementation was found that uses paginated query execution.
- No request-list tests were found that verify filtering behavior.
- No request-list tests were found that verify sorting behavior.
- No request-list tests were found that verify pagination behavior.

## Open Evidence Questions
- Whether any non-request module UI precedent exists for filter bars, sort controls, or paginated tables outside the inspected concrete view precedent set
- Whether any API consumer outside the inspected request list surfaces depends on the fixed `orderByDesc('created_at')` behavior of `RequestReadQuery::listByEmployee()`
- Whether any repository artifact outside the inspected request governance files documents expected future filtering, sorting, or pagination behavior for the request list
