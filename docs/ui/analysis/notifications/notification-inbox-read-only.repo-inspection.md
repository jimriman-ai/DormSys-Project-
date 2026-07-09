# Repository Inspection: Notification Inbox (Read-Only)

## Scope

Fact-only inspection of repository evidence for **Notification Inbox (Read-Only)** across:

- Notification module backend/read foundation
- Notification UI surface (routes, Livewire, Blade, navigation)
- Principal and ownership resolution
- Authorization/access patterns
- Backend sufficiency for a read-only inbox list
- Dependencies, blockers, and inconsistencies visible in code, tests, and governance artifacts

Inspection date basis: current workspace state.

## Inspection Status

| Area | Evidence located |
|---|---|
| Notification module structure | Yes |
| Read contracts / DTOs / services | Yes |
| Repository / persistence model | Yes |
| Web UI (route, Livewire, Blade) | Yes |
| Principal resolution service | Yes |
| Middleware / access pattern | Yes |
| Backend inbox read tests | Yes |
| UI flow tests | Yes |
| Governance artifacts (contract, lock, open decisions) | Yes |
| Closeout artifact for inbox UI | No repository evidence found |
| API routes for notification inbox | No repository evidence found |
| Notification detail page / `notifications.show` route | No repository evidence found |
| Layout navigation link to notifications | No repository evidence found |

## Repository Facts

- Notification module path: `app/Modules/Notification/`
- `NotificationServiceProvider` registered in `bootstrap/providers.php`
- `NotificationPresentationServiceProvider` is referenced from `routes/web.php` via static method `notificationWebRoutePath()`; `NotificationPresentationServiceProvider` is not listed in `bootstrap/providers.php`
- Web authenticated route group in `routes/web.php` uses middleware `['auth:api', 'request.mutation.principal', 'audit.principal']`
- Notification web routes registered under prefix `notifications` from `app/Modules/Notification/Presentation/Routes/web.php`
- Persistence table: `notification_logs` (migration `database/migrations/modules/notification/2026_07_02_000001_create_notification_logs_table.php`)
- Governance artifacts present:
  - `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` (status: `draft`)
  - `docs/ui/decisions/notifications/notification-inbox-read-only-list.open-decisions-resolution.yaml` (status: `resolved`)
  - `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` (lock status: `draft`, `Coding authorized: false`)
- No files found under `docs/ui/closeouts/notifications/`

## Notification Backend Read Foundation

### Module directories and major classes

**Application**

| Path | Symbol |
|---|---|
| `app/Modules/Notification/Application/Contracts/NotificationInboxReadContract.php` | `NotificationInboxReadContract` |
| `app/Modules/Notification/Application/Contracts/MarkNotificationReadContract.php` | `MarkNotificationReadContract` |
| `app/Modules/Notification/Application/Contracts/NotificationRepositoryContract.php` | `NotificationRepositoryContract` |
| `app/Modules/Notification/Application/Contracts/NotificationDeliveryContract.php` | `NotificationDeliveryContract` |
| `app/Modules/Notification/Application/Contracts/EmployeeExistenceReadPort.php` | `EmployeeExistenceReadPort` |
| `app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php` | `NotificationProjectionDto` |
| `app/Modules/Notification/Application/DTOs/NotificationIntentDto.php` | `NotificationIntentDto` |
| `app/Modules/Notification/Application/DTOs/NotificationDeliveryResultDto.php` | `NotificationDeliveryResultDto` |
| `app/Modules/Notification/Application/Services/NotificationInboxReadService.php` | `NotificationInboxReadService` |
| `app/Modules/Notification/Application/Services/NotificationPrincipalEmployeeResolver.php` | `NotificationPrincipalEmployeeResolver` |
| `app/Modules/Notification/Application/Services/MarkNotificationReadAction.php` | `MarkNotificationReadAction` |
| `app/Modules/Notification/Application/Services/DeliverNotificationAction.php` | `DeliverNotificationAction` |
| `app/Modules/Notification/Application/Services/NotificationRetentionSettingsReader.php` | `NotificationRetentionSettingsReader` |

**Domain**

