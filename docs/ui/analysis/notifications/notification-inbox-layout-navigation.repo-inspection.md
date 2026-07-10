# Repository Inspection: Notification Inbox Layout Navigation

## Feature

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-layout-navigation` |
| **Feature title (working)** | P7 — Notification Inbox Layout Navigation |
| **Domain area** | notifications |
| **Inspection date** | 2026-07-10 |
| **Predecessor context** | P6 — Notification Inbox Deep-Link Navigation is **IMPLEMENTED_VERIFIED** (`docs/ui/closeout/notifications/notification-inbox-deep-link-navigation.closeout.md`). This inspection does not revisit P6 deep-link, mark-read, or read-only list behavior. |

## Inspection objective

Establish repository truth for **notification inbox discoverability via shared layout/navigation surfaces** — whether `/notifications` has a user-visible entry point beyond direct URL access, which files own navigation, what authorization/visibility gates exist, and what existing patterns constrain a future presentation change — without proposing implementation, contracts, or scope expansion.

---

## Current state summary

| Question | Repository answer |
|---|---|
| Does `/notifications` exist as a route? | **Yes** — `GET /notifications`, named `notifications.index`, handled by `NotificationInboxPage`. |
| Is there a visible UI link/button/menu to the inbox? | **No** — no `route('notifications.index')`, `/notifications`, or notification nav label found in inspected Blade views outside the inbox page itself. |
| Is the inbox route-only / discoverability-poor? | **Yes (evidence-based)** — direct URL works for authenticated users; no inspected in-app navigation affordance leads to the inbox. |
| Which surface owns discoverability today? | **None in layout/nav** — inbox page owns its own content only; shared header nav exposes requests only. |
| Role/permission nav gates? | **Not found** — no Notification Policy, Gate, or role check in layout or Notification presentation code. Route access uses the shared authenticated middleware stack. |
| Presentation-only or structural? | **Likely presentation-only** for adding a nav link; route, page, and read path already exist. Structural constraint: shared cross-module layout file. |

---

## Relevant files / routes / components inspected

### Routes

| Item | Evidence |
|---|---|
| Root web group | `routes/web.php` — authenticated group middleware: `auth:api`, `request.mutation.principal`, `audit.principal` |
| Notification prefix | `Route::prefix('notifications')->group(NotificationPresentationServiceProvider::notificationWebRoutePath())` |
| Module route file | `app/Modules/Notification/Presentation/Routes/web.php` — `Route::get('/', NotificationInboxPage::class)->name('notifications.index')` |
| Resolved URL | `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` — `route('notifications.index')` equals `url('/notifications')` |
| Home redirect | `routes/web.php` — `Route::redirect('/', '/requests')->name('home')` (not to notifications) |

### Inbox page ownership

| Symbol | Path | Role |
|---|---|---|
| `NotificationInboxPage` | `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Full-page Livewire inbox; `#[Layout('components.layouts.app')]` |
| Inbox Blade | `resources/views/livewire/notification/notification-inbox-page.blade.php` | Page header `اعلان‌های من`; refresh button; table with P5 mark-read and P6 **مشاهده** deep-link affordances |
| Route provider helper | `app/Modules/Notification/Presentation/Providers/NotificationPresentationServiceProvider.php` | Static path to module web routes only |

### Shared layout / navigation surfaces

| Surface | Path | Observed navigation |
|---|---|---|
| **Primary authenticated layout (in use)** | `resources/views/components/layouts/app.blade.php` | App title → `requests.index`; nav link **درخواست‌ها** → `requests.index` with `request()->routeIs('requests.*')` active styling; logout form |
| Bare layout (no nav) | `resources/views/layouts/app.blade.php` | Slot only; no header/nav |
| UI page header component | `resources/views/components/ui/page-header.blade.php` | Title/actions slot; no global nav |

**Livewire pages using `components.layouts.app` (confirmed):**

- `RequestListPage`, `RequestCreatePage`, `RequestShowPage`
- `NotificationInboxPage`

No sidebar, drawer, footer nav, or secondary menu component was found under `resources/views/components/`.

### Authorization / principal resolution

| Symbol | Path | Relevance to discoverability |
|---|---|---|
| `NotificationPrincipalEmployeeResolver` | `app/Modules/Notification/Application/Services/NotificationPrincipalEmployeeResolver.php` | Requires authenticated principal with linked employee when inbox **loads**; throws `UnauthorizedMutationException` otherwise |
| Notification module policies/gates | searched under `app/Modules/Notification/**` | **No matches** for `Policy`, `Gate::`, `hasRole`, or `can(` |
| P2 lock note | `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` | Explicitly excluded "Notification-specific Gate or new permission model" from P2 scope |

Nav visibility is not separately gated in repository code; inbox **page execution** requires linked employee via resolver on `refreshList()`.

