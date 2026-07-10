# Notification Inbox Unread Badge — Repository Inspection

## Feature

`notification-inbox-unread-badge` — notifications / presentation

## Inspection date

2026-07-10

## Inspection scope

Repository-observable facts only for unread notification badge / `countUnread` consumption baseline. No product decisions, design, or governance conclusions beyond inspection status.

---

## 1. Inspection Summary

Unread **badge UI does not exist** in the repository. No badge markup, numeric unread counter, or `countUnread` consumption appears in presentation-layer views or Livewire components.

Unread **count backend support exists** and is production-ready within the Notification module: `NotificationInboxReadContract::countUnread()` is implemented through `NotificationInboxReadService` → `NotificationRepository::countUnread()`, backed by `notification_logs.read_at` and a partial index for unread rows.

The shared layout exposes a P7 **اعلان‌ها** nav link to `notifications.index` with no badge. The notification inbox page lists per-row read/unread status but does not display an aggregate unread count.

Repository baseline is sufficient to proceed to feature-analysis.

---

## 2. Inputs Reviewed

### Code files

| Path | Role |
|---|---|
| `app/Modules/Notification/Application/Contracts/NotificationInboxReadContract.php` | Application read contract including `countUnread` |
| `app/Modules/Notification/Application/Contracts/NotificationRepositoryContract.php` | Repository contract including `countUnread` |
| `app/Modules/Notification/Application/Services/NotificationInboxReadService.php` | Application service delegating `countUnread` |
| `app/Modules/Notification/Infrastructure/Repositories/NotificationRepository.php` | Repository query implementation |
| `app/Modules/Notification/Domain/Models/Notification.php` | Domain model; `readAt`, `isRead()` |
| `app/Modules/Notification/Infrastructure/Persistence/Models/NotificationLogModel.php` | Eloquent model; `read_at`, `archived_at` |
| `app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php` | Per-row projection; `isRead`; no aggregate count field |
| `app/Modules/Notification/Application/Services/NotificationPrincipalEmployeeResolver.php` | Principal → employee scoping for inbox flows |
| `app/Modules/Notification/Infrastructure/Providers/NotificationServiceProvider.php` | DI bindings for read/mark-read contracts |
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Inbox Livewire page; uses `listForRecipient` only |
| `app/Modules/Notification/Presentation/Routes/web.php` | `notifications.index` route |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Inbox Blade view |
| `resources/views/components/layouts/app.blade.php` | Shared layout header nav (P7) |
| `routes/web.php` | Authenticated route group and `notifications` prefix |
| `database/migrations/modules/notification/2026_07_02_000001_create_notification_logs_table.php` | `read_at` column and unread partial index |

### Test files

| Path | Role |
|---|---|
| `tests/Feature/Modules/Notification/NotificationInboxTest.php` | Backend `countUnread` assertions |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | UI flows; P7 layout-nav tests; architecture guard |
| `tests/Feature/Modules/Notification/NotificationIdempotencyTest.php` | Test double delegating `countUnread` |
| `tests/Feature/Modules/Request/RequestUiFlowTest.php` | Cross-module layout nav regression |

### Governance artifacts (read-only context)

| Path | Role |
|---|---|
| `docs/ui/review/governance-next-candidate-triage.md` | Selected this candidate; next gate repo-inspection |
| `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` | P2 deferral of `countUnread` / badge |
| `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` | P2 exclusions |
| `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml` | P5 exclusions |
| `docs/ui/locks/notifications/notification-mark-read-mutation.implementation-lock.yaml` | P5 forbids `countUnread` in inbox page |
| `docs/ui/contracts/notifications/notification-inbox-deep-link-navigation.feature-contract.yaml` | P6 exclusions |
| `docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml` | P7 exclusions; AC-LN-005 no badge |
| `docs/ui/closeouts/notifications/notification-inbox-layout-navigation.closeout.md` | P7 closed; badge explicitly not delivered |
| `specs/009-notification-delivery/contracts/notification-inbox-read-contract.md` | Spec09 design baseline for `countUnread` |

---

## 3. Backend Notification Baseline

### Model / persistence