| Path | Symbol |
|---|---|
| `app/Modules/Notification/Domain/Models/Notification.php` | `Notification` |
| `app/Modules/Notification/Domain/Enums/NotificationType.php` | `NotificationType` |
| `app/Modules/Notification/Domain/Enums/DeliveryStatus.php` | `DeliveryStatus` |
| `app/Modules/Notification/Domain/Enums/DeliveryPriority.php` | `DeliveryPriority` |
| `app/Modules/Notification/Domain/ValueObjects/NotificationId.php` | `NotificationId` |
| `app/Modules/Notification/Domain/ValueObjects/EntityReference.php` | `EntityReference` |
| `app/Modules/Notification/Domain/ValueObjects/CorrelationId.php` | `CorrelationId` |

**Infrastructure**

| Path | Symbol |
|---|---|
| `app/Modules/Notification/Infrastructure/Repositories/NotificationRepository.php` | `NotificationRepository` |
| `app/Modules/Notification/Infrastructure/Persistence/Models/NotificationLogModel.php` | `NotificationLogModel` |
| `app/Modules/Notification/Infrastructure/Adapters/StubEmployeeExistenceReadAdapter.php` | `StubEmployeeExistenceReadAdapter` |
| `app/Modules/Notification/Infrastructure/Adapters/InMemoryEmployeeExistenceReadAdapter.php` | `InMemoryEmployeeExistenceReadAdapter` |
| `app/Modules/Notification/Infrastructure/Jobs/SendNotificationJob.php` | `SendNotificationJob` |
| `app/Modules/Notification/Infrastructure/Jobs/ArchiveExpiredNotificationsJob.php` | `ArchiveExpiredNotificationsJob` |
| `app/Modules/Notification/Infrastructure/Providers/NotificationServiceProvider.php` | `NotificationServiceProvider` |

**Presentation**

| Path | Symbol |
|---|---|
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | `NotificationInboxPage` |
| `app/Modules/Notification/Presentation/Routes/web.php` | web route registration |
| `app/Modules/Notification/Presentation/Providers/NotificationPresentationServiceProvider.php` | `NotificationPresentationServiceProvider` |

Placeholder `.gitkeep` files also exist under several Notification subdirectories (`Application/DTOs/.gitkeep`, `Presentation/Livewire/.gitkeep`, etc.) alongside implemented classes.

### Read contracts and method signatures

**`NotificationInboxReadContract`** (`app/Modules/Notification/Application/Contracts/NotificationInboxReadContract.php`):

```php
public function listForRecipient(string $recipientEmployeeId, ?bool $unreadOnly = null, int $limit = 50): array;
public function findByIdForRecipient(string $notificationId, string $recipientEmployeeId): ?NotificationProjectionDto;
public function countUnread(string $recipientEmployeeId): int;
```

**`NotificationRepositoryContract`** (`app/Modules/Notification/Application/Contracts/NotificationRepositoryContract.php`):

```php
public function listForRecipient(string $recipientEmployeeId, ?bool $unreadOnly = null, int $limit = 50): array;
public function findByIdForRecipient(NotificationId $notificationId, string $recipientEmployeeId): ?Notification;
public function countUnread(string $recipientEmployeeId): int;
```

**`MarkNotificationReadContract`** (`app/Modules/Notification/Application/Contracts/MarkNotificationReadContract.php`):

```php
public function markRead(string $notificationId, string $recipientEmployeeId, DateTimeImmutable $readAt): void;
```

### DTOs / projections / read models

**`NotificationProjectionDto`** (`app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php`) fields:

- `id` (string)
- `notificationType` (string)
- `title` (string)
- `message` (string)
- `entityType` (?string)
- `entityId` (?string)
- `deepLinkRoute` (?string)
- `isRead` (bool)
- `readAt` (?DateTimeImmutable)
- `createdAt` (DateTimeImmutable)
- `priority` (string)

**`NotificationInboxReadService::toProjection()`** maps domain `Notification` to `NotificationProjectionDto`, including `entityType`, `entityId`, `deepLinkRoute`, and `isRead` via `Notification::isRead()`.

### Repositories / query services / application services for read access

| Component | Role |
|---|---|
| `NotificationInboxReadService` | Implements `NotificationInboxReadContract`; delegates to `NotificationRepositoryContract` |
| `NotificationRepository` | Queries `NotificationLogModel`; recipient-scoped list/find/count |
| `NotificationServiceProvider` | Binds `NotificationInboxReadContract` → `NotificationInboxReadService`, `NotificationRepositoryContract` → `NotificationRepository` |