### Analogous discoverability patterns

| Pattern | Location | Relevance |
|---|---|---|
| **Layout header nav (requests)** | `resources/views/components/layouts/app.blade.php` | Only confirmed global nav pattern; single module link with active-route class |
| **Page-header action link (P3 create)** | `resources/views/livewire/request/request-list-page.blade.php` | **ثبت درخواست جدید** → `route('requests.create')` in header actions and empty-state action slot — page-level, **not** layout nav |
| **List row detail link** | `resources/views/livewire/request/request-list-page.blade.php` | Row links to `requests.show` |
| **Show page back link** | `resources/views/livewire/request/request-show-page.blade.php` | **بازگشت به فهرست** → `requests.index` only; no notification link |

P3 discoverability was delivered on the **request list page**, not in `app.blade.php` layout nav. Layout nav still exposes only requests.

### Governance / deferral evidence (not implementation authorization)

| Artifact | Layout-nav posture |
|---|---|
| P2 lock | `layout navigation link (deferred; not authorized by this lock)`; forbidden without separate authorization |
| P2 contract | `No existing layout navigation entry for notifications`; optional layout link noted as review decision |
| P5 contract/lock | `layout navigation link to inbox` out of scope |
| P6 contract/lock/closeout | `Layout navigation link to inbox` explicitly out of scope; P6 closed as inbox-row deep-link only |
| P6 closeout deferred items | mark-all, badge, **layout nav**, pagination require separate governance cycle |

No P7 contract, lock, decision, analysis, or closeout artifact exists under `docs/ui/` for `notification-inbox-layout-navigation`.

### Test evidence

| Test file | Layout-nav relevance |
|---|---|
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Confirms route registration, middleware stack, guest redirect, authenticated inbox render (`اعلان‌های من`), inbox states, P5 mark-read, P6 deep-link; **no assertion** for layout nav link presence or absence |
| `tests/Feature/Modules/Request/RequestUiFlowTest.php` | Asserts **ثبت درخواست جدید** on request list (P3 page-level discoverability); **no layout nav tests** for requests or notifications |
| Other notification tests | Backend/inbox behavior only; no layout navigation coverage |

No test was found asserting `assertDontSee` for a notification nav label on authenticated pages.

---

## Evidence-based findings (by inspection question)

### 1. Does `/notifications` currently exist as a route, and what owns it?

**Confirmed:**

- Route name: `notifications.index`
- Path: `GET /notifications`
- Handler: `App\Modules\Notification\Presentation\Livewire\NotificationInboxPage`
- Layout: `components.layouts.app`
- View: `livewire.notification.notification-inbox-page`
- Registration: `routes/web.php` → Notification module `Presentation/Routes/web.php`

### 2. Does the current UI expose a link/button/menu entry to Notification Inbox?

**Confirmed: No** in inspected surfaces.

Search of `resources/views/**/*.blade.php` for `notifications.index`, `route('notifications`, `/notifications`, and notification nav labels found matches **only** in `notification-inbox-page.blade.php` (page title/description copy, not navigation).

### 3. If yes, where exactly?

**N/A** — no entry point found outside the inbox page content itself.

### 4. If no, is the inbox effectively route-only / discoverability-poor?

**Confirmed: Yes**, relative to inspected in-app navigation.

- Direct URL `/notifications` is reachable when authenticated (tested).
- Home redirects to `/requests`, not notifications.
- Shared header nav does not mention notifications.
- Request list, show, and create pages do not link to the inbox.

### 5. Which layout/navigation surfaces are shared and realistic ownership candidates?

**Confirmed candidates (repository inventory):**

| Candidate | Shared? | Nav today | Realistic for inbox link? |
|---|---|---|---|
| `resources/views/components/layouts/app.blade.php` | **Yes** — all authenticated Livewire full pages | Header `<nav>` with module links | **Primary evidence-backed owner** — only global nav surface in use |
| `resources/views/livewire/request/request-list-page.blade.php` | Request module only | Page-header actions | Page-level discoverability precedent (P3); not layout navigation |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Notification page only | Refresh action only | Destination page, not an entrypoint to itself |
| `resources/views/layouts/app.blade.php` | Unused by inspected Livewire pages | None | Not evidenced as active nav owner |

### 6. Are there authorization, role, or conditional-rendering constraints already in place?

**Confirmed:**

- **Route-level:** Same authenticated middleware stack as requests (`auth:api`, `request.mutation.principal`, `audit.principal`).
- **Guest behavior:** `GET /notifications` redirects to `/login` (tested).
- **No Notification-specific nav visibility gate** in layout Blade.
- **Page-level principal constraint:** `NotificationPrincipalEmployeeResolver::requireEmployeeId()` on inbox load; unlinked employee principal fails at page execution, not at nav render (nav does not check this today for requests either).

