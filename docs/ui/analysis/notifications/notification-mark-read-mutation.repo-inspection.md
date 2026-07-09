# Repository Inspection: P5 — Notification Mark-Read Mutation

## Feature

- **Feature code:** `notification-mark-read-mutation`
- **Feature title:** P5 — Notification Mark-Read Mutation
- **Domain area:** notifications

## Inspection date

2026-07-09 (current workspace state)

## Inspection scope

Fact-only inspection across:

1. Entry points (routes, navigation, row/item actions, mark-read triggers)
2. Existing UI surfaces (inbox page, Livewire, Blade, read/unread indicators, action affordances)
3. Backend and mutation paths (actions, services, contracts, persistence)
4. Authorization and principal handling
5. Existing tests
6. Governance context (predecessor P2 read-only inbox artifacts, frozen boundaries)
7. Gap evidence (implemented / partial / absent)

No solution design, scope decisions, or implementation performed.

---

## Findings

### Summary

The repository contains a **complete backend mark-read mutation path** (contract, action, domain behavior, repository persistence, service-provider binding) with **feature-level backend tests**. The **notification inbox UI is read-only**: it displays read/unread state but exposes **no mark-read trigger**, **no Livewire mutation method**, and **no HTTP/API mutation endpoint** for marking notifications read. Predecessor P2 governance artifacts explicitly **exclude mark-read** from the reconciled read-only inbox baseline.

### Entry points

| Entrypoint type | Evidence |
|---|---|
| Inbox route | `GET /notifications` → `notifications.index` in `app/Modules/Notification/Presentation/Routes/web.php`, mounted from `routes/web.php` under authenticated middleware |
| Layout navigation to inbox | **Absent** — `resources/views/components/layouts/app.blade.php` links only to `requests.index`; no `notifications.index` link |
| Row/item mark-read action | **Absent** — inbox Blade rows are plain `<tr>` cells with no `wire:click`, buttons, links, or dropdown items for mark-read |
| Dedicated mark-read route/endpoint | **Absent** — no additional routes in `app/Modules/Notification/Presentation/Routes/web.php`; no notification routes in `routes/api.php` |
| Programmatic/backend entry | `MarkNotificationReadContract::markRead()` callable via container; used in backend feature tests only |

### UI surfaces