**`NotificationRepository::listForRecipient()`** filters:

- `recipient_employee_id = $recipientEmployeeId`
- `delivery_status = DeliveryStatus::Delivered`
- `archived_at IS NULL`
- optional `read_at IS NULL` when `$unreadOnly === true`
- `orderByDesc('created_at')`
- `limit($limit)` (default parameter `50`)

**`NotificationRepository::countUnread()`** counts rows where `recipient_employee_id` matches, `delivery_status = Delivered`, `archived_at IS NULL`, `read_at IS NULL`.

### Symbols related to notification listing

- `NotificationInboxReadContract::listForRecipient`
- `NotificationRepository::listForRecipient`
- `NotificationInboxPage::refreshList` calls `listForRecipient($employeeId, null, self::LIST_LIMIT)` where `LIST_LIMIT = 50`

### Symbols related to unread/read state

- Domain: `Notification::isRead()` returns `$this->readAt !== null`
- Domain: `Notification::markRead(DateTimeImmutable $readAt)`
- Persistence column: `notification_logs.read_at` (nullable timestamp)
- DTO field: `NotificationProjectionDto::isRead`, `NotificationProjectionDto::readAt`
- Read filter: `listForRecipient(..., unreadOnly: true)` applies `whereNull('read_at')`
- Count: `NotificationInboxReadContract::countUnread`, `NotificationRepository::countUnread`
- Mutation: `MarkNotificationReadContract::markRead`, `MarkNotificationReadAction::markRead`

### Symbols related to recipient scoping

- Domain field: `Notification::$recipientEmployeeId`
- Persistence column: `notification_logs.recipient_employee_id`
- Repository queries filter by `recipient_employee_id` in `findByIdForRecipient`, `listForRecipient`, `countUnread`
- Delivery input: `NotificationIntentDto::$recipientEmployeeId`
- Dedup key includes `recipient_employee_id` in `NotificationRepository::findByDedupKey`

### Automated tests covering notification read/list behavior

| Test file | Coverage evidenced |
|---|---|
| `tests/Feature/Modules/Notification/NotificationInboxTest.php` | `listForRecipient`, unread filter, `countUnread`, `findByIdForRecipient`, `markRead`, cross-recipient denial, archived exclusion, empty inbox |
| `tests/Feature/Modules/Notification/NotificationDeepLinkTest.php` | `deepLinkRoute`, `entityType`, `entityId` persisted and returned in `NotificationProjectionDto` via `findByIdForRecipient` |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | UI route, middleware, Livewire list rendering, principal resolution, limit 50, error states |
| `tests/Architecture/NotificationBoundaryTest.php` | Module boundary checks; binds `NotificationInboxReadContract` |

Other Notification feature tests exist (`NotificationDeliveryTest.php`, `NotificationIdempotencyTest.php`, `NotificationRetentionTest.php`, `NotificationCheckInReminderTest.php`) focused on delivery/retention paths rather than inbox list UI.

## Notification UI Surface

### Web routes related to notifications

**`routes/web.php`** (authenticated group):

```php
Route::prefix('notifications')
    ->group(NotificationPresentationServiceProvider::notificationWebRoutePath());
```

**`app/Modules/Notification/Presentation/Routes/web.php`**:

```php
Route::get('/', NotificationInboxPage::class)->name('notifications.index');
```

Effective path: `GET /notifications` (prefix + `/`).

### Route names

| Name | Path | Handler |
|---|---|---|
| `notifications.index` | `GET /notifications` | `NotificationInboxPage` (Livewire full-page) |

No other notification web route names found in `app/Modules/Notification/Presentation/Routes/web.php`.

`routes/api.php` contains no notification route registrations.

### Livewire pages/components related to notifications

| Class | Path |
|---|---|
| `NotificationInboxPage` | `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` |

No other Notification Livewire component classes found under `app/Modules/Notification/Presentation/Livewire/`.

### Blade views related to notifications

| View | Path |
|---|---|
| `livewire.notification.notification-inbox-page` | `resources/views/livewire/notification/notification-inbox-page.blade.php` |

No other notification Blade views found under `resources/views/`.

