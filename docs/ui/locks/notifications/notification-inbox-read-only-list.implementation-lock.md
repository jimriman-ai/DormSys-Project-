# Notification Inbox (Read-Only List) — Implementation Lock

## 1. Scope

Greenfield read-only notification inbox list presentation for authenticated employee users.

In scope:

- `GET /notifications` presentation entrypoint (`notifications.index`)
- single full-page Livewire inbox list surface
- `NotificationPrincipalEmployeeResolver::requireEmployeeId` wiring
- `NotificationInboxReadContract::listForRecipient` consumption
- `NotificationProjectionDto` list rendering
- UI states: loading, empty, ready, error
- initial presentation cap of 50 items

Out of scope:

- countUnread
- badge
- detail view
- deep-link navigation
- mark-read / mark-all-as-read
- archive / delete / restore
- filtering
- search
- sorting
- realtime / polling / websocket
- pagination
- backend contract, DTO, repository, migration, or schema changes
- direct database access from UI
- Notification-specific Gate or new permission model
- layout navigation link (deferred; not authorized by this lock)

---

## 2. Approved Inputs

| Authority | Reference |
|---|---|
| Feature Contract | `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` |
| Open Decisions Resolution | `docs/ui/decisions/notifications/notification-inbox-read-only-list.open-decisions-resolution.yaml` v1.0.0 |
| Middleware verification | `auth:api`, `request.mutation.principal`, `audit.principal` — PASS |
| Pattern authority | Request read-only Livewire web presentation flow (`routes/web.php`, `RequestListPage`) |

No additional architectural decisions may be introduced during implementation.

---

## 3. Change Boundaries

| Layer | Boundary |
|---|---|
| Presentation | greenfield inbox page, blade view, module route file, route group registration |
| Application | `NotificationPrincipalEmployeeResolver` only (approved resolver) |
| Infrastructure | no changes |
| Domain | no changes |
| Database | no changes |
| Shared auth/middleware | no changes |

Recipient visibility remains backend-authoritative via `NotificationRepository` recipient-scoped queries.

---

## 4. Allowed Changes

- Notification presentation route definition for `GET /notifications` named `notifications.index` within existing repository conventions
- Notification inbox Livewire page class within existing Notification presentation conventions
- Notification inbox blade view within existing Notification presentation conventions
- `app/Modules/Notification/Application/Services/NotificationPrincipalEmployeeResolver.php` (create)
- Service provider binding for resolver following existing Notification module registration conventions
- Route registration within existing authenticated web session group following module conventions
- Feature tests for this lock's test obligations
- This lock artifact (governance administration only)

Presentation formatting permitted:

- Jalali display of `createdAt` / `readAt`
- Display of existing DTO field values without semantic remapping

Deferred and not authorized by this lock:

- localized `notificationType` labels
- localized `priority` labels
- message truncation policy
- layout navigation link

---

## 5. Forbidden Changes

- `NotificationInboxReadContract`
- `NotificationInboxReadService`
- `NotificationRepository`
- `NotificationProjectionDto`
- `NotificationLogModel`
- `MarkNotificationReadContract`
- notification migrations / schema
- countUnread consumption
- badge surfaces
- detail routes / detail views
- row-click navigation
- deep-link consumption (`deepLinkRoute`, `entityType`, `entityId`)
- mark-read / mark-all-as-read actions
- archive / delete / restore actions
- filtering / search / sorting controls
- pagination UI or backend pagination support
- realtime / polling / websocket refresh
- Notification-specific Gate / permission definitions
- auth / middleware / guard / policy changes
- cross-module Infrastructure imports from Notification presentation
- direct Eloquent or database access from presentation
- layout navigation changes (deferred)
- modification of existing `NotificationInboxTest` or other spec09 backend tests

---

## 6. Route Boundary

| Property | Locked value |
|---|---|
| HTTP method | `GET` |
| Path | `/notifications` |
| Named route | `notifications.index` |
| Route file | `app/Modules/Notification/Presentation/Routes/web.php` |
| Registration | `Route::prefix('notifications')->group(...)` inside authenticated web session group in `routes/web.php` |
| Livewire entry | single full-page component at route root (`/`) |
| Additional routes | forbidden |

---

## 7. Presentation Boundary

Authorized:

- one Livewire full-page inbox list component
- blade view for list, empty, loading, and error states
- read-only row rendering from mapped DTO fields
- display fields: `id`, `notificationType`, `title`, `message`, `isRead`, `readAt`, `createdAt`, `priority`
- initial presentation cap of 50 items

Forbidden:

- second inbox page or alternate entrypoint
- detail view or row-click interactions
- deep-link activation
- mark-read controls
- unread badge / count display
- filter / search / sort controls
- pagination / infinite scroll
- realtime refresh
- UI-owned authorization, ownership, or recipient visibility logic
- consumption of `entityType`, `entityId`, `deepLinkRoute` for navigation

---

## 8. Principal Resolution Wiring Boundary

| Property | Locked value |
|---|---|
| Resolver | `App\Modules\Notification\Application\Services\NotificationPrincipalEmployeeResolver` |
| Method | `requireEmployeeId()` |
| Composition | `MutationPrincipalContextPort` + `EmployeeRepositoryContract` |
| Pattern authority | `App\Modules\Request\Application\Services\RequestPrincipalEmployeeResolver` |
| UI ownership | forbidden — UI does not own principal resolution logic |
| Livewire usage | inject resolver; call `requireEmployeeId()`; pass result to read contract |
| Direct resolution in Blade / Livewire / Eloquent | forbidden |

