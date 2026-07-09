# P5 — Notification Mark-Read Mutation — Lock Review

## Feature

- **Feature code:** `notification-mark-read-mutation`
- **Feature title:** P5 — Notification Mark-Read Mutation
- **Domain area:** notifications

## Review date

2026-07-09

## Inputs reviewed

- `docs/ui/locks/notifications/notification-mark-read-mutation.implementation-lock.yaml` (v0.1.0, status: draft) — primary
- `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml` (v0.1.0, status: draft)
- `docs/ui/decisions/notifications/notification-mark-read-mutation.contract-review.md` (verdict: `APPROVED_FOR_LOCK_DRAFTING`)
- `docs/ui/decisions/notifications/notification-mark-read-mutation.review-decision.md`
- `docs/ui/analysis/notifications/notification-mark-read-mutation.feature-analysis.md`
- `docs/ui/analysis/notifications/notification-mark-read-mutation.repo-inspection.md`
- Predecessor context:
  - `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml`
  - `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md`
  - `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md`

## Lock summary

The draft lock authorizes a narrow P5 successor overlay on `NotificationInboxPage`: one per-row mark-read affordance for unread notifications, single `MarkNotificationReadContract::markRead` delegation per user action, post-success full list reload via existing `refreshList` / `NotificationInboxReadContract`, and `HandlesUiMutationFeedback` for mutation error/success surfacing. Allowed surfaces are three files only (Livewire component, Blade view, UI flow test). Backend, routes, layout, backend tests, and P2 governance artifacts remain frozen. P2 architecture guard in `NotificationInboxUiFlowTest` that currently forbids `MarkNotificationReadContract` must be replaced with P5-governed assertions. Affordance form and Persian copy open question from the contract is resolved in the lock (`x-ui.button` secondary, column **عملیات**, label **علامت‌گذاری به‌عنوان خوانده‌شده**).

## Findings

### Contract fidelity

| Contract area | Lock coverage |
|---|---|
| Single-item per-row mark-read | `approved_behavior.rules`, `allowed_actions.mark_notification_read.scope` |
| `MarkNotificationReadContract::markRead` delegation | `application_boundary`, `allowed_actions.mark_notification_read.delegation` |
| Post-success full list reload | `feedback_and_refresh.success.list_reload`, `post_success` |
| `is_read` presentation-only affordance gating | `affordance_gating`, `render_mark_read_affordance` |
| Recipient-scoped auth-only; MPEP deferred v1 | `authorization_and_security` |
| Exclusions (mark-all, bulk, delete/archive/dismiss, API, backend) | `scope.out_of_scope`, `forbidden_actions`, `forbidden_surfaces` |
| P2 supersession bounded to mark-read only | `predecessor_lock_interaction` |
| P2 guard replacement obligation | `test_boundary.required_regression_updates` |
| Affordance open question resolved | `affordance_and_copy` |

The lock faithfully translates the approved contract without behavioral scope expansion.

### Required review questions

| # | Question | Result |
|---|---|---|
| 1 | Lock keeps P5 limited to single-item mark-read from inbox UI? | **Pass** — `mutations` model is single notification per invocation; route pinned to `notifications.index` / `NotificationInboxPage` |
| 2 | Continues to exclude mark-all, bulk, delete/archive/dismiss, API routes, backend/schema changes, new authorization architecture? | **Pass** — explicit in `scope.out_of_scope`, `forbidden_actions`, `forbidden_surfaces` |
| 3 | Allowed file paths correct and sufficient? | **Pass** — see File boundary validation |
| 4 | Blade view path accurate? | **Pass** — correct path is singular `notification/`; see File boundary validation |
| 5 | Direct delegation to `MarkNotificationReadContract::markRead` consistent with application contract patterns? | **Pass** — see Mutation boundary validation |
| 6 | `NotificationPrincipalEmployeeResolver::requireEmployeeId` preserves recipient boundaries without UI policy ownership? | **Pass** — same resolver already used in `refreshList`; lock forbids Gate/role checks |
| 7 | `markNotificationRead(string $notificationId)` appropriately narrow? | **Pass** — one string UUID parameter; does not over-constrain beyond contract |
| 8 | Full `refreshList` reload after success consistent with contract? | **Pass** — matches contract `post_mutation_refresh` and forbids optimistic local authority |
| 9 | Required tests focused and sufficient without scope expansion? | **Pass** — see Test boundary validation |
| 10 | Architecture guard update limited to P5-specific successor exception? | **Pass** — only `NotificationInboxUiFlowTest`; permits contract reference while forbidding persistence smells |
| 11 | Lock avoids editing predecessor P2 contract/lock/closeout artifacts? | **Pass** — listed under `forbidden_surfaces.do_not_modify.predecessor_governance_files` |