### Navigation / menu / sidebar / header exposure

**`resources/views/components/layouts/app.blade.php`** header navigation contains:

- Link to `route('requests.index')` labeled `درخواست‌ها`
- App title link to `route('requests.index')`
- Logout form

No link to `notifications.index` or notification-related label found in `resources/views/components/layouts/app.blade.php`.

No repository evidence found for notification links in other layout, sidebar, or header view files searched under `resources/views/components/`.

### Notification detail page or deep-link route

No repository evidence found for:

- `notifications.show` or similar detail route name
- Notification detail Livewire page
- Notification detail Blade view

`NotificationProjectionDto` and `notification_logs.deep_link_route` store named route strings (e.g. `requests.show`, `allocations.show` per `NotificationDeepLinkTest.php`), but the inbox Blade view contains no `route()` calls or anchor links using `deepLinkRoute`.

### UI entrypoints for notifications

| Entrypoint | Evidence |
|---|---|
| Direct URL `/notifications` | `notifications.index` route registration |
| Layout navigation | No repository evidence found |
| API entrypoint | No repository evidence found |

### `NotificationInboxPage` behavior (evidenced)

- UI states: `loading`, `empty`, `ready`, `error`
- `refreshList()` injects `NotificationInboxReadContract`, `NotificationPrincipalEmployeeResolver`
- Calls `listForRecipient($employeeId, null, 50)`
- Maps `NotificationProjectionDto` via `mapProjectionRow()` to keys: `id`, `notification_type`, `title`, `message`, `is_read`, `read_at`, `created_at`, `priority`
- `mapProjectionRow()` does not include `entityType`, `entityId`, `deepLinkRoute`
- Jalali formatting applied to `readAt` and `createdAt` in `mapProjectionRow()`
- No `MarkNotificationReadContract` usage in `NotificationInboxPage`
- No `countUnread` usage in `NotificationInboxPage`

### Inbox Blade table columns (evidenced)

From `resources/views/livewire/notification/notification-inbox-page.blade.php`:

| Column header (Persian) | View-model key |
|---|---|
| عنوان | `title` |
| پیام | `message` |
| نوع | `notification_type` |
| اولویت | `priority` |
| تاریخ ایجاد | `created_at` |
| وضعیت | derived from `is_read` (`خوانده شده` / `خوانده نشده`) |

`read_at` is mapped in `NotificationInboxPage` but not rendered in the Blade table.

Row interaction: table rows only; no links, `wire:click` actions, or navigation affordances on rows.

## Principal and Ownership Resolution

### Authenticated principal access in web flows

- Web authenticated routes use `auth:api` guard (`routes/web.php`)
- `NotificationInboxUiFlowTest` authenticates via `authenticateRequestHttpMutationUser($identity)` and `MutationPrincipalContextHolder::set($identity->id)`
- `EnforceSessionMutationPrincipalMiddleware` registered in `app/Providers/AppServiceProvider.php` as alias `request.mutation.principal` (`app/Modules/Request/Presentation/Http/Middleware/EnforceSessionMutationPrincipalMiddleware.php`)

### Employee-resolution services / contracts / context

**`NotificationPrincipalEmployeeResolver`** (`app/Modules/Notification/Application/Services/NotificationPrincipalEmployeeResolver.php`):

```php
public function requireEmployeeId(): string
```

Composition:

- `MutationPrincipalContextPort::currentPrincipalId()`
- `EmployeeRepositoryContract::findEmployeeIdByIdentityUserId($principalId)`

Throws `UnauthorizedMutationException` when principal is missing or no linked employee.

Registered as singleton in `NotificationServiceProvider`.

Parallel pattern exists in **`RequestPrincipalEmployeeResolver`** (`app/Modules/Request/Application/Services/RequestPrincipalEmployeeResolver.php`) with same `requireEmployeeId()` composition; Request resolver additionally defines `assertOwnsSummary()`.

**`MutationPrincipalContextPort`** (`app/Application/Mutation/Contracts/MutationPrincipalContextPort.php`):

```php
public function currentPrincipalId(): ?string;
```

### Notification scoping symbols

Notifications are scoped by **`recipientEmployeeId`** / **`recipient_employee_id`** throughout domain, persistence, repository, and read contract layers.

