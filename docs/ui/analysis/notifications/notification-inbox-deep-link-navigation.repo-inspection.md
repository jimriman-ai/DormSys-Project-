# Repository Inspection: Notification Inbox Deep Link Navigation

## Feature

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-deep-link-navigation` |
| **Feature title (working)** | P6 — Notification Inbox Deep Link Navigation |
| **Domain area** | notifications |
| **Inspection date** | 2026-07-09 |

## Inspection objective

Establish the current repository truth for **deep-link navigation from the notification inbox** — where the inbox UI lives, how rows are rendered, what projection fields exist for deep links, what backend and test evidence supports them, and what remains absent or ambiguous — without proposing implementation, UI design, contracts, or locks.

---

## Current repository evidence

### UI entrypoint(s)

| Item | Evidence |
|---|---|
| Web route registration | `routes/web.php` mounts `NotificationPresentationServiceProvider::notificationWebRoutePath()` under prefix `notifications` inside middleware group `auth:api`, `request.mutation.principal`, `audit.principal` |
| Module route file | `app/Modules/Notification/Presentation/Routes/web.php` — `Route::get('/', NotificationInboxPage::class)->name('notifications.index')` |
| Resolved URL | `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` asserts `route('notifications.index')` equals `url('/notifications')` |
| Livewire page | `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` — full-page component with `#[Layout('components.layouts.app')]` |
| Blade view | `resources/views/livewire/notification/notification-inbox-page.blade.php` |
| Layout navigation to inbox | **Not present** — `resources/views/components/layouts/app.blade.php` nav links only to `requests.index` (out of scope for this inspection; recorded for entrypoint context only) |
| Notification detail route | **Not found** — no `notifications.show` or similar detail route under `app/Modules/Notification/Presentation/Routes/` |

**Provider registration note:** `NotificationPresentationServiceProvider` is **not** listed in `bootstrap/providers.php`. Routes are loaded via static path reference from `routes/web.php`. `NotificationServiceProvider` (Infrastructure) is registered in `bootstrap/providers.php`.

### Notification rendering path

**Load path (confirmed):**

1. `notification-inbox-page.blade.php` — `wire:init="refreshList"` on root element
2. `NotificationInboxPage::refreshList()` — resolves employee via `NotificationPrincipalEmployeeResolver::requireEmployeeId()`, calls `NotificationInboxReadContract::listForRecipient($employeeId, null, 50)`
3. Each `NotificationProjectionDto` is mapped through private static `NotificationInboxPage::mapProjectionRow()`
4. Result stored in public `array $notifications` with `uiState` set to `loading` / `empty` / `ready` / `error`

**Row rendering (confirmed):** `notification-inbox-page.blade.php` ready state renders an HTML `<table>` with `@foreach ($notifications as $notification)` and `<tr wire:key="notification-{{ $notification['id'] }}">`.

**Table columns rendered (confirmed):**

| Persian header | View-model key |
|---|---|
| عنوان | `title` |
| پیام | `message` |
| نوع | `notification_type` |
| اولویت | `priority` |
| تاریخ ایجاد | `created_at` |
| وضعیت | derived from `is_read` (`خوانده شده` / `خوانده نشده`) |
| عملیات | conditional mark-read button (P5) |

**Fields mapped in `mapProjectionRow()` but not rendered in Blade (confirmed):** `read_at` is mapped in `NotificationInboxPage.php` but has no column in the Blade table.

### DTO / projection / data shape evidence

**`NotificationProjectionDto`** (`app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php`):

| Field | Type |
|---|---|
| `id` | `string` |
| `notificationType` | `string` |
| `title` | `string` |
| `message` | `string` |
| `entityType` | `?string` |
| `entityId` | `?string` |
| `deepLinkRoute` | `?string` |
| `isRead` | `bool` |
| `readAt` | `?DateTimeImmutable` |
| `createdAt` | `DateTimeImmutable` |
| `priority` | `string` |

**Read contract population (confirmed):** `NotificationInboxReadService::toProjection()` maps domain `Notification` to `NotificationProjectionDto`, including:

