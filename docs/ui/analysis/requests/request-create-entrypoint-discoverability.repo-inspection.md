# Request Create Entrypoint Discoverability — Repository Inspection

## Feature

P3 — Request Create Entrypoint Discoverability

## Inspection Scope

This inspection is limited to repository-observable facts about request-create entrypoint discoverability.

Sources inspected: application routes, Livewire components, Blade views, layout/navigation surfaces, authorization-related classes, feature tests, and Request List governance text that references create entrypoint scope.

This file is an inspection artifact only. It does not record governance conclusions for P3.

No dedicated P3 contract, lock, decision, closeout, or analysis artifact was found under `docs/ui/` for **Request Create Entrypoint Discoverability** (confirmed in `docs/ui/analysis/feature-status-repository-inspection.md`).

---

## Findings Summary

- Request creation capability is present in the repository via web route `requests.create`, Livewire `RequestCreatePage`, application action `CreatePersonalRequestAction`, and API route `requests.flow.create-personal`.
- Direct navigation to `GET /requests/create` is reachable when authenticated (confirmed by tests).
- No link, button, or menu item pointing to `route('requests.create')` or `/requests/create` was found in inspected Blade views for list, show, layout navigation, or empty states.
- Tests on the request list page explicitly assert absence of the string `ثبت درخواست جدید`.
- Request List governance artifacts list `create request entrypoint` as out-of-scope for Request List.

---

## Repository Evidence

### Routes

**Web (authenticated group in `routes/web.php`, prefix `requests` from `app/Modules/Request/Presentation/Routes/web.php`):**

| Named route | Method | Path | Handler |
|---|---|---|---|
| `requests.create` | GET | `/requests/create` | `App\Modules\Request\Presentation\Livewire\RequestCreatePage` |
| `requests.index` | GET | `/requests` | `RequestListPage` |
| `requests.show` | GET | `/requests/{requestId}` | `RequestShowPage` |
| `home` | redirect | `/` → `/requests` | — |

**API (`routes/api.php`, prefix `requests`, file `app/Modules/Request/Presentation/Routes/requests.php`):**

| Named route | Method | Path | Handler |
|---|---|---|---|
| `requests.flow.create-personal` | POST | `/api/requests/personal` | `RequestFlowController::storePersonal` |

No other web or API route names containing `create` were found under Request presentation route files inspected.

---

### Request Creation Implementation Surface

| Symbol | Path | Role |
|---|---|---|
| `RequestCreatePage` | `app/Modules/Request/Presentation/Livewire/RequestCreatePage.php` | Livewire full-page create form; method `save()` calls `CreatePersonalRequestAction::execute()` |
| `request-create-page` view | `resources/views/livewire/request/request-create-page.blade.php` | Create form UI; submit label `ثبت درخواست`; back link to `route('requests.index')` |
| `CreatePersonalRequestAction` | `app/Modules/Request/Application/Services/CreatePersonalRequestAction.php` | Application action; method `execute()` creates draft personal request |
| `RequestFlowController` | `app/Modules/Request/Presentation/Http/Controllers/RequestFlowController.php` | API create via `storePersonal()` |
| `CreatePersonalRequestRequest` | `app/Modules/Request/Presentation/Http/Requests/CreatePersonalRequestRequest.php` | API form request (file present; not fully inspected) |
| `CreatePersonalRequestCommand` | `app/Modules/Request/Presentation/Console/CreatePersonalRequestCommand.php` | Artisan command `request:create-personal` |

`RequestCreatePage::save()` redirects to `requests.show` on success via `$this->redirectRoute('requests.show', ...)`.

---

### UI Entrypoints Found

Search for `route('requests.create')`, `/requests/create`, and `requests.create` in `resources/views/**/*.blade.php` returned **no matches**.

Confirmed UI surfaces that **do not** link to create (inspected file content):

| Surface | File | Observed actions/links |
|---|---|---|
| Request list header | `resources/views/livewire/request/request-list-page.blade.php` | `wire:click="refreshList"` button (`بروزرسانی`) only in `<x-slot:actions>` |
| Request list empty state | same file | `<x-ui.empty-state>` without `action` slot |
| Request create page | `resources/views/livewire/request/request-create-page.blade.php` | Form submit + back link to `requests.index` (destination page, not an entrypoint) |
| Request show page | `resources/views/livewire/request/request-show-page.blade.php` | Back link to `requests.index` only |
| App header nav | `resources/views/components/layouts/app.blade.php` | Links to `requests.index` (`درخواست‌ها`); logout form |