| Item | Evidence |
|---|---|
| Table | `notification_logs` (`NotificationLogModel`) |
| Read status field | `read_at` nullable timestamp; unread when `read_at IS NULL` |
| Archive exclusion | `archived_at IS NULL` required for inbox list and count |
| Delivery filter | `delivery_status = Delivered` |
| Recipient scoping | `recipient_employee_id` on all inbox queries |
| Unread index | Partial index `notification_logs_unread_idx` on `(recipient_employee_id, read_at) WHERE read_at IS NULL` |

### `countUnread` implementation chain

| Layer | Symbol | Path |
|---|---|---|
| Application contract | `NotificationInboxReadContract::countUnread(string $recipientEmployeeId): int` | `app/Modules/Notification/Application/Contracts/NotificationInboxReadContract.php` |
| Application service | `NotificationInboxReadService::countUnread()` → repository | `app/Modules/Notification/Application/Services/NotificationInboxReadService.php` |
| Repository contract | `NotificationRepositoryContract::countUnread()` | `app/Modules/Notification/Application/Contracts/NotificationRepositoryContract.php` |
| Repository query | Count where `recipient_employee_id` match, `delivery_status = Delivered`, `archived_at IS NULL`, `read_at IS NULL` | `app/Modules/Notification/Infrastructure/Repositories/NotificationRepository.php` |

### Classification

`countUnread` is **production application-contract + infrastructure repository query code**. It is not a UI helper, domain-only service without persistence, or test-only helper (though tests invoke the contract directly).

### Presentation-layer usage

**Confirmed absent.** Grep of `app/` shows `countUnread` only in Application contracts/services and Infrastructure repository — not in `Presentation/` or `resources/views/`.

### Auth / recipient scoping

- Inbox Livewire flows resolve employee via `NotificationPrincipalEmployeeResolver::requireEmployeeId()` (principal → linked employee).
- `countUnread` accepts `recipientEmployeeId` as parameter; caller must supply scoped id (same pattern as `listForRecipient`).
- Web routes use middleware `auth:api`, `request.mutation.principal`, `audit.principal` (confirmed in `NotificationInboxUiFlowTest`).

### Related read capabilities (not aggregate count)

- `listForRecipient($recipientEmployeeId, ?bool $unreadOnly, int $limit)` supports `unreadOnly: true` filter.
- `NotificationProjectionDto` carries per-row `isRead` / `readAt`; no unread-total field on DTO.

---

## 4. UI / Navigation Baseline

### Notification inbox UI

| File | Observed behavior |
|---|---|
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Page header **اعلان‌های من**; refresh button; table with per-row **خوانده شده** / **خوانده نشده**; P5 mark-read button; P6 **مشاهده** deep-link |
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | `refreshList()` calls `listForRecipient` only; `markNotificationRead()` then re-lists; no `countUnread` call |

### Shared layout navigation (P7)

| File | Observed behavior |
|---|---|
| `resources/views/components/layouts/app.blade.php` | Header `<nav>` with **درخواست‌ها** → `requests.index`, then **اعلان‌ها** → `notifications.index` |
| Active state | `request()->routeIs('requests.*')` / `request()->routeIs('notifications.*')` with `font-semibold text-sky-700` vs `text-slate-600 hover:text-slate-900` |
| Transport | Plain `href` anchors; no `wire:navigate` on layout nav items |
| Badge / counter | **Not present** — label text only beside each nav link |

### Mobile / desktop nav split

**Not found.** Single shared header nav; no separate mobile menu or sidebar pattern inspected.

### Existing badge / counter UI patterns

**None found** under `resources/views/` or `resources/views/components/ui/`. UI component inventory includes `alert`, `button`, `empty-state`, `form-field`, `page-header` only — no badge or counter component.

### Refresh / reactivity patterns (relevant to future badge)

| Pattern | Present in repository? |
|---|---|
| Server-rendered static layout (`app.blade.php`) | Yes — layout is Blade slot wrapper, not Livewire |
| Livewire reactive update on inbox page | Yes — `NotificationInboxPage` refreshes list after mark-read |
| `wire:poll` | Not found in codebase |
| View composers injecting nav data | Not found |
| Event-driven cross-page nav refresh | Not found |
| Polling / Alpine counter | Not found |

---

## 5. Route / Controller / Component Baseline

### Routes

| Named route | Method | Path | Handler |
|---|---|---|---|
| `notifications.index` | GET | `/notifications` | `NotificationInboxPage` Livewire full-page component |