- `entityType: $notification->entityReference?->entityType`
- `entityId: $notification->entityReference?->entityId`
- `deepLinkRoute: $notification->deepLinkRoute`

Both `listForRecipient()` and `findByIdForRecipient()` on `NotificationInboxReadContract` return projections built through `toProjection()`.

**Persistence (confirmed):** `database/migrations/modules/notification/2026_07_02_000001_create_notification_logs_table.php` defines nullable columns `entity_type`, `entity_id`, `deep_link_route`. `NotificationRepository` reads/writes these fields. Domain `Notification` model holds `?EntityReference $entityReference` and `?string $deepLinkRoute`.

**UI row shape after mapping (confirmed):** `NotificationInboxPage::mapProjectionRow()` returns only:

`id`, `notification_type`, `title`, `message`, `is_read`, `read_at`, `created_at`, `priority`

**`entityType`, `entityId`, and `deepLinkRoute` are omitted** from the UI row array despite being present on `NotificationProjectionDto` at the read-contract boundary.

### Route / navigation evidence

**`deepLinkRoute` definition and exposure:**

| Layer | Present? | Location |
|---|---|---|
| Intent DTO | Yes | `NotificationIntentDto::$deepLinkRoute` — documented as optional "Presentation route token (not validated by Notification)" in `specs/009-notification-delivery/contracts/notification-intent-dto.md` |
| Domain model | Yes | `Notification::$deepLinkRoute` |
| Persistence | Yes | `notification_logs.deep_link_route` |
| Read projection | Yes | `NotificationProjectionDto::$deepLinkRoute` via `NotificationInboxReadService::toProjection()` |
| Inbox UI row | **No** | Omitted in `mapProjectionRow()`; Blade has no `route()` or `href` for notification rows |

**`entityId` definition and exposure:** Same path as `deepLinkRoute` — present on intent, domain (`EntityReference`), persistence (`entity_id`), and `NotificationProjectionDto`, **absent from inbox UI row mapping and Blade**.

**`entityType` definition and exposure:** Same as `entityId` — present through read path, **absent from inbox UI**.

**Named routes observed in notification deep-link tests (confirmed in test fixtures, not validated by Notification module):**

| `deepLinkRoute` value | Route definition file | HTTP surface | Route parameter name |
|---|---|---|---|
| `requests.show` | `app/Modules/Request/Presentation/Routes/web.php` | Web (`routes/web.php` → `requests` prefix) | `requestId` |
| `allocations.show` | `app/Modules/Allocation/Presentation/Routes/allocations.php` | API only (`routes/api.php` → `allocations` prefix) | `allocationId` |
| `check-in.show` | `app/Modules/CheckIn/Presentation/Routes/check_in.php` | API only (`routes/api.php` → `check-in` prefix) | `allocationId` |

**Existing web navigation affordance pattern (confirmed, Request module — not Notification):** `resources/views/livewire/request/request-list-page.blade.php` uses:

```blade
href="{{ route('requests.show', ['requestId' => $request['id']]) }}"
```

**Notification-specific navigation helpers:** **Not found.** No presenter, URL builder, transformer, or mapper under `app/Modules/Notification/` produces navigation URLs from `deepLinkRoute` / `entityId`.

**Governance exclusion (confirmed):** P2 contract `notification-inbox-read-only-list.feature-contract.yaml` lists deep-link consumption in `scope.excluded` and `view_model.not_consumed_in_v1` (`entityType`, `entityId`, `deepLinkRoute`). P5 contract and lock (`notification-mark-read-mutation.feature-contract.yaml`, `notification-mark-read-mutation.implementation-lock.yaml`) exclude deep-link navigation, detail view, and row-click navigation.

### Test evidence

**Backend deep-link persistence and projection (confirmed):**

| Test file | What it asserts |
|---|---|
| `tests/Feature/Modules/Notification/NotificationDeepLinkTest.php` | Delivery persists `entity_type`, `entity_id`, `deep_link_route`; `findByIdForRecipient` returns `entityType`, `entityId`, `deepLinkRoute` for `requests.show` and `allocations.show`; notifications without link data return nulls; mark-read does not mutate entity/deep-link fields |
| `tests/Feature/Modules/Notification/NotificationCheckInReminderTest.php` | Delivery with `deepLinkRoute: 'check-in.show'` returned on projection |