**No confirmed button, link, nav item, or menu entry** leading to `requests.create` was found in inspected views.

---

### Expected UI Surfaces Inspected

| Surface | Result | Evidence |
|---|---|---|
| **Request list page** | No entrypoint found | `request-list-page.blade.php`: header actions contain refresh only; empty state has no action slot; no `route('requests.create')` |
| **Request show page** | No entrypoint found | `request-show-page.blade.php`: actions slot contains back-to-list link only |
| **Dashboard / home** | No dedicated dashboard surface found; home redirects to list | `routes/web.php`: `Route::redirect('/', '/requests')->name('home')`; no `/dashboard` route in `routes/` |
| **Primary navigation / sidebar / menus** | No entrypoint found | `components/layouts/app.blade.php`: single nav link `درخواست‌ها` → `requests.index` |
| **Empty states / page headers / action bars** | No create entrypoint found | List empty state: title `درخواستی ثبت نشده است`, no action; list header: title `درخواست‌های من`, refresh button only; `empty-state.blade.php` supports optional `$action` slot but list page does not pass it |

`resources/views/welcome.blade.php` contains a `Dashboard` link to `url('/dashboard')`; no matching dashboard route was found under `routes/`. Not confirmed as an authenticated request-creation surface.

---

### Authorization / Visibility Evidence

**Route access (web):**

- `requests.create` is registered inside the authenticated middleware group in `routes/web.php`: `['auth:api', 'request.mutation.principal', 'audit.principal']`.
- Guest redirect behavior for list is tested (`RequestUiFlowTest`: guest `GET /requests` → `/login`). Guest behavior for `GET /requests/create` was not found in inspected tests.

**Create execution (web Livewire):**

- `RequestCreatePage::save()` calls `RequestPrincipalEmployeeResolver::requireEmployeeId()` before `CreatePersonalRequestAction::execute()`.
- `RequestPrincipalEmployeeResolver` throws `UnauthorizedMutationException` when principal is missing or has no linked employee (`app/Modules/Request/Application/Services/RequestPrincipalEmployeeResolver.php`).

**Create execution (API):**

- `RequestFlowController::storePersonal()` calls `RequestPrincipalEmployeeResolver::requireEmployeeId()`.
- `RequestHttpFlowCompletionTest` includes `rejects create when principal has no linked employee` for `POST /api/requests/personal`.

**Mutation policy registry:**

- `CreatePersonalRequestAction` is listed in `PendingMutationAuthorizationRegistry::PENDING` (`app/Application/Mutation/Registry/PendingMutationAuthorizationRegistry.php`) as a grandfathered pending-authorization action.
- `CreatePersonalRequestAction` does not call `MutationPolicyEnforcementPoint` directly (inspected class body).

**UI visibility / role checks:**

- No Request-specific Policy or Gate class tied to create-page visibility was found in inspected `app/Modules/Request/` paths.
- No `hasRole`, `Gate::`, or `canCreate` checks were found in `RequestCreatePage`, `request-list-page.blade.php`, or `components/layouts/app.blade.php`.

**Governance text (Request List scope only):**

- `docs/ui/contracts/requests/request-list.feature-contract.yaml`: `scope.out_of_scope` includes `create request entrypoint`.
- `docs/ui/contracts/requests/request-list.implementation-lock.yaml`: `scope_authorization.out_of_scope` includes `create request entrypoint`.

---

### Test Evidence

