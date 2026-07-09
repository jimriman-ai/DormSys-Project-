# P5 — Notification Mark-Read Mutation — Reconciliation Note

## Feature

- **Feature code:** `notification-mark-read-mutation`
- **Feature title:** P5 — Notification Mark-Read Mutation
- **Domain area:** notifications
- **Classification:** successor-feature (P2 inbox overlay)

## Final status

**IMPLEMENTED_RECONCILED**

## Governance path completed

The full DormSys UI governance path for P5 was completed end-to-end:

| Stage | Artifact | Outcome |
|---|---|---|
| Repository inspection | `docs/ui/analysis/notifications/notification-mark-read-mutation.repo-inspection.md` | Backend mark-read exists; UI mutation gap identified |
| Feature analysis | `docs/ui/analysis/notifications/notification-mark-read-mutation.feature-analysis.md` | `UI_BEHAVIOR_GAP` / `UI_ONLY_GAP` |
| Review decision | `docs/ui/decisions/notifications/notification-mark-read-mutation.review-decision.md` | `CONTRACT_REQUIRED_BEFORE_IMPLEMENTATION` |
| Feature contract | `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml` | Drafted and reviewed |
| Contract review | `docs/ui/decisions/notifications/notification-mark-read-mutation.contract-review.md` | `APPROVED_FOR_LOCK_DRAFTING` |
| Implementation lock | `docs/ui/locks/notifications/notification-mark-read-mutation.implementation-lock.yaml` | Drafted with narrow file boundary |
| Lock review | `docs/ui/decisions/notifications/notification-mark-read-mutation.lock-review.md` | `APPROVED_FOR_IMPLEMENTATION` |
| Implementation | Three authorized surfaces only | Completed |
| Verification | `docs/ui/verification/notifications/notification-mark-read-mutation.verification.md` | `VERIFIED` |
| Closeout | This reconciliation | `IMPLEMENTED_RECONCILED` |

No governance stage was skipped. No scope expansion was authorized or delivered beyond the approved P5 successor overlay.

## Delivered behavior

P5 adds governed single-item mark-read to the existing `NotificationInboxPage` inbox surface on route `notifications.index`:

- **Single-item mutation** — `markNotificationRead(string $notificationId)` marks at most one notification per user action.
- **Unread-only affordance** — Per-row secondary button (**علامت‌گذاری به‌عنوان خوانده‌شده**) in column **عملیات**, rendered only when `is_read` is false (presentation gating, not authorization).
- **Application contract delegation** — One call to `MarkNotificationReadContract::markRead` per action.
- **Employee context** — `NotificationPrincipalEmployeeResolver::requireEmployeeId()` forwards authenticated recipient context; no UI policy or ownership logic.
- **Authoritative refresh** — On success, existing `refreshList` reloads inbox rows from `NotificationInboxReadContract`; no optimistic local `is_read` mutation as source of truth.
- **Mutation feedback** — `HandlesUiMutationFeedback` surfaces `actionError` on failure and optional success flash on success.
- **Thin UI preserved** — No repository access, transactions, Gate checks, or orchestration in Livewire.

## Files changed

Implementation touched exactly three authorized files:

| File | Change |
|---|---|
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | `markNotificationRead` action, trait adoption, contract delegation, post-success refresh |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | **عملیات** column, per-row mark-read button, `actionError` alert |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Five P5 UI tests, P2 architecture guard successor update |

No backend, domain, infrastructure, route, layout, migration, or P2 governance artifact files were modified.

## Verification summary

| Field | Value |
|---|---|
| Verification artifact | `docs/ui/verification/notifications/notification-mark-read-mutation.verification.md` |
| Verdict | `VERIFIED` |
| Implementation boundary | Compliant — 3 files only |
| Contract alignment | Compliant |
| Lock alignment | Compliant |
| Deviations | None |
| Blockers | None |

### Test evidence

```text
php artisan test tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php --ansi
```

| Metric | Result |
|---|---|
| Tests | 18 passed |
| Assertions | 53 |
| Exit code | 0 |

P5-specific coverage includes: unread affordance visibility, read-row affordance absence, successful mark-read with post-refresh state, contract delegation with employee context, failure via `actionError`, and updated architecture guard (persistence smells blocked; governed `MarkNotificationReadContract` permitted).