**Inbox UI flow tests (confirmed — no deep-link UI coverage):**

| Test file | Relevant behavior |
|---|---|
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Route registration, auth middleware, inbox render, empty/ready/error states, 50-item cap, mark-read mutation (P5); helper `deliverNotificationUiItem()` delivers intents **without** `entityType`, `entityId`, or `deepLinkRoute`; mock projections in cap test set `deepLinkRoute: null` |
| Architecture guard in same file | Asserts `NotificationInboxPage.php` does **not** contain `DB::transaction`, `DB::table`, `NotificationRepositoryContract`, `countUnread`; **permits** `MarkNotificationReadContract` (P5). **No assertion** forbidding or requiring deep-link fields |

**UI test asserting absence of deep-link affordance:** **Not found** in inspected notification UI tests.

**Spec09 planning contract (confirmed):** `specs/009-notification-delivery/contracts/notification-inbox-read-contract.md` documents `entityType`, `entityId`, `deepLinkRoute` on `NotificationProjectionDto`. Tasks T021–T022 marked complete for persistence and `NotificationDeepLinkTest.php`.

### Likely affected files

If this feature proceeds through governance, repository evidence points to these **presentation-layer** surfaces as the ones currently implementing or testing inbox row rendering (inspection inference only — not an implementation authorization):

| File | Current relevance |
|---|---|
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | `mapProjectionRow()` omits deep-link fields; sole inbox Livewire component |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Row table; no navigation links; has P5 mark-read affordance in **عملیات** column |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Inbox UI regression and architecture guard tests |

**Not evidenced as required for inbox deep-link navigation at repository layer:**

- Backend contracts, DTOs, domain, repository, migrations (fields already exist)
- `routes/web.php` notification route registration (unless new routes added — none evidenced today)
- `resources/views/components/layouts/app.blade.php` (layout nav is a separate deferred concern)

---

## Confirmed facts

1. **Notification inbox UI exists** at `GET /notifications` (`notifications.index`) via `NotificationInboxPage` and `notification-inbox-page.blade.php`.

2. **Rows are rendered as a read-only table** with text columns plus a conditional mark-read button in **عملیات** for unread rows (P5). No `<a href>`, `route()`, row `wire:click` navigation, or deep-link affordance exists on inbox rows.

3. **`NotificationProjectionDto` includes `entityType`, `entityId`, and `deepLinkRoute`** and `NotificationInboxReadService` populates them for both list and single-item reads.

4. **`NotificationInboxPage::mapProjectionRow()` does not pass `entityType`, `entityId`, or `deepLinkRoute` to the Blade view** — the UI layer currently discards these fields after read.

5. **Backend persistence and read-path support for deep links is implemented and tested** (`NotificationDeepLinkTest.php`, `NotificationCheckInReminderTest.php`). Notifications may be delivered with or without link data; null fields are valid.

6. **`deepLinkRoute` is stored as a Laravel route name token** (e.g. `requests.show`, `allocations.show`, `check-in.show`). The Notification module does **not** validate route names or resolve URLs.

7. **No Notification-module presenter, mapper, or URL helper** for deep-link resolution was found.

8. **An existing web navigation pattern** for list-to-detail links exists on the Request list (`route('requests.show', ['requestId' => $id])`) but is not used on the notification inbox.

9. **P2 and P5 governance artifacts explicitly excluded deep-link consumption** from their approved scopes. P5 lock lists `deep_link_navigation` as out of scope / forbidden surface for P5.

10. **Inbox UI tests do not exercise deep-link rendering**; default test fixtures deliver notifications without deep-link fields.

11. **The feature is not already implemented** in the inbox presentation layer — deep-link fields are available at the read contract but not consumed in UI.

---

## Open questions / ambiguities