No repository evidence found for user-ID-based inbox scoping independent of employee ID in read queries.

### User-to-employee mapping evidence

`NotificationPrincipalEmployeeResolver::requireEmployeeId()` calls `EmployeeRepositoryContract::findEmployeeIdByIdentityUserId($principalId)`.

`NotificationInboxUiFlowTest` asserts resolved employee ID matches `$actor['employee']->requireId()->value`.

`NotificationInboxUiFlowTest` surfaces error message `'Authenticated principal has no linked employee.'` when principal has no employee link.

### Ambiguity or absence of mapping evidence

`EmployeeExistenceReadPort` is bound to `StubEmployeeExistenceReadAdapter` in `NotificationServiceProvider`; stub returns `Uuid::isValid($employeeId)` only. README states: `EmployeeExistenceReadPort: stub adapter until spec03 live supplier is wired`.

No repository evidence found for a Notification-specific ownership assertion method beyond recipient-scoped repository queries.

## Authorization and Access Pattern

### Middleware conventions for authenticated web pages

Authenticated web group in `routes/web.php`:

```php
Route::middleware(['auth:api', 'request.mutation.principal', 'audit.principal'])
```

### Middleware used for principal / mutation / audit context

| Middleware alias | Class |
|---|---|
| `request.mutation.principal` | `EnforceSessionMutationPrincipalMiddleware` |
| `audit.principal` | (alias referenced in routes; class not inspected in this document) |
| `auth:api` | Laravel auth guard |

`NotificationInboxUiFlowTest::notificationInboxRouteMiddleware()` gathers middleware for `notifications.index` and asserts presence of `auth:api`, `request.mutation.principal`, `audit.principal`.

### Access control enforcement for notification reads

**Route layer:** guest `GET /notifications` redirects to `/login` (`NotificationInboxUiFlowTest`).

**Application layer:** `NotificationPrincipalEmployeeResolver::requireEmployeeId()` gates employee context before read call in `NotificationInboxPage::refreshList()`.

**Repository layer:** `NotificationRepository` enforces `recipient_employee_id` on list/find/count queries.

No Notification-specific Gate or Policy classes found in Notification module paths inspected.

`MarkNotificationReadAction` throws `ValidationException` when notification not found for recipient (`NotificationInboxTest`).

### UI authorization checks on similar read-only pages

**`RequestListPage`** (`app/Modules/Request/Presentation/Livewire/RequestListPage.php`) uses `RequestPrincipalEmployeeResolver::requireEmployeeId()` in `refreshList()` without UI-side permission evaluation.

**`RequestShowPage`** uses `RequestPrincipalEmployeeResolver::assertOwnsSummary()` in `mount()`.

**`NotificationInboxPage`** uses `NotificationPrincipalEmployeeResolver::requireEmployeeId()` only; no per-row ownership check in UI (list is recipient-scoped at repository via employee ID passed to read contract).

### Thin-UI governance alignment evidence

`NotificationInboxUiFlowTest` architecture guard asserts `NotificationInboxPage.php` does not contain:

- `DB::transaction`
- `DB::table`
- `NotificationRepositoryContract`
- `MarkNotificationReadContract`
- `countUnread`

`NotificationInboxPage` consumes `NotificationInboxReadContract` and maps DTO fields for display.

## Backend Sufficiency Evidence

### Recipient-scoped notification list

**Exists.** `NotificationInboxReadContract::listForRecipient` implemented by `NotificationInboxReadService` → `NotificationRepository::listForRecipient`.

Test evidence: `NotificationInboxTest` `lists inbox notifications for a recipient`.

### Fields needed for inbox row in DTO/projection/model

**`NotificationProjectionDto`** provides: `id`, `notificationType`, `title`, `message`, `isRead`, `readAt`, `createdAt`, `priority`, plus `entityType`, `entityId`, `deepLinkRoute`.

**`NotificationInboxPage::mapProjectionRow()`** exposes subset: `id`, `notification_type`, `title`, `message`, `is_read`, `read_at`, `created_at`, `priority`.

**Blade** renders: `title`, `message`, `notification_type`, `priority`, `created_at`, read/unread label from `is_read`.

### Unread/read state representation

**Exists** in backend:

- `read_at` column nullable on `notification_logs`
- `Notification::isRead()` / `NotificationProjectionDto::isRead`
- `listForRecipient(..., unreadOnly: true)` filter
- `countUnread()` method

UI displays read/unread via `is_read` boolean mapped to Persian strings in Blade.

### Target URLs / deep links stored or exposed

**Stored:** `notification_logs.deep_link_route`, `entity_type`, `entity_id` columns; `NotificationProjectionDto::deepLinkRoute`, `entityType`, `entityId`.

**Delivery test evidence:** `NotificationDeepLinkTest` persists `deepLinkRoute: 'requests.show'` and reads it back via `findByIdForRecipient`.

**Inbox UI exposure:** `NotificationInboxPage::mapProjectionRow()` does not map `deepLinkRoute`, `entityType`, or `entityId`. Inbox Blade contains no deep-link rendering.

### List limits/caps

- `NotificationInboxReadContract::listForRecipient` default `$limit = 50`
- `NotificationRepository::listForRecipient` default `$limit = 50`
- `NotificationInboxPage::LIST_LIMIT = 50`
- `NotificationInboxUiFlowTest` asserts `listForRecipient` called with `($employeeId, null, 50)` and at most 50 items presented

No offset/cursor pagination symbols found in read contract or repository.

### Explicit backend gaps visible in code

No repository evidence found for explicit TODO/FIXME markers indicating missing inbox list backend capability.

`app/Modules/Notification/README.md` lists deferred Wave 2+ items including retention archive job and architecture boundary test; `ArchiveExpiredNotificationsJob` and `NotificationBoundaryTest` exist in codebase.

## Dependencies and Blockers

### Confirmed dependencies present in repository

| Dependency | Evidence |
|---|---|
| `NotificationInboxReadContract` + implementation | `NotificationInboxReadService`, `NotificationServiceProvider` binding |
| `NotificationProjectionDto` | DTO class; used by read service and UI |
| `NotificationRepository` / `NotificationLogModel` | Persistence and query layer |
| `NotificationPrincipalEmployeeResolver` | Class exists; bound in `NotificationServiceProvider` |
| `MutationPrincipalContextPort` | Used by resolver |
| `EmployeeRepositoryContract` | Used by resolver for principal-to-employee lookup |
| Authenticated web middleware stack | `routes/web.php`, verified in `NotificationInboxUiFlowTest` |
| Employee delivery path for test data | `NotificationDeliveryContract`, `NotificationIntentDto` in tests |

### Missing symbols (expected by naming/tests but absent for inbox UI scope)

| Expected symbol | Status |
|---|---|
| `notifications.show` route | No repository evidence found |
| Notification detail Livewire page | No repository evidence found |
| Layout nav link to inbox | No repository evidence found |
| `countUnread` usage in presentation | No repository evidence found in `NotificationInboxPage` or inbox Blade |
| `MarkNotificationReadContract` usage in presentation | No repository evidence found in `NotificationInboxPage` |
| API notification inbox routes | No repository evidence found in `routes/api.php` |
| Closeout artifact | No repository evidence found under `docs/ui/closeouts/notifications/` |

### Incomplete architectural chain (directly evidenced)

No repository evidence found for broken service-provider binding of `NotificationInboxReadContract` (test `notification inbox read depends only on notification contracts` resolves the contract).

`EmployeeExistenceReadPort` uses `StubEmployeeExistenceReadAdapter` in production binding (`NotificationServiceProvider`); README documents this as stub until spec03 supplier.

### Test coverage: exists vs does not exist

**Exists:**

- Backend inbox read: `NotificationInboxTest.php`
- Deep link persistence on delivery/read projection: `NotificationDeepLinkTest.php`
- UI inbox flow: `NotificationInboxUiFlowTest.php`
- Module boundaries: `NotificationBoundaryTest.php`

**Does not exist (searched paths):**

- UI test for layout navigation to notifications
- UI test for deep-link consumption from inbox list
- UI test for mark-read from inbox
- UI test for `countUnread` badge/display
- Closeout verification artifact

## Inconsistencies or Ambiguities

### Governance artifact vs repository code

**`docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml`** states:

- `status: draft`
- `No notification inbox route or presentation surface currently exists in the codebase.`
- `Notification/Presentation contains only placeholder directories ... no implementation exists.`
- `principal_resolution.status: open_review` / `Notification module has no bound principal-to-employee resolution mechanism for presentation`