Registered via `routes/web.php` authenticated group → `NotificationPresentationServiceProvider::notificationWebRoutePath()` → `app/Modules/Notification/Presentation/Routes/web.php`.

### Controllers

No dedicated HTTP controller for inbox UI; presentation is Livewire-only.

### Mark-read action

Livewire method `NotificationInboxPage::markNotificationRead()` delegates to `MarkNotificationReadContract::markRead()` — not a separate web route.

### Middleware / auth

`notifications.index` middleware stack (test-confirmed): `auth:api`, `request.mutation.principal`, `audit.principal`. Guests redirect to `/login`.

---

## 6. Test Baseline

### Notification test files

| File | Coverage relevant to badge |
|---|---|
| `NotificationInboxTest.php` | Backend `countUnread` after mark-read filter scenario; empty inbox `countUnread` = 0 |
| `NotificationInboxUiFlowTest.php` | Inbox UI states; P5 mark-read; P6 deep-link; P7 layout nav; architecture guard |
| `NotificationIdempotencyTest.php` | Delegating test double for `countUnread` |
| `NotificationDeepLinkTest.php` | Deep-link backend (not badge) |
| `NotificationDeliveryTest.php` | Delivery (not badge) |
| `NotificationRetentionTest.php` | Retention/archive (not badge) |
| `NotificationCheckInReminderTest.php` | Domain delivery (not badge) |
| `tests/Architecture/NotificationBoundaryTest.php` | Architecture boundary (not badge UI) |

### Unread count tests

| Test | Evidence |
|---|---|
| `it('filters unread notifications')` | `NotificationInboxTest.php` — `countUnread($employeeId)` expects `1` after one item marked read |
| `it('returns an empty inbox without error')` | `NotificationInboxTest.php` — `countUnread` expects `0` |

### Tests proving no badge currently exists

| Test | Evidence |
|---|---|
| `it('does not render unread badge or countUnread output in layout nav')` | `NotificationInboxUiFlowTest.php` — nav HTML must not contain `countUnread`; no numeric counter pattern; exactly 2 `<a>` tags in nav |
| Architecture guard on `NotificationInboxPage.php` | Same file — source must not contain `countUnread` string |

### Coverage gaps (inspection observation only)

- No positive UI test asserting badge rendering (expected — feature not implemented).
- No test covering layout-badge refresh after mark-read on a different page.
- No test for `countUnread` via UI/Livewire path (contract tested at application layer only).

---

## 7. Prior Governance Context

Unread badge / `countUnread` display was **explicitly deferred** across prior notification governance cycles:

| Predecessor | Deferred / excluded language (summary) |
|---|---|
| **P2** read-only inbox | Lock and contract exclude `countUnread` consumption and badge surfaces |
| **P5** mark-read mutation | Contract/lock exclude `countUnread`, badge; architecture guard forbids `countUnread` in `NotificationInboxPage` |
| **P6** deep-link navigation | Contract excludes unread badge / `countUnread` consumption |
| **P7** layout navigation | Contract AC-LN-005 and closeout confirm no badge on layout nav; closed 2026-07-10 |

Governance triage (`docs/ui/review/governance-next-candidate-triage.md`) selected `notification-inbox-unread-badge` as the next open candidate after P7 closeout.

**No closed predecessor is reopened by this inspection.** P2–P7 delivery artifacts remain authoritative for what was delivered; this feature addresses only the deferred badge/count presentation gap.

### Repository vs governance alignment

No material conflict observed: governance deferred badge; repository confirms backend count exists and UI does not consume it. P7 negative tests and closeout match current `app.blade.php` state.

---

## 8. Repository Findings

### Confirmed facts

1. **`countUnread` backend support exists** at application contract and repository levels with recipient scoping, delivered-status filter, archive exclusion, and `read_at IS NULL` semantics.
2. **Unread badge is not rendered** in any inspected view or Livewire component.
3. **Shared layout exposes notifications nav** (P7): **اعلان‌ها** link to `route('notifications.index')` without badge.
4. **Inbox page shows per-row read status** (`is_read` → خوانده شده / خوانده نشده) but not aggregate unread count.
5. **`NotificationInboxPage` does not call `countUnread`**; architecture guard test explicitly forbids `countUnread` in inbox page source (P5 carryover).
6. **No reusable badge/counter UI component** exists in the repository.
7. **Layout is static Blade**; inbox is Livewire — cross-surface count refresh would require a future architectural choice (not inspected/decided here).
8. **Tests cover backend `countUnread`** and **negative layout-nav badge absence** (P7 AC-LN-005).

