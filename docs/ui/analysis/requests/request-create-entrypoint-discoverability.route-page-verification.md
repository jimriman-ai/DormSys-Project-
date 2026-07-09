# Request Create Entrypoint Discoverability — Route/Page Verification

## Verification Scope

This artifact verifies route/page existence only for the request create web route, page class, related Blade view, direct-access test evidence, and inspected UI discoverability surfaces.

## Route Evidence

### `requests.create`

- Found
- Exact file path: `app/Modules/Request/Presentation/Routes/web.php`
- Exact symbol or route definition: `Route::get('/create', RequestCreatePage::class)->name('requests.create');`

### `GET /requests/create`

- Found
- Exact file path: `routes/web.php`
- Exact symbol or route definition: `Route::prefix('requests')->group(RequestPresentationServiceProvider::requestWebRoutePath());`

- Found
- Exact file path: `app/Modules/Request/Presentation/Routes/web.php`
- Exact symbol or route definition: `Route::get('/create', RequestCreatePage::class)->name('requests.create');`

## Page Evidence

### `RequestCreatePage`

- Found
- Exact file path: `app/Modules/Request/Presentation/Livewire/RequestCreatePage.php`

### Blade view

- Found
- Exact file path: `resources/views/livewire/request/request-create-page.blade.php`

## Test Evidence

### Direct URL access

- `tests/Feature/Modules/Request/RequestUiFlowTest.php`
  - `it('renders the request create page', ...)`
  - Evidence: `GET /requests/create` followed by `->assertOk()->assertSee('ثبت درخواست شخصی');`

- `tests/Feature/Mutation/LivewireSessionMutationPrincipalTest.php`
  - `it('creates a personal request through the create page livewire flow after session login', ...)`
  - Evidence: `GET /requests/create` followed by `->assertOk();`

- `tests/Feature/Mutation/LivewireSessionMutationPrincipalTest.php`
  - `it('does not resolve a mutation principal for guests on request create livewire actions', ...)`
  - Evidence: `GET /requests/create` followed by `->assertRedirect('/login');`

- `tests/Feature/Mutation/LivewireSessionMutationPrincipalTest.php`
  - `it('persists a request owned by the authenticated identity employee after livewire save', ...)`
  - Evidence: `GET /requests/create` used before Livewire update assertions.

### Create page reachability

- `tests/Feature/Modules/Request/RequestUiFlowTest.php`
  - `it('renders the request create page', ...)`

- `tests/Feature/Mutation/LivewireSessionMutationPrincipalTest.php`
  - `it('creates a personal request through the create page livewire flow after session login', ...)`
  - Snapshot expectation includes `requests/create`.

### Request creation flow

- `tests/Feature/Modules/Request/RequestUiFlowTest.php`
  - `it('creates a request through the ui and shows it on the list', ...)`
  - Evidence: `Livewire::test(RequestCreatePage::class)...->call('save')->assertRedirect();`

- `tests/Feature/Mutation/LivewireSessionMutationPrincipalTest.php`
  - `it('creates a personal request through the create page livewire flow after session login', ...)`
  - Evidence: Livewire update to create-page snapshot, `save` call, redirect containing `/requests/`.

- `tests/Feature/Mutation/LivewireSessionMutationPrincipalTest.php`
  - `it('persists a request owned by the authenticated identity employee after livewire save', ...)`
  - Evidence: `GET /requests/create`, Livewire save, then persisted request assertion.

## UI Discoverability Evidence

### Request list page

- no link/button found
- Inspected file: `resources/views/livewire/request/request-list-page.blade.php`
- Observed links/actions: refresh button; `requests.show` link labeled `مشاهده`
- No `requests.create` link/button found in inspected file

### Request show page

- no link/button found
- Inspected file: `resources/views/livewire/request/request-show-page.blade.php`
- Observed links/actions: back link to `requests.index`
- No `requests.create` link/button found in inspected file

### Navigation/sidebar/layout

- no link/button found
- Inspected file: `resources/views/components/layouts/app.blade.php`
- Observed links/actions: app-name link to `requests.index`; nav link `درخواست‌ها` to `requests.index`
- No `requests.create` link/button found in inspected file

### Empty state/actions

- no link/button found
- Inspected file: `resources/views/livewire/request/request-list-page.blade.php`
- Observed empty state: `<x-ui.empty-state title="درخواستی ثبت نشده است" ... />`
- No create action slot or `requests.create` link/button found in inspected file

## Contradiction Resolution

CONFIRMED_PRESENT_BUT_NOT_DISCOVERABLE