| Surface | Path | Mark-read relevance |
|---|---|---|
| Livewire inbox page | `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Read-only: `refreshList()` calls `NotificationInboxReadContract` only; no mutation methods |
| Blade inbox view | `resources/views/livewire/notification/notification-inbox-page.blade.php` | Displays `is_read` as `خوانده شده` / `خوانده نشده`; header has refresh button only |
| Other Notification Livewire/Blade | **None found** under `app/Modules/Notification/Presentation/` or `resources/views/livewire/notification/` |
| Controllers | `app/Modules/Notification/Presentation/Controllers/` contains only `.gitkeep` |

`NotificationInboxPage::mapProjectionRow()` maps `id`, `is_read`, and `read_at` (Jalali-formatted) but Blade does not render `read_at` or expose `id` in actionable controls.

### Backend and mutation paths

| Layer | Symbol | Path | Status |
|---|---|---|---|
| Contract | `MarkNotificationReadContract` | `app/Modules/Notification/Application/Contracts/MarkNotificationReadContract.php` | Exists |
| Action | `MarkNotificationReadAction` | `app/Modules/Notification/Application/Services/MarkNotificationReadAction.php` | Exists, bound in `NotificationServiceProvider` |
| Domain | `Notification::markRead()` | `app/Modules/Notification/Domain/Models/Notification.php` | Exists; idempotent when already read (`readAt !== null` returns `$this`) |
| Repository | `NotificationRepository::save()` / `update()` | `app/Modules/Notification/Infrastructure/Repositories/NotificationRepository.php` | Persists `read_at` on update |
| Persistence | `notification_logs.read_at` | `database/migrations/modules/notification/2026_07_02_000001_create_notification_logs_table.php` | Nullable `timestampTz`; partial index `notification_logs_unread_idx` |
| Read after mutation | `NotificationInboxReadContract::findByIdForRecipient` | via `NotificationInboxReadService` | Returns updated `isRead` / `readAt` in `NotificationProjectionDto` |
| Presentation consumption | — | `NotificationInboxPage` | **Does not** inject or call `MarkNotificationReadContract` |

**`MarkNotificationReadAction::markRead()` behavior (evidenced):**

1. `findByIdForRecipient(notificationId, recipientEmployeeId)` — recipient-scoped lookup, excludes archived
2. Throws `ValidationException('Notification not found for recipient.')` when not found
3. `save($notification->markRead($readAt))` — sets `read_at` UTC timestamp

No separate command handler, controller, or job wraps this action beyond the Application service.

### Authorization and principal handling

| Mechanism | Evidence for mark-read |
|---|---|
| Route middleware | Inbox uses `auth:api`, `request.mutation.principal`, `audit.principal` (`routes/web.php`) |
| Principal → employee | `NotificationPrincipalEmployeeResolver::requireEmployeeId()` resolves via `MutationPrincipalContextPort` + `EmployeeRepositoryContract` |
| Recipient ownership | Enforced in `MarkNotificationReadAction` through `findByIdForRecipient`; cross-recipient `markRead` throws `ValidationException` (`NotificationInboxTest`) |
| Notification Policy/Gate | **No** `NotificationMutationAuthorizationGate`, Policy, or Gate classes under `app/Modules/Notification/` |
| MPEP mutation registry | `MarkNotificationReadAction` listed in `PendingMutationAuthorizationRegistry::PENDING` (`app/Application/Mutation/Registry/PendingMutationAuthorizationRegistry.php`) — grandfathered pending domain authorization adoption |

Mark-read authorization is **recipient-scoped data access** at the Application/repository layer, not a separate permission model. No UI-side authorization mirroring found (none expected given absent UI mutation).

### Tests

| Test file | Mark-read / read-state coverage |
|---|---|
| `tests/Feature/Modules/Notification/NotificationInboxTest.php` | `marks a notification as read`; `filters unread notifications` (post-markRead unread filter + `countUnread`); `denies cross-recipient inbox access` (markRead denied for wrong recipient) |
| `tests/Feature/Modules/Notification/NotificationDeepLinkTest.php` | Uses `markRead` as setup step; asserts `isRead` true on projection after mark |
| `tests/Feature/Modules/Notification/NotificationDeliveryTest.php` | Asserts `read_at` null on delivery |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | UI list rendering incl. `خوانده نشده`; architecture guard **asserts** `NotificationInboxPage.php` does **not** contain `MarkNotificationReadContract` |
| `tests/Architecture/NotificationBoundaryTest.php` | Module boundary checks; resolves read/delivery contracts only |

**Absent test evidence (searched):**

- Livewire/UI test invoking mark-read from inbox
- UI test asserting read/unread label changes after user mark-read action
- UI test for mark-read error propagation (not found, validation, cross-recipient)
- Dedicated idempotency test for double `markRead` (domain is idempotent; no explicit test found)
- `tests/Feature/Mutation/` coverage for `MarkNotificationReadAction`

### Governance context

| Artifact | Path | Relevance to P5 mark-read |
|---|---|---|
| P2 reconciliation closeout | `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md` | Status `IMPLEMENTED_RECONCILED` / `CLOSED`; explicitly states reconciliation does **not** add `mark-as-read / mark persistence design` — requires separate approval |
| P2 feature contract | `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` | Lists `MarkNotificationReadContract` as **out of scope** and **forbidden** for UI consumption; `No mutation controls, mark-read actions` |
| P2 implementation lock | `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` | Out of scope: `mark-read / mark-all-as-read`; forbidden: `MarkNotificationReadContract` usage, mark-read controls |
| P2 open decisions | `docs/ui/decisions/notifications/notification-inbox-read-only-list.open-decisions-resolution.yaml` | `mark-read` explicitly out of scope |
| P2 repo inspection | `docs/ui/analysis/notifications/notification-inbox-read-only.repo-inspection.md` | Documents backend `MarkNotificationReadContract` exists; presentation does not consume it |
| P2 feature analysis | `docs/ui/analysis/notifications/notification-inbox-read-only.feature-analysis.md` | P2 read-only baseline analysis |
| P5-specific governance | **No** contract, lock, decision, analysis, or closeout artifacts found under `docs/ui/` for `notification-mark-read-mutation` |

**Frozen predecessor boundaries affecting P5:**

- P2 read-only inbox is closed/reconciled without mutation UI
- P2 contract/lock forbid introducing mark-read in the read-only feature scope
- P5 is a **separate** feature per closeout language

**Spec09 backend baseline (non-UI governance):**

- `specs/009-notification-delivery/tasks.md` marks T017/T019 (mark-read action + tests) complete
- `specs/009-notification-delivery/spec.md` FR-002 requires read/unread with read timestamp on mark

---

## Existing implementation evidence

### Implemented (backend write path)

- `MarkNotificationReadContract` interface with `markRead(string $notificationId, string $recipientEmployeeId, DateTimeImmutable $readAt): void`
- `MarkNotificationReadAction` implementation with recipient-scoped lookup and `ValidationException` on miss
- `Notification::markRead()` domain transition (idempotent for already-read)
- `NotificationRepository` update path writing `read_at`
- `notification_logs.read_at` column and unread partial index
- DI binding: `MarkNotificationReadContract` → `MarkNotificationReadAction` in `NotificationServiceProvider`
- Backend tests proving mark-read persistence, unread count decrease, and cross-recipient denial

### Implemented (read-only UI baseline — predecessor P2)

- `NotificationInboxPage` Livewire component with `refreshList()` read path
- `notification-inbox-page.blade.php` table with read/unread status column
- `GET /notifications` authenticated route
- `NotificationPrincipalEmployeeResolver` for employee context on read
- `NotificationInboxUiFlowTest` for inbox access, states, and read-only architecture guard

### Partially implemented

- **Read-state display without mutation affordance:** UI shows `is_read` labels but provides no user action to change state
- **Mutation authorization maturity:** `MarkNotificationReadAction` is in `PendingMutationAuthorizationRegistry` (no module-specific `*MutationAuthorizationGate`); recipient scoping is implemented, formal MPEP gate is not
- **Inbox discoverability:** direct URL `/notifications` works; layout navigation link to inbox remains absent (deferred in P2 lock)

### Absent

- Livewire method (e.g. `markAsRead`) on `NotificationInboxPage` or any Notification presentation class
- Blade/UI controls (button, row click, dropdown item) triggering mark-read
- HTTP POST/PATCH route or API endpoint for mark-read
- Notification presentation controller for mutations
- UI/feature tests for mark-read interaction from inbox
- P5 governance artifacts (contract, lock, analysis, decisions)
- `HandlesUiMutationFeedback` or equivalent mutation feedback wiring on notification inbox (trait exists at `app/Support/Presentation/Concerns/HandlesUiMutationFeedback.php`; used by `RequestCreatePage`, not by `NotificationInboxPage`)

---

## Existing authorization evidence

| Check | Location | Result |
|---|---|---|
| Guest blocked from inbox | `NotificationInboxUiFlowTest` | Redirect to `/login` |
| Authenticated middleware stack | `notifications.index` route | `auth:api`, `request.mutation.principal`, `audit.principal` |
| Principal without employee | `NotificationInboxPage::refreshList` + `NotificationPrincipalEmployeeResolver` | Error state with message `Authenticated principal has no linked employee.` |
| Mark-read recipient isolation | `MarkNotificationReadAction` + `NotificationInboxTest` | Wrong `recipientEmployeeId` → `ValidationException` |
| Notification-specific permission model | Notification module | **Not found** |
| MPEP authorization gate on mark-read | `PendingMutationAuthorizationRegistry` | `MarkNotificationReadAction` listed as **pending** |

---

## Existing test evidence

### Backend (mark-read mutation)

| Test | Assertion |
|---|---|
| `it('marks a notification as read')` | `isRead` true; `readAt` timestamp persisted |
| `it('filters unread notifications')` | After `markRead`, unread list excludes marked item; `countUnread` = 1 |
| `it('denies cross-recipient inbox access')` | `markRead` by non-recipient throws `ValidationException` |
| `NotificationDeepLinkTest` | `markRead` then projection `isRead` true |

### UI (inbox — no mark-read)

| Test | Assertion |
|---|---|
| `renders populated inbox rows from the read contract` | Sees `خوانده نشده` for unread delivered items |
| `keeps the livewire inbox page free of persistence orchestration smells` | Source must **not** contain `MarkNotificationReadContract` |

### Not found

- UI test calling a mark-read Livewire action
- UI test verifying status label flips to `خوانده شده` after user mutation
- Regression test locking mark-read UI behavior

---

## Governance artifact evidence

| Artifact | Present | Notes |
|---|---|---|
| P2 closeout reconciliation | Yes | `notification-inbox-read-only.reconciliation.md` — CLOSED; defers mutation to separate approval |
| P2 contract / lock / decisions | Yes | Explicitly forbid mark-read in read-only scope |
| P5 contract | **No** | — |
| P5 lock | **No** | — |
| P5 analysis (prior) | **No** | This document is the P5 repo inspection |
| P5 closeout | **No** | — |

Predecessor constraint (verbatim intent from closeout): *"This reconciliation does not add or approve: notification mutation actions, mark-as-read / mark persistence design."*

Backend mark-read persistence **does exist** in code (spec09); closeout language refers to **not approving mutation as part of P2**, not asserting backend absence.

---

## Gap summary

| Area | Status | Evidence |
|---|---|---|
| Backend mark-read contract & action | **Implemented** | `MarkNotificationReadContract`, `MarkNotificationReadAction` |
| Domain & persistence write path | **Implemented** | `Notification::markRead`, `read_at` column, repository `save`/`update` |
| Backend mark-read tests | **Implemented** | `NotificationInboxTest`, `NotificationDeepLinkTest` |
| Inbox UI read/unread display | **Implemented** (P2) | Blade `is_read` column |
| Inbox UI mark-read trigger | **Absent** | No `wire:click`, buttons, or mutation methods |
| Presentation → `MarkNotificationReadContract` wiring | **Absent** | Confirmed by component source and architecture guard test |
| Mark-read HTTP/API surface | **Absent** | Single GET route only |
| UI tests for mark-read mutation | **Absent** | — |
| P5 governance artifacts | **Absent** | No contract/lock for mark-read mutation feature |
| Layout nav to inbox | **Absent** (deferred P2) | May affect discoverability of inbox, not backend mutation |
| Notification `*MutationAuthorizationGate` | **Absent** | Action in pending registry; recipient scoping only |

**Conflicting/partial evidence to record:**

- Spec09/tasks mark backend mark-read as complete; P2 UI governance forbids UI consumption of the same contract — intentional scope separation, not code conflict
- `NotificationInboxPage` maps `read_at` but Blade does not display it; irrelevant to mutation gap
- `PendingMutationAuthorizationRegistry` flags `MarkNotificationReadAction` as authorization-pending; mutation still functions in feature tests without a dedicated gate

---

## Initial classification

**`UI_BEHAVIOR_GAP`**

Backend mark-read mutation path (contract, action, domain, repository, persistence, backend tests) is present. The gap is the **absence of user-facing mutation behavior** in the notification inbox presentation layer: no trigger, no Livewire delegation to `MarkNotificationReadContract`, no UI tests, and an explicit architecture guard preventing contract usage in `NotificationInboxPage`.

---

## Recommended next step

Proceed to **P5 feature analysis** (`notification-mark-read-mutation.feature-analysis.md`) to classify the UI behavior gap against governance requirements (UI Anti-Leak contract, predecessor P2 frozen boundaries, mutation feedback patterns such as `HandlesUiMutationFeedback`, and whether mark-read authorization remains recipient-scoped auth-only or requires explicit gate/MPEP resolution) — **without** drafting contract, lock, or implementation in this inspection step.