**Not found:**

- Spatie role/permission checks in layout or Notification presentation
- Notification Policy class
- Conditional `@can` / `@role` around nav items in `app.blade.php`

### 7. Are there analogous patterns for adding discoverable list/inbox entry points?

**Confirmed patterns:**

1. **Global layout nav item** — `app.blade.php` link to `requests.index` with `request()->routeIs('requests.*')` active-state ternary. Closest structural analogue for P7.
2. **Page-header primary action** — P3 added create link on request list header and empty state using `route('requests.create')` and `wire:navigate`. Analogous for **page-level** discoverability, not header nav.
3. **Reuse existing named route** — P3 and P6 both reused existing routes (`requests.create`, `requests.show`) without new route registration. Inbox route `notifications.index` already exists for reuse.

### 8. Is this feature likely presentation-only, or are there hidden structural constraints?

**Likely presentation-only (inferred from evidence, not product decision):**

- Route, Livewire page, read contract, and inbox UI already operational through P2/P5/P6.
- No backend/DTO/migration gap evidenced for **linking** to the existing inbox.

**Structural constraints (confirmed):**

- **Shared cross-module layout** — `components.layouts.app` serves Request and Notification modules; changes affect all pages using this layout.
- **Successor governance required** — P2 deferred layout nav; P5/P6 explicitly excluded it. P7 requires a new governance cycle; prior locks do not authorize layout nav changes.
- **Separation from badge/count** — `countUnread` exists on read contract/repository but is **not consumed** in layout; P2/P5/P6 exclude badge surfaces. Layout nav must not be conflated with unread-badge work (separate deferred candidate).
- **Architecture guard on inbox page** — `NotificationInboxUiFlowTest` architecture guard blocks persistence smells on `NotificationInboxPage`; it does **not** block or require layout nav changes (layout is outside that guard scope).

---

## Confirmed facts

1. **`notifications.index` (`GET /notifications`) is registered and functional** via `NotificationInboxPage` with shared `components.layouts.app` layout.

2. **No inspected Blade view exposes a navigation affordance to the notification inbox** except the inbox page's own content (title, refresh).

3. **`resources/views/components/layouts/app.blade.php` is the sole evidenced global navigation surface** for authenticated Livewire pages; it contains one module nav link (**درخواست‌ها** → `requests.index`) and no notification link.

4. **Home (`/`) redirects to `/requests`**, not the notification inbox.

5. **Notification inbox middleware matches the authenticated web stack** used for requests (`auth:api`, `request.mutation.principal`, `audit.principal`).

6. **No Notification-specific Policy, Gate, or role-based nav rendering** was found in the Notification module or shared layout.

7. **Inbox page load requires a linked employee** via `NotificationPrincipalEmployeeResolver`; this is a page-execution constraint, not a layout nav visibility rule in current code.

8. **P2 explicitly deferred layout navigation**; P5 and P6 contracts/locks/closeout **explicitly excluded** layout nav to inbox. P6 is closed; layout nav remains a separately deferred presentation gap.

9. **No automated test currently asserts layout navigation to notifications** (presence or absence).

10. **P3 discoverability precedent is page-level** (request list header/empty state), not layout-header nav. Layout nav for requests was already present before P3; notification layout nav was never added.

11. **P6 deep-link navigation is implemented on inbox rows only** (**مشاهده** in **عملیات**); it does not add inbox discoverability from other surfaces.

---

## Inferred constraints

| Constraint | Basis |
|---|---|
| Primary ownership surface is likely `components/layouts/app.blade.php` | Only global nav in use by all authenticated full pages |
| Active-route styling should follow existing `request()->routeIs('requests.*')` pattern | Evidenced in layout nav; likely `notifications.*` for parity (**inferred**, not implemented) |
| Reuse `route('notifications.index')` | Route already registered; P3/P6 precedents reuse existing named routes |
| Cross-module blast radius | Layout change affects Request and Notification pages alike |
| Successor governance supersedes P2 deferral and P5/P6 exclusions | Multiple governance artifacts defer/exclude layout nav without P7 authorization |
| Keep unread badge / `countUnread` out of P7 scope | Explicitly deferred in P2/P5/P6; backend count exists but layout does not consume it |
| Do not modify inbox row behavior, mark-read, or deep-link | Out of scope per inspection mandate; owned by closed P5/P6 |

---

## Unknowns (for later clarification — not inspection blockers)