## File boundary validation

| Path | Exists | In lock | Sufficient |
|---|---|---|---|
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Yes | Allowed | Yes — mutation handler, trait, post-success reload |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Yes | Allowed | Yes — per-row affordance, error surfacing |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Yes | Allowed | Yes — P5 UI tests + guard replacement |

### Blade path verification

Repository evidence confirms the correct Blade path is:

`resources/views/livewire/notification/notification-inbox-page.blade.php`

(singular `notification`, not `notifications`).

- `NotificationInboxPage::render()` returns `view('livewire.notification.notification-inbox-page')`.
- Glob search finds exactly one inbox Blade file at that path.
- No file exists at `resources/views/livewire/notifications/notification-inbox-page.blade.php`.

The lock uses the correct path. **No revision required.**

## Mutation boundary validation

| Check | Result |
|---|---|
| Livewire → Application Contract invocation allowed | **Pass** — `NotificationInboxPage::refreshList` already injects `NotificationInboxReadContract`. `RequestShowPage` injects `RequestReadContract`. Presentation-layer contract injection is an established repository pattern. |
| Single contract call per action | **Pass** — locked in `allowed_actions.mark_notification_read.constraints` and `anti_leak_boundaries` |
| No direct domain/repository/transaction from UI | **Pass** — explicit `forbidden_in_livewire` and guard replacement retains DB/repository/countUnread prohibitions |
| No new HTTP/API mutation route | **Pass** — routes frozen in `forbidden_surfaces` |
| `readAt` handling | **Pass** — lock requires supplying `readAt` per existing contract signature at the Application boundary without UI business timestamp semantics; execution-time transport is consistent with `MarkNotificationReadContract` signature and backend action behavior |
| Principal resolution not UI policy | **Pass** — `requireEmployeeId` forwards authenticated recipient context; backend `findByIdForRecipient` enforces scoping |
| `HandlesUiMutationFeedback` pattern | **Pass** — matches `RequestCreatePage` precedent cited in lock `references.pattern_authority` |
| UI Anti-Leak compliance | **Pass** — no optimistic `is_read` authority, no orchestration, no authorization mirroring |

**No architectural conflict identified.** Direct Livewire-to-`MarkNotificationReadContract` delegation is consistent with existing inbox read delegation and does not require a lock revision to specify an alternate application boundary.

## Test boundary validation

| Requirement | Lock coverage | Assessment |
|---|---|---|
| Unread row renders affordance with locked Persian label | `test_boundary.required_new_coverage` | Focused |
| Read row hides affordance | same | Focused |
| Successful mark-read shows read state after refresh | same | Focused |
| Delegation to contract with id + employee context | same | Focused |
| Mutation failure surfaced | same | Focused |
| Replace P2 negative guard on `MarkNotificationReadContract` | `required_regression_updates` | Correctly scoped successor exception |
| Retain prohibitions on DB/repository/countUnread | `required_regression_updates` | Preserves P2 anti-leak guard intent |
| No mark-all/API/backend/layout test expansion | `forbidden_test_expansion` | Bounded |

Current guard evidence (`NotificationInboxUiFlowTest` lines 269–285) forbids `MarkNotificationReadContract` among persistence smells. Lock correctly mandates replacing that negative assertion with P5-governed positive/negative guard split.

**Tests are sufficient for P5 scope without expansion.**

## Governance validation