## Scope compliance

### In scope (delivered)

- Single-item per-row mark-read from inbox UI
- Delegation through existing `MarkNotificationReadContract`
- Full list reload after successful mutation
- Presentation-only `is_read` affordance gating
- Recipient-scoped auth-only (MPEP gate deferred for v1 per contract)
- P5 architecture guard successor exception in UI flow tests

### Out of scope (not introduced)

- mark-all-as-read
- bulk/batch selection or mutation
- delete, archive, dismiss, or restore actions
- notification preferences or settings
- countUnread, badge, or unread counter UI
- deep-link navigation, detail view, or row-click navigation
- filtering, search, sorting, pagination, or realtime refresh
- new HTTP or API mutation routes
- backend contract, DTO, domain, migration, or schema changes
- `NotificationMutationAuthorizationGate` or new Gate/policy model
- layout navigation link to inbox
- optimistic local `is_read` authority without backend refresh

Thin UI / Anti-Leak compliance was preserved. No prohibited scope was introduced.

## Successor relationship to P2

P2 — Notification Inbox (Read-Only) remains **CLOSED** per `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md`.

P5 is a **narrow successor overlay** on the existing P2 inbox surface. It supersedes only:

- The P2 prohibition on mark-read UI actions on `NotificationInboxPage`
- The P2 architecture guard negative assertion forbidding `MarkNotificationReadContract` in the inbox Livewire page

P5 does **not**:

- Reopen or amend the P2 closeout, contract, or lock artifacts
- Expand P2 into general notification management
- Change P2 read-only list obligations beyond adding the **عملیات** column and per-row mark-read affordance
- Alter P2 backend freeze or backend test boundaries

P2 and P5 coexist: the inbox remains a list surface with governed single-item mark-read as an approved successor exception.

## Notes

Non-blocking items recorded for audit completeness; none weaken the final verdict:

1. **`readAt` transport** — UI supplies UTC execution-time `DateTimeImmutable` at the contract boundary. Lock review accepted this as transport, not business timestamp semantics.

2. **Success flash** — `flashSuccess` is implemented per lock. Tests assert authoritative post-refresh row state rather than session flash directly; this is sufficient per verification.

3. **Governance YAML hygiene** — Contract and lock YAML statuses (`draft` / `coding_authorized: false`) may require repository consistency review if not promoted to `approved` / `coding_authorized: true` during the governance workflow. This is administrative hygiene, not an implementation defect.

4. **Broader CI** — Full suite (`composer run arch`, `composer run phpstan`, full test run) remains a PR-time Definition of Done obligation; not required to alter this closeout verdict.

## Final verdict

**IMPLEMENTED_RECONCILED**

P5 — Notification Mark-Read Mutation is implemented, verified, and reconciled within approved contract and lock boundaries. The feature is closed for the approved MVF scope.

## Follow-up actions

| Action | Required | Notes |
|---|---|---|
| P5 feature closeout | Complete | This artifact |
| Promote contract/lock YAML to `approved` if still `draft` | Optional hygiene | Repository governance consistency |
| PR merge with CI (`arch`, `phpstan`, full tests) | At PR time | Standard Definition of Done |
| mark-all, bulk, delete/archive/dismiss, badge, nav | Not in scope | Require separate governance cycle if needed later |

No follow-up implementation is required for the approved P5 scope.

## Rationale

1. **Governance path was complete and followed.** Inspection through closeout produced explicit artifacts at each stage; lock review authorized implementation; verification confirmed compliance.

2. **Implementation matched authorized boundaries exactly.** Three files only; behavior pins from contract and lock were delivered without deviation.

3. **Verification passed with evidence.** Focused UI test suite (18/18) covers P5 behavior, delegation, failure surfacing, and architecture guard successor exception.

4. **P2 remains closed and unchanged.** P5 supersession is bounded to mark-read on the inbox page only; no P2 artifact edits and no general notification-management expansion.

5. **Thin UI / backend authority preserved.** Mutation delegates to one Application contract; display reloads from read contract; UI does not own authorization, capability, or optimistic state authority.

6. **No blockers remain.** Residual notes are non-blocking hygiene or out-of-scope future work.