| # | Unknown | Why it remains open |
|---|---|---|
| OQ-01 | Exact Persian nav label (e.g. **اعلان‌ها** vs **اعلان‌های من**) | No notification nav label exists in layout to copy |
| OQ-02 | Should nav link render for principals **without** a linked employee? | Resolver fails on inbox load, but layout does not gate request nav similarly today; product rule not in code |
| OQ-03 | Contract/lock required vs `DIRECT_UI_IMPLEMENTATION_AUTHORIZED` (P3-style)? | P3 skipped contract for page-level discoverability; P7 touches **shared cross-module layout** — governance posture not decided |
| OQ-04 | Page-level entrypoints (e.g. request list header) in scope or layout-only? | Feature title says "layout navigation"; P3 placed discoverability on page header, not layout — scope boundary not defined in repo |
| OQ-05 | Nav ordering/placement relative to **درخواست‌ها** | Single existing nav item; no second-module precedent in layout |
| OQ-06 | Test strategy for layout nav (which pages, which roles) | No existing layout nav tests for any module |

---

## Risks / boundary notes

1. **Shared layout blast radius** — `app.blade.php` is cross-module; a nav link appears on request list, create, show, and notification inbox pages simultaneously.

2. **Governance supersession** — Implementing layout nav without P7 successor artifacts would conflict with P2 forbidden change ("layout navigation is changed without separate authorization") and P5/P6 explicit exclusions.

3. **Feature conflation with unread badge** — `countUnread` backend support exists; P2/P5/P6 deferred badge on layout. P7 inspection scope is **link discoverability only**, not count display.

4. **Principal-without-employee UX** — User could follow nav link and hit inbox error state if resolver throws; no nav-level guard evidenced. Whether that is acceptable is a product/analysis question.

5. **Out of scope (per inspection mandate)** — Deep-link row navigation (P6), mark-read (P5), read-only list behavior (P2), pagination/filter/sort, mark-all-as-read, notification detail page, new routes, backend changes, badge/countUnread consumption.

6. **Test gap** — No regression test today for layout nav; adding behavior will need new coverage (likely HTTP GET on authenticated pages using shared layout).

7. **P3 precedent is partial** — P3 authorized page-level discoverability without layout nav change; P7 targets the layout surface P3 did not modify.

---

## Inferred ownership boundaries

| Layer | Owner for P7 concern | Inspection note |
|---|---|---|
| **Presentation — shared layout** | `resources/views/components/layouts/app.blade.php` | Primary evidenced surface for global inbox discoverability |
| **Presentation — inbox page** | `NotificationInboxPage` + inbox Blade | Destination only; no self-entrypoint nav evidenced |
| **Application** | No change evidenced as required for nav link | Resolver/read contracts already support inbox |
| **Infrastructure / Domain** | No change evidenced as required | Route and page exist |
| **Authorization** | Shared web middleware only | No module-specific nav gate found |

---

## Gap characterization (repository evidence only)

**Implemented and reachable:**

- Web route `notifications.index` (`GET /notifications`)
- Livewire inbox page with P2 list, P5 mark-read, P6 row deep-link
- Authenticated direct URL access (tested)

**Not visibly exposed in inspected navigation/layout surfaces:**

- No header/nav/menu link to `notifications.index`
- No cross-link from request list/show/create pages to inbox
- Home redirect targets requests, not notifications

**Observable gap:** **reachable inbox route/page** vs **absence of in-app navigation affordances** on shared layout (and other inspected surfaces) to that route. This aligns with the deferred item recorded since P2 and excluded through P6.

---

## Recommended next stage classification

**READY_FOR_FEATURE_ANALYSIS**

Repository evidence is sufficient to describe current system truth: the inbox route and page are operational; shared layout navigation exposes requests only; no notification discoverability affordance exists in inspected views; authorization does not gate nav visibility today; predecessor governance explicitly deferred/excluded layout nav pending a successor cycle. Open questions (label, employee-linked visibility, contract necessity, layout-only vs multi-surface scope) are analysis/governance matters, not missing repository facts that block inspection.

---

## Evidence references

- `routes/web.php`
- `app/Modules/Notification/Presentation/Routes/web.php`
- `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php`
- `app/Modules/Notification/Presentation/Providers/NotificationPresentationServiceProvider.php`
- `app/Modules/Notification/Application/Services/NotificationPrincipalEmployeeResolver.php`
- `resources/views/components/layouts/app.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/livewire/notification/notification-inbox-page.blade.php`
- `resources/views/livewire/request/request-list-page.blade.php`
- `resources/views/livewire/request/request-show-page.blade.php`
- `resources/views/components/ui/page-header.blade.php`
- `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md`
- `docs/ui/contracts/notifications/notification-inbox-deep-link-navigation.feature-contract.yaml`
- `docs/ui/closeout/notifications/notification-inbox-deep-link-navigation.closeout.md`
- `docs/ui/verification/requests/request-create-entrypoint-discoverability.implementation-verification.md`
- `docs/ui/analysis/feature-next-candidate.md`
- `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php`
- `tests/Feature/Modules/Request/RequestUiFlowTest.php`