Resolver failures surface as inbox page error state.

---

## 9. Read Contract Usage Boundary

| Property | Locked value |
|---|---|
| Contract | `App\Modules\Notification\Application\Contracts\NotificationInboxReadContract` |
| Method | `listForRecipient` |
| Call signature | `listForRecipient($employeeId, null, 50)` |
| View-model source | `App\Modules\Notification\Application\DTOs\NotificationProjectionDto` only |
| `unreadOnly` parameter | not consumed (`null`) |
| Limit | `50` (initial presentation cap; no pagination) |
| Alternative queries | forbidden |
| Contract / DTO / repository changes | forbidden |

Backend recipient scoping remains authoritative; presentation must not filter for ownership or visibility.

---

## 10. Authorization Boundary

| Property | Locked value |
|---|---|
| Model | authenticated employee session via route middleware |
| Authority owner | backend |
| Implementation authority | `existing_application_authorization_flow` |
| Middleware bindings | `auth:api`, `request.mutation.principal`, `audit.principal` |
| Registration target | authenticated web session group in `routes/web.php` |
| Notification-specific Gate | none |
| New permission model | none |
| UI authorization evaluation | forbidden |

UI consumes backend access boundary only.

Recipient visibility authority: `NotificationInboxReadContract` via recipient-scoped repository queries.

---

## 11. Dependency Injection Expectations

Livewire page method injection:

| Dependency | Role |
|---|---|
| `NotificationInboxReadContract` | list read authority |
| `NotificationPrincipalEmployeeResolver` | approved employee context |

Resolver constructor injection:

| Dependency | Role |
|---|---|
| `MutationPrincipalContextPort` | principal context |
| `EmployeeRepositoryContract` | principal-to-employee lookup |

Service provider bindings:

- `NotificationPrincipalEmployeeResolver` registered in `NotificationServiceProvider`
- `NotificationInboxReadContract` remains bound to `NotificationInboxReadService` (existing; unchanged)

Forbidden injection / usage:

- `MarkNotificationReadContract`
- `NotificationRepositoryContract` from presentation
- Eloquent models from presentation
- cross-module Infrastructure types from presentation

---

## 12. Error Handling Boundary

| Condition | Required behavior |
|---|---|
| Principal resolution failure | error UI state; user-visible message; no silent failure |
| Read contract failure | error UI state; user-visible message; no silent failure |
| Empty inbox | empty UI state; no error |
| Successful load | ready UI state |

Forbidden:

- stack traces or internal diagnostics in UI
- silent swallowing of resolver or read failures

---

## 13. Test Obligations

Minimum required coverage:

| Area | Requirement |
|---|---|
| Route | `GET /notifications` exists; named `notifications.index` |
| Access | unauthenticated redirect/deny; authenticated reachability under approved middleware |
| Principal resolution | authenticated principal resolves to employee via `NotificationPrincipalEmployeeResolver` |
| Principal failure | resolver failure produces error state |
| Read contract | page invokes `NotificationInboxReadContract::listForRecipient` with limit 50 |
| List rendering | populated list renders DTO-backed fields |
| Cap | at most 50 items presented |
| Empty state | empty inbox renders without error |
| Error state | read failure renders bounded error state |
| Scope protection | tests do not assert mark-read, detail, deep-link, countUnread, badge, filter, sort, pagination, or realtime behavior |
| Regression | existing `NotificationInboxTest` and spec09 backend tests remain unmodified and passing |

Suggested test location: `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php`

---

## 14. Review Gates

Reject implementation if any of the following occur:

- route path or name diverges from locked values
- middleware bindings differ from approved stack
- UI owns principal resolution logic
- read path bypasses `NotificationInboxReadContract`
- `NotificationProjectionDto` shape or read contract is modified
- limit differs from 50 or pagination is introduced
- any forbidden scope item is implemented
- presentation accesses database or Infrastructure directly
- Notification-specific Gate or permission model is introduced
- layout navigation is changed without separate authorization
- deferred presentation decisions are treated as resolved without governance update
- existing spec09 backend tests are modified to accommodate presentation shortcuts

---

## 15. Definition of Done

- `GET /notifications` reachable as `notifications.index`
- greenfield read-only Livewire inbox page exists
- `NotificationPrincipalEmployeeResolver::requireEmployeeId` wired; UI does not own resolution
- list loaded exclusively via `NotificationInboxReadContract::listForRecipient($employeeId, null, 50)`
- rows render from `NotificationProjectionDto` fields only
- loading, empty, ready, and error states implemented
- initial presentation cap of 50 items enforced
- approved middleware stack applied
- no forbidden scope implemented
- no backend contract, DTO, repository, or migration changes
- required tests pass
- lock review passes

---

## 16. Status

| Field | Value |
|---|---|
| Feature ID | `notification-inbox-read-only-list` |
| Lock version | `1.0.0` |
| Lock status | `draft` |
| Coding authorized | `false` |
| Scope expansion | `not_allowed` |
| Architectural reopen | `not_allowed` |
| Preconditions for coding | feature contract approved; this lock approved; open decisions resolution v1.0.0 accepted |
