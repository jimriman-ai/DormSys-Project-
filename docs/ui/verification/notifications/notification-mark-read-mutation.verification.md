# P5 — Notification Mark-Read Mutation — Implementation Verification

## Feature

- **Feature code:** `notification-mark-read-mutation`
- **Feature title:** P5 — Notification Mark-Read Mutation
- **Domain area:** notifications

## Verification date

2026-07-09

## Inputs reviewed

- `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml` (v0.1.0)
- `docs/ui/locks/notifications/notification-mark-read-mutation.implementation-lock.yaml` (v0.1.0)
- `docs/ui/decisions/notifications/notification-mark-read-mutation.lock-review.md` (verdict: `APPROVED_FOR_IMPLEMENTATION`)
- Supporting context:
  - `docs/ui/analysis/notifications/notification-mark-read-mutation.repo-inspection.md`
  - `docs/ui/decisions/notifications/notification-mark-read-mutation.contract-review.md`
  - `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md`

## Authorized implementation boundary

Per approved lock `allowed_surfaces`, implementation was authorized only in:

| Path | Role |
|---|---|
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Livewire mutation handler |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Per-row affordance + error surfacing |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | P5 UI tests + architecture guard update |

## Observed implementation summary

### Files changed (implementation)

`git diff --name-only HEAD` reports exactly three modified implementation files:

1. `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php`
2. `resources/views/livewire/notification/notification-inbox-page.blade.php`
3. `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php`

No backend, route, layout, migration, or P2 governance artifact modifications were observed in the implementation diff.

### Livewire (`NotificationInboxPage.php`)

- Adopts `HandlesUiMutationFeedback` trait.
- Adds `markNotificationRead(string $notificationId)` with method-injected dependencies:
  - `MarkNotificationReadContract`
  - `NotificationInboxReadContract`
  - `NotificationPrincipalEmployeeResolver`
- On invocation: resets feedback → single `markRead(notificationId, requireEmployeeId(), readAt)` call → on success `flashSuccess` + `refreshList` → on failure `captureMutationFailure`.
- No `DB::`, repository, `countUnread`, Gate, or role/permission usage present.
- Existing `refreshList` read path unchanged.

### Blade (`notification-inbox-page.blade.php`)

- Adds global `actionError` alert below page header.
- Adds **عملیات** table column.
- Renders per-row secondary `x-ui.button` with label **علامت‌گذاری به‌عنوان خوانده‌شده** only when `! $notification['is_read']`.
- `wire:click="markNotificationRead('{{ $notification['id'] }}')"` with `wire:target="markNotificationRead"` loading state.
- Blade path matches lock: `resources/views/livewire/notification/notification-inbox-page.blade.php` (singular `notification`).

### Tests (`NotificationInboxUiFlowTest.php`)

- Adds `notification inbox mark-read mutation` describe block (5 tests).
- Updates architecture guard: removes `MarkNotificationReadContract` from forbidden needles; adds positive P5 delegation assertion.
- Retains prohibitions on `DB::transaction`, `DB::table`, `NotificationRepositoryContract`, `countUnread`.

## Contract compliance check

| Contract requirement | Observed | Result |
|---|---|---|
| Single-item per-row mark-read on `NotificationInboxPage` | `markNotificationRead` accepts one `notificationId`; one contract call per action | Pass |
| Delegate to `MarkNotificationReadContract::markRead` | Injected contract; single `markRead` invocation | Pass |
| Post-success full list reload | `refreshList($inbox, $principalEmployee)` after successful mutation | Pass |
| No optimistic local `is_read` authority | Row state updated only via `refreshList` reload from read contract | Pass |
| `is_read` affordance gating (presentation only) | Blade `@if (! $notification['is_read'])` branch | Pass |
| Recipient context via principal resolver | `requireEmployeeId()` forwarded to `markRead` | Pass |
| No UI policy/ownership logic | No Gate, role, or permission checks added | Pass |
| Exclude mark-all, bulk, delete/archive/dismiss | No such actions, routes, or affordances | Pass |
| No backend/schema/API expansion | No backend files in implementation diff | Pass |
| P2 successor scope only | Mark-read overlay on existing inbox; P2 columns preserved | Pass |

## Lock compliance check