| Check | Result |
|---|---|
| P2 closeout remains CLOSED | Pass — `predecessor_lock_interaction` |
| P2 contract/lock/closeout files not editable | Pass — `forbidden_surfaces.do_not_modify.predecessor_governance_files` |
| P2 supersession limited to mark-read on inbox page | Pass — `superseded_within_p5_scope_only` |
| P2 read-only list obligations preserved except عملیات column | Pass — `not_superseded` |
| Backend tests frozen (`NotificationInboxTest`, etc.) | Pass |
| Contract reference requires approved status | Noted — contract remains `draft`; see Coding authorization |
| Lock status draft / coding unauthorized | Expected pre-review state |

## Risks / concerns

### Non-blocking

1. **`readAt` transport at call site** — Implementer must pass `DateTimeImmutable` to satisfy contract signature. Lock correctly forbids UI business clock semantics; using execution-time UTC at the delegation boundary is acceptable transport, not lifecycle interpretation.

2. **Contract lifecycle** — Feature contract remains `status: draft`. Governance hygiene: advance contract to `approved` concurrent with or before lock promotion, per repository convention (P4 precedent).

3. **Per-row loading UX** — Lock permits but does not mandate `wire:target` scoping detail beyond `affordance_and_copy.form.loading`. Implementer discretion within lock constraints.

4. **`actionError` surfacing in Blade** — Lock references trait pattern; Blade must add alert wiring when trait is adopted. This is implementation detail within allowed surfaces, not a lock gap.

### Blocking

**None.**

## Verdict

**`APPROVED_FOR_IMPLEMENTATION`**

The lock is narrow, enforceable, contract-faithful, and architecture-compliant. Blade path is correct. Mutation boundary matches established Livewire-to-Application-Contract patterns. P2 supersession is bounded. No scope leak or governance conflict was identified.

## Required revisions

**None.**

No lock revision is required before implementation authorization.

## Coding authorization decision

**The lock may be promoted to:**

- `status: approved`
- `coding_authorized: true`

**Preconditions before coding starts:**

1. Advance P5 feature contract `notification-mark-read-mutation.feature-contract.yaml` from `draft` to `approved` (governance hygiene; contract review already returned `APPROVED_FOR_LOCK_DRAFTING`).
2. Update this implementation lock status and `coding_authorized` as above after accepting this lock review.

Until both are recorded, coding must remain unauthorized per lock `authorization_notice`.

## Next step

1. Accept this lock review in the governance workflow.
2. Promote feature contract status to `approved`.
3. Promote implementation lock to `status: approved` and `coding_authorized: true`.
4. Implement P5 strictly within the three allowed surfaces and governed actions defined in the lock.
5. Run focused `NotificationInboxUiFlowTest` and verify no unauthorized file changes before closeout.

## Rationale

1. **Contract translation is complete.** Every contract obligation—single-item mutation, backend delegation, full reload, presentation-only `is_read` gating, exclusions, P2 supersession, and test guard replacement—is pinned in the lock without reopening behavioral scope.

2. **File boundaries are verified against the repository.** All three allowed paths exist. The Blade path uses singular `notification/`, matching `NotificationInboxPage::render()` and the on-disk view file. The incorrect plural `notifications/` path does not exist.

3. **Mutation boundary is architecture-safe.** `NotificationInboxPage` already delegates reads to `NotificationInboxReadContract` via Livewire method injection. Adding `MarkNotificationReadContract` for a single write per action follows the same Presentation → Application boundary. The lock forbids repository access, transactions, orchestration, and UI authorization mirroring—consistent with UI Anti-Leak and P2 guard intent.

4. **P2 governance remains closed.** Supersession applies only to mark-read prohibition and the negative architecture guard. P2 contract, lock, and closeout artifacts are explicitly frozen.

5. **Tests are appropriately bounded.** Required coverage exercises affordance visibility, successful refresh, delegation, and failure surfacing. Guard replacement is a targeted successor exception, not a broad test expansion.

6. **Affordance open question is resolved without scope creep.** Secondary per-row button, Persian label, and **عملیات** column are pinned without introducing mark-all, bulk, or management affordances.
