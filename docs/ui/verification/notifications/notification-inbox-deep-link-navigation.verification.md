# P6 — Notification Inbox Deep-Link Navigation — Implementation Verification

## Feature

- **Feature code:** `notification-inbox-deep-link-navigation`
- **Feature title:** P6 — Notification Inbox Deep-Link Navigation
- **Domain area:** notifications

## Verification date

2026-07-09

## Inputs reviewed

- `docs/ui/contracts/notifications/notification-inbox-deep-link-navigation.feature-contract.yaml` (v0.1.0)
- `docs/ui/locks/notifications/notification-inbox-deep-link-navigation.implementation-lock.md`
- `docs/ui/decisions/notifications/notification-inbox-deep-link-navigation.review-decision.md`

## Authorized implementation boundary

Per approved lock, implementation was authorized only in:

| Path | Role |
|---|---|
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Row mapping + frozen `requests.show` URL |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | `مشاهده` link in عملیات |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | P6 UI tests + architecture guard |

## Observed implementation summary

### Files changed

1. `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php`
2. `resources/views/livewire/notification/notification-inbox-page.blade.php`
3. `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php`

No backend, route, DTO, service, migration, or P2/P5 governance artifact modifications.

### Livewire

- Adds `APPROVED_REQUEST_SHOW_ROUTE = 'requests.show'`.
- `mapProjectionRow()` sets `request_show_url` only when `deepLinkRoute === 'requests.show'` and `entityId` is non-null.
- URL generated via `route(self::APPROVED_REQUEST_SHOW_ROUTE, ['requestId' => $projection->entityId])`.
- P5 `markNotificationRead` and `refreshList` unchanged.

### Blade

- عملیات column wraps mark-read and navigation in a flex container.
- Renders `مشاهده` anchor when `request_show_url` is non-null.
- P5 mark-read button unchanged for unread rows.

## Acceptance criteria mapping

| AC | Status | Evidence |
|---|---|---|
| AC-DL-001 | Pass | Eligible row test renders `مشاهده` |
| AC-DL-002 | Pass | Null `entityId` test — no navigation |
| AC-DL-003 | Pass | `allocations.show` test — no navigation |
| AC-DL-004 | Pass | Null `deepLinkRoute` test — no navigation |
| AC-DL-005 | Pass | Architecture guard — no `entityType` routing |
| AC-DL-006 | Pass | Unread eligible row retains mark-read + navigation |
| AC-DL-007 | Pass | `request_show_url` equals `route('requests.show', ['requestId' => $requestId])` |
| AC-DL-008 | Pass | Non-allowlisted route — no web navigation |

## Tests executed

```bash
php artisan test tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php
```

**Result:** 25 passed (77 assertions)

## Verification verdict

**IMPLEMENTED_WITHIN_LOCK** — P6 narrow deep-link navigation delivered within approved presentation-only scope.