| Test file | Relevant coverage |
|---|---|
| `tests/Feature/Modules/Request/RequestUiFlowTest.php` | `GET /requests/create` returns 200 and sees `ثبت درخواست شخصی`; creates request via `RequestCreatePage` Livewire `save()`; list page `assertDontSee('ثبت درخواست جدید')` on initial HTTP GET |
| `tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php` | Populated list `assertDontSee('ثبت درخواست جدید')` |
| `tests/Feature/Mutation/LivewireSessionMutationPrincipalTest.php` | `GET /requests/create` exercised in session/principal context tests |
| `tests/Feature/Modules/Request/RequestHttpFlowCompletionTest.php` | API `POST /api/requests/personal` create; rejects create without linked employee |
| `tests/Feature/Modules/Request/PersonalRequestTest.php` | `CreatePersonalRequestAction` domain behavior |
| `tests/Feature/Modules/Request/RequestLifecycleTest.php` | Create + lifecycle via application action |

No test file was found whose name or describe block explicitly targets **create entrypoint discoverability** or navigation-to-create from list/nav surfaces.

Tests confirm create page reachability by direct URL and assert that list UI does not show `ثبت درخواست جدید`.

---

## Observed Gap Statement

**Implemented (repository-confirmed):**

- Web route `requests.create` (`GET /requests/create`)
- Livewire create page `RequestCreatePage` with form and `save()` mutation path
- Blade view `request-create-page.blade.php`
- Backend create action `CreatePersonalRequestAction`
- API create endpoint `POST /api/requests/personal` (`requests.flow.create-personal`)
- Authenticated access path and principal-to-employee resolution on create execution

**Not visibly exposed in inspected UI surfaces:**

- No navigation link, button, or menu item to `requests.create` in request list, request show, app layout nav, or list empty state
- List empty state does not supply an action slot to `x-ui.empty-state`
- Home redirect targets request list, not create

**Gap characterization (repository evidence only):**

- Create capability and a dedicated create page exist and are directly reachable by URL.
- Inspected navigation and list/show/header surfaces do not expose a path to the create route.
- Based on inspected code and tests alone, the observable difference is between **reachable create route/page** and **absence of in-app navigation affordances** to that route. Whether that constitutes a discoverability-only gap beyond this inspection is not determined here.

---

## Out of Scope / Not Determined

- Whether users can discover `/requests/create` without documentation or direct URL (not observable from code alone).
- Guest/unauthenticated behavior for `GET /requests/create` (not found in inspected tests).
- Whether any undocumented or dynamic UI outside inspected Blade files exposes create (not found in inspected evidence).
- Product priority, implementation need, or governance outcome for P3.
- Whether layout navigation or list empty state should include a create affordance.
- Authorization policy completeness for `CreatePersonalRequestAction` beyond `PendingMutationAuthorizationRegistry` listing.
- Mission, family, or lottery request creation UI entrypoints (only personal create web page found).
- Mobile or external client discoverability paths.

---

## Evidence References

- `routes/web.php`
- `routes/api.php`
- `app/Modules/Request/Presentation/Routes/web.php`
- `app/Modules/Request/Presentation/Routes/requests.php`
- `app/Modules/Request/Presentation/Livewire/RequestCreatePage.php`
- `app/Modules/Request/Presentation/Livewire/RequestListPage.php`
- `app/Modules/Request/Presentation/Livewire/RequestShowPage.php`
- `app/Modules/Request/Presentation/Http/Controllers/RequestFlowController.php`
- `app/Modules/Request/Application/Services/CreatePersonalRequestAction.php`
- `app/Modules/Request/Application/Services/RequestPrincipalEmployeeResolver.php`
- `app/Application/Mutation/Registry/PendingMutationAuthorizationRegistry.php`
- `resources/views/livewire/request/request-create-page.blade.php`
- `resources/views/livewire/request/request-list-page.blade.php`
- `resources/views/livewire/request/request-show-page.blade.php`
- `resources/views/components/layouts/app.blade.php`
- `resources/views/components/ui/empty-state.blade.php`
- `resources/views/welcome.blade.php`
- `docs/ui/contracts/requests/request-list.feature-contract.yaml`
- `docs/ui/contracts/requests/request-list.implementation-lock.yaml`
- `docs/ui/analysis/feature-status-repository-inspection.md`
- `tests/Feature/Modules/Request/RequestUiFlowTest.php`
- `tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php`
- `tests/Feature/Mutation/LivewireSessionMutationPrincipalTest.php`
- `tests/Feature/Modules/Request/RequestHttpFlowCompletionTest.php`
- `tests/Feature/Modules/Request/PersonalRequestTest.php`