Repository code contains implemented `NotificationInboxPage`, `notification-inbox-page.blade.php`, `app/Modules/Notification/Presentation/Routes/web.php`, route registration in `routes/web.php`, and `NotificationPrincipalEmployeeResolver` bound in `NotificationServiceProvider`.

**`docs/ui/decisions/notifications/notification-inbox-read-only-list.open-decisions-resolution.yaml`** states `status: resolved` for route, principal resolution, and authorization semantics.

**`docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md`** states `Lock status: draft`, `Coding authorized: false`, while implementation files and `NotificationInboxUiFlowTest.php` exist in repository.

### README vs test files

**`app/Modules/Notification/README.md`** deferred list includes `Deep-link feature tests (US3)` and `architecture boundary test`.

Repository contains `tests/Feature/Modules/Notification/NotificationDeepLinkTest.php` and `tests/Architecture/NotificationBoundaryTest.php`.

### DTO fields vs UI mapping

`NotificationProjectionDto` includes `entityType`, `entityId`, `deepLinkRoute`. `NotificationInboxPage::mapProjectionRow()` omits these fields. Governance contract `not_consumed_in_v1` lists the same three fields as excluded from v1 list consumption.

### `read_at` mapping vs display

`NotificationInboxPage::mapProjectionRow()` maps `read_at` with Jalali formatting. Inbox Blade table does not include a `read_at` column.

### Placeholder directories vs implemented files

`.gitkeep` files remain in `Presentation/Livewire/`, `Presentation/Routes/`, etc., while concrete implementation files exist in those areas.

### Module README scope statement

README opening line: `In-app notification delivery and inbox state (spec09 Wave 1).` Deferred section does not mention inbox UI presentation; does not state inbox UI is implemented.

## Evidence Index

| Topic | Primary evidence paths |
|---|---|
| Inbox read contract | `app/Modules/Notification/Application/Contracts/NotificationInboxReadContract.php` |
| Inbox read service | `app/Modules/Notification/Application/Services/NotificationInboxReadService.php` |
| Projection DTO | `app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php` |
| Repository / queries | `app/Modules/Notification/Infrastructure/Repositories/NotificationRepository.php` |
| Persistence model | `app/Modules/Notification/Infrastructure/Persistence/Models/NotificationLogModel.php` |
| Migration schema | `database/migrations/modules/notification/2026_07_02_000001_create_notification_logs_table.php` |
| Domain notification | `app/Modules/Notification/Domain/Models/Notification.php` |
| Mark read | `app/Modules/Notification/Application/Services/MarkNotificationReadAction.php` |
| Principal resolver | `app/Modules/Notification/Application/Services/NotificationPrincipalEmployeeResolver.php` |
| Service provider bindings | `app/Modules/Notification/Infrastructure/Providers/NotificationServiceProvider.php` |
| Web route registration | `routes/web.php`, `app/Modules/Notification/Presentation/Routes/web.php` |
| Livewire inbox page | `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` |
| Inbox Blade view | `resources/views/livewire/notification/notification-inbox-page.blade.php` |
| App layout nav | `resources/views/components/layouts/app.blade.php` |
| Middleware alias | `app/Modules/Request/Presentation/Http/Middleware/EnforceSessionMutationPrincipalMiddleware.php`, `bootstrap/app.php` (alias registration via `AppServiceProvider`) |
| Backend inbox tests | `tests/Feature/Modules/Notification/NotificationInboxTest.php` |
| Deep link tests | `tests/Feature/Modules/Notification/NotificationDeepLinkTest.php` |
| UI flow tests | `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` |
| Architecture tests | `tests/Architecture/NotificationBoundaryTest.php` |
| Feature contract (draft) | `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` |
| Open decisions (resolved) | `docs/ui/decisions/notifications/notification-inbox-read-only-list.open-decisions-resolution.yaml` |
| Implementation lock (draft) | `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` |
| Module README | `app/Modules/Notification/README.md` |
| Parallel Request resolver pattern | `app/Modules/Request/Application/Services/RequestPrincipalEmployeeResolver.php`, `app/Modules/Request/Presentation/Livewire/RequestListPage.php` |