### Unknowns (not determined from inspection)

- Optimal badge placement (layout nav vs inbox header vs both).
- Count refresh semantics after mark-read when user is on a non-inbox page.
- Whether zero-count badge should be hidden.
- Whether formal contract is required vs direct-UI path (governance decision, not repo fact).

### Unread count ownership

**Remains inside Notification module backend layers** (Application contract → Infrastructure repository). No evidence of presentation-layer ownership or layout-level query logic for unread counting today.

---

## 9. Risks / Ambiguity For Future Analysis

| Type | Item |
|---|---|
| **Placement ambiguity** | Two evidenced surfaces: shared layout nav (`app.blade.php`) vs inbox page header (`notification-inbox-page.blade.php`). No repository default. |
| **Refresh semantics ambiguity** | Layout is non-Livewire Blade; mark-read occurs on Livewire inbox page. Post-mutation nav badge update path not evidenced. |
| **Architecture guard conflict risk** | P5 lock and tests forbid `countUnread` in `NotificationInboxPage.php`; layout-badge vs inbox-badge choice affects which guards/tests require successor amendment. |
| **P7 test regression risk** | P7 test `does not render unread badge or countUnread output in layout nav` would conflict if badge is placed on layout nav without governance/test updates. |
| **UI Anti-Leak consideration** | Whether badge consumes raw `countUnread` in Blade/Livewire vs backend-shaped view payload is undecided (analysis scope). |
| **Authorization ambiguity** | Low — same authenticated middleware as inbox; recipient scoping via employee resolver pattern already established. |
| **Test coverage gap** | No positive badge UI tests; no cross-page refresh tests. |
| **Stale governance text risk** | `docs/ui/analysis/feature-status-repository-inspection.md` undercounts current `docs/ui/` inventory; not authoritative over code. |

---

## 10. Recommended Next Governance Gate

**`feature-analysis`**

Reason: Backend `countUnread` support, presentation absence, nav/inbox surfaces, route/auth baseline, test posture, and predecessor deferral context are evidenced concretely. No repository ambiguity blocks analysis. Placement, refresh, contract requirement, and guard supersession are analysis-stage decisions, not inspection blockers.

---

## 11. Final Inspection Status

**REPO_INSPECTION_COMPLETE_READY_FOR_FEATURE_ANALYSIS**

---

## Inspection Questions — Evidence Answers

| # | Question | Answer |
|---|---|---|
| 1 | Does unread count backend support currently exist? | **Yes** |
| 2 | Where implemented and how called? | `NotificationInboxReadContract::countUnread($recipientEmployeeId)` via `NotificationInboxReadService` → `NotificationRepository::countUnread()` |
| 3 | Is unread count rendered in UI? | **No** |
| 4 | Does shared layout expose notifications nav item? | **Yes** — **اعلان‌ها** → `notifications.index` (P7) |
| 5 | Existing badge/counter patterns to reuse? | **None found** |
| 6 | Tests covering unread count behavior? | **Yes** — backend tests in `NotificationInboxTest.php` |
| 7 | Tests proving no badge exists? | **Yes** — P7 negative layout-nav test; inbox architecture guard |
| 8 | Files relevant to future analysis? | `app.blade.php`, `notification-inbox-page.blade.php`, `NotificationInboxPage.php`, `NotificationInboxReadContract.php`, `NotificationPrincipalEmployeeResolver.php`, `NotificationInboxUiFlowTest.php`, P2/P5/P7 contracts and locks |
| 9 | Constraints/risks for feature-analysis? | Placement, refresh, P5/P7 guard supersession, UI Anti-Leak consumption shape |
| 10 | Baseline clear enough for feature-analysis? | **Yes** |
| 11 | Ownership inside Notification module vs presentation leakage? | **Inside Notification Application/Infrastructure**; no presentation ownership today |

---

*This artifact records repository inspection only. It does not approve the feature, decide badge design, or authorize implementation.*