| Lock pin | Observed | Result |
|---|---|---|
| Livewire method name `markNotificationRead` | Matches | Pass |
| Contract path `MarkNotificationReadContract::markRead` | Matches | Pass |
| `HandlesUiMutationFeedback` pattern | Trait used; `actionError` surfaced in Blade | Pass |
| Affordance: `x-ui.button` secondary, column **عملیات** | Matches | Pass |
| Label: **علامت‌گذاری به‌عنوان خوانده‌شده** | Matches | Pass |
| Success message FA copy | `flashSuccess('اعلان به‌عنوان خوانده‌شده علامت‌گذاری شد.')` | Pass |
| Forbidden: DB/repository/transaction/countUnread in Livewire | Absent; guard tests enforce | Pass |
| Forbidden: new routes/controllers/backend changes | Not in diff | Pass |
| Architecture guard replacement scoped to P5 | Guard updated in `NotificationInboxUiFlowTest` only | Pass |
| P2 artifacts not edited | Not in diff | Pass |
| Allowed files only | Exactly 3 implementation files modified | Pass |

## Test evidence

### Command executed

```text
php artisan test tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php --ansi
```

### Result (2026-07-09)

| Metric | Value |
|---|---|
| Tests | 18 |
| Passed | 18 |
| Failed | 0 |
| Assertions | 53 |
| Duration | ~6.4s |
| Exit code | 0 |

### P5-specific coverage exercised

| Lock-required coverage | Test |
|---|---|
| Unread row renders mark-read affordance with locked Persian label | `renders the mark-read affordance for unread inbox rows` |
| Read row does not render affordance | `does not render the mark-read affordance for read inbox rows` |
| Successful mark-read updates rendered state after refresh | `marks a notification as read through the ui and refreshes rendered state` |
| Delegation to `MarkNotificationReadContract` with id + employee context | `delegates mark-read to MarkNotificationReadContract with notification and employee context` |
| Mutation failure surfaces via `actionError` | `surfaces mark-read mutation failures through actionError` |
| Architecture guard: persistence smells blocked | `keeps the livewire inbox page free of persistence orchestration smells` |
| Architecture guard: P5 contract delegation permitted | `permits governed MarkNotificationReadContract delegation for P5 mark-read` |

### Regression coverage retained

Pre-P5 inbox tests (access, states, principal resolution, list limit, error surfacing) remain in the same file and passed as part of the 18-test run.

## Prohibited-scope check

| Prohibited item | Observed in implementation | Result |
|---|---|---|
| mark-all-as-read | Absent | Pass |
| Bulk/batch selection or mutation | Absent | Pass |
| delete / archive / dismiss / restore | Absent | Pass |
| New HTTP/API mutation route | Absent | Pass |
| Backend contract/DTO/domain/schema changes | Not in diff | Pass |
| `NotificationMutationAuthorizationGate` or new Gate/policy | Absent | Pass |
| Layout nav link / badge / countUnread UI | Absent | Pass |
| Filter/sort/pagination/realtime redesign | Absent | Pass |
| P2 contract/lock/closeout edits | Not in diff | Pass |
| Optimistic local `is_read` without reload | Absent | Pass |

## Risks / notes

### Non-blocking

1. **`readAt` transport** — UI supplies `new DateTimeImmutable('now', new DateTimeZone('UTC'))` at the contract boundary. Lock review accepted this as execution-time transport, not business timestamp semantics. Compliant with lock intent.

2. **Success flash not explicitly asserted** — `flashSuccess` is implemented per lock; lock treats success message as informational and not a substitute for list reload. Session flash is not covered by a dedicated assertion; list-refresh assertion provides authoritative state evidence.

3. **Governance artifact lifecycle** — Contract and lock YAML remain `status: draft` / `coding_authorized: false` in the working tree at verification time. This is a governance hygiene item for closeout, not an implementation compliance defect.

### Blocking

None.

## Verdict

**VERIFIED**

Implementation matches the approved P5 contract and implementation lock. Changes are confined to the three authorized surfaces. Focused test evidence passes with full P5 coverage and updated architecture guard. No prohibited scope or Thin UI / Anti-Leak violations were identified.

## Verification status

| Field | Value |
|---|---|
| Implementation boundary | Compliant — 3 files only |
| Contract alignment | Compliant |
| Lock alignment | Compliant |
| Test evidence | Sufficient — 18/18 passed |
| Prohibited scope | None introduced |
| Architecture (Thin UI / Anti-Leak) | Compliant |
| Deviations | None |
| Blockers | None |

## Recommended next step

Proceed to **P5 governance closeout** (reconciliation artifact) after:

1. Promoting contract and lock statuses to `approved` / `coding_authorized: true` if not already recorded in governance workflow.
2. Optional: run broader CI suites (`composer run arch`, `composer run phpstan`, full test suite) at PR time per repository Definition of Done.

Do not reopen P2 closeout or expand P5 scope during closeout.