| # | Question | Repository basis |
|---|---|---|
| OQ-01 | How should UI bind `entityId` to the correct route parameter name for a given `deepLinkRoute`? | `requests.show` uses `requestId`; `allocations.show` and `check-in.show` use `allocationId`. `NotificationProjectionDto` provides `deepLinkRoute` + `entityId` but **no route-parameter key field**. No mapping contract found in Notification module. |
| OQ-02 | Which `deepLinkRoute` values are in scope for **web** inbox navigation? | `requests.show` is a web Livewire route. `allocations.show` and `check-in.show` are registered under `routes/api.php` only — **no web route registration found** for those names. Whether inbox deep links may target API routes is **not confirmed** in repository evidence. |
| OQ-03 | What affordance form is intended — linked title, dedicated link column, row click, or action in **عملیات** alongside mark-read? | Blade currently uses plain text cells and a mark-read button only. No precedent on inbox for navigation affordance placement. |
| OQ-04 | How should rows with partial or missing link data behave? | Tests confirm notifications can have `deepLinkRoute` / `entityId` / `entityType` all null. Frequency of partial data (e.g. `entityId` without `deepLinkRoute`) in production delivery paths is **not evidenced** in repository inspection. |
| OQ-05 | Should `entityType` be consumed by UI for any purpose? | Field exists on projection; P2 contract listed it as `not_consumed_in_v1`. Using `entityType` to infer routes or parameter names would intersect with UI Anti-Leak concerns — **no backend capability flag** (e.g. `can_navigate`) found. |
| OQ-06 | What is the successor relationship to P2/P5 frozen exclusions? | P2 and P5 explicitly forbade deep-link UI. A new feature would need successor governance (as P5 did for mark-read) — **no P6 governance artifacts exist yet**. |
| OQ-07 | Is a pre-built URL or capability payload required from backend instead of UI `route()` binding? | Not present today. `deepLinkRoute` is a route name token only per `notification-intent-dto.md`. |

---

## Risks / boundary notes

1. **Route-parameter binding ambiguity (OQ-01):** Repository provides route name + entity UUID but not parameter key. Any UI that maps `entityType` → parameter name would reconstruct navigation semantics locally.

2. **Web vs API target routes (OQ-02):** Deep-link test fixtures reference API-only named routes. Inbox is a web Livewire surface. Misaligned targets may produce non-navigable or wrong-surface links without governance clarification.

3. **P2/P5 scope supersession:** Implementing deep links without successor contract/lock would conflict with frozen P2/P5 exclusion language and P5 lock `forbidden_surfaces` entries for `deep_link_navigation`.

4. **Coexistence with P5 mark-read:** **عملیات** column already hosts mark-read for unread rows. Deep-link affordance placement must not assume unlimited column space; repository shows both would share the same row surface.

5. **Thin UI / Anti-Leak:** Inbox page must remain a read-contract consumer. No evidence supports adding repository access, transactions, or route validation logic in `NotificationInboxPage` — architecture guard already blocks persistence smells.

6. **Test gap:** No UI regression test today asserts absence or presence of deep links. Adding behavior will require new test coverage; existing `deliverNotificationUiItem()` helper omits deep-link intent fields.

7. **Out of scope for this feature (per inspection mandate):** Layout nav to inbox, unread badge, mark-all-as-read, pagination, notification detail page, backend contract changes — all excluded from this inspection and not evidenced as required for deep-link field availability.

---

## Recommended next governance step

**Feature analysis required** — repository evidence is sufficient to describe current system truth (DTO fields exist, UI omits them, tests cover backend not presentation, route-parameter and web-target ambiguities remain). Analysis should classify gap type (`UI_BEHAVIOR_GAP` vs mixed), resolve OQ-01–OQ-07 within governance, and determine successor relationship to P2/P5 before any contract drafting.

---

## Decision status

**READY_FOR_FEATURE_ANALYSIS**

Repository evidence confirms: (a) inbox UI entrypoint and row rendering path, (b) deep-link fields on read projection, (c) intentional omission at UI mapping layer, (d) backend/test support for persistence and projection, (e) explicit prior governance exclusions. Ambiguities are scope and binding questions for analysis — not missing repo facts that block inspection.

---

Recommended next step: feature analysis
