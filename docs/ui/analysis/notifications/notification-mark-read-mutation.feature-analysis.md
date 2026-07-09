# P5 — Notification Mark-Read Mutation — Feature Analysis

## Feature

- **Feature code:** `notification-mark-read-mutation`
- **Feature title:** P5 — Notification Mark-Read Mutation
- **Domain area:** notifications

## Analysis date

2026-07-09

## Inputs reviewed

- `docs/ui/analysis/notifications/notification-mark-read-mutation.repo-inspection.md` (primary)
- `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md`
- `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` (excerpt: out-of-scope / forbidden mutation clauses)
- `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` (excerpt: mark-read exclusions and forbidden changes)

## Current baseline

### Backend mark-read capability — implemented

Repository inspection confirms a complete Application-layer mutation path:

- `MarkNotificationReadContract` and `MarkNotificationReadAction`
- `Notification::markRead()` (idempotent when already read)
- `NotificationRepository` persistence of `read_at` on `notification_logs`
- Service binding in `NotificationServiceProvider`
- Backend feature tests in `NotificationInboxTest.php` (mark read, unread filter/count after mark, cross-recipient denial)

Spec09 (`specs/009-notification-delivery/tasks.md`) records mark-read action and tests as complete.

### Notification inbox UI — implemented (P2 read-only baseline)

- `GET /notifications` → `NotificationInboxPage` (`notifications.index`)
- `notification-inbox-page.blade.php` table rendering
- `NotificationPrincipalEmployeeResolver` for employee context on list load
- `NotificationInboxUiFlowTest` for access, list states, and read-only architecture guard

### Read/unread display — implemented

- `NotificationInboxPage::mapProjectionRow()` maps `is_read` from `NotificationProjectionDto`
- Blade renders Persian labels: `خوانده شده` / `خوانده نشده`
- UI test asserts `خوانده نشده` for unread delivered items

## Confirmed gap

The missing feature surface is **user-triggered mark-read behavior from the inbox**, not backend persistence or inbox listing.

| Surface | Status |
|---|---|
| Mark-read UI trigger (button, row action, link) | **Absent** |
| Livewire mutation method delegating to `MarkNotificationReadContract` | **Absent** |
| Presentation-layer path from inbox to mark-read action | **Absent** |
| HTTP/API mark-read endpoint | **Absent** |
| UI tests invoking mark-read and asserting post-mutation display | **Absent** |

What exists today is **display-only** read/unread state. Users cannot change read state through the inbox UI.

Secondary note (not the primary P5 gap): layout navigation to the inbox (`notifications.index`) remains absent from `app.blade.php` header nav (deferred in P2). Direct URL `/notifications` is reachable. This is inbox discoverability, not mark-read mutation behavior.

## Governance context

### P2 closed as read-only

`notification-inbox-read-only.reconciliation.md` records:

- Status: `IMPLEMENTED_RECONCILED` / `CLOSED`
- Reconciled scope: authenticated inbox list, read-only rendering, existing backend read support
- **Scope not added:** notification mutation actions; mark-as-read / mark persistence design; new authorization model
- Closeout states these require **separate approval** if later needed

### Mark-read explicitly excluded from P2

P2 contract and implementation lock both record:

- `MarkNotificationReadContract` out of scope / forbidden for P2 UI consumption
- No mark-read controls, mark-all-as-read, or mutation actions in the read-only feature
- P2 UI architecture test **requires** `NotificationInboxPage.php` not to contain `MarkNotificationReadContract`

### P5 must be treated as a separate feature

Evidence supports treating P5 as a **new governance path**, not an extension of the closed P2 baseline:

- P2 is closed without mutation UI approval
- Backend mark-read exists from spec09 but was intentionally withheld from P2 presentation scope
- No P5 contract, lock, decision, or closeout artifacts exist yet

P5 implementation will require revisiting presentation constraints that P2 deliberately froze, under P5-specific governance — not by reopening P2.

## Noted constraints

Record for later governance decisions; not solution design.

1. **Read-only architecture guard (active today).** `NotificationInboxUiFlowTest` asserts `NotificationInboxPage.php` must not contain `MarkNotificationReadContract`. Any P5 inbox mutation wiring will conflict with this test until P5 governance authorizes the change and updates test obligations.

2. **Thin-UI / single-delegation pattern.** Existing mutation precedent (`RequestCreatePage`) delegates to one Application action via method injection; uses `HandlesUiMutationFeedback` for error surfacing. Inbox page does not use this trait today.

3. **Recipient-scoped authorization only.** `MarkNotificationReadAction` enforces ownership via `findByIdForRecipient`; no Notification-specific Gate or `*MutationAuthorizationGate` exists. `MarkNotificationReadAction` is listed in `PendingMutationAuthorizationRegistry` (MPEP adoption pending). Inspection does not prove a new gate is required for initial UI wiring, but review should confirm whether recipient-scoped auth-only is sufficient for P5.

4. **P2 lock forbids backend contract changes in read-only scope.** P5 should not be interpreted as permission to alter `MarkNotificationReadContract`, repository, or schema — inspection shows those are already sufficient; the gap is presentation consumption.

5. **Row `id` mapped but not actionable.** `mapProjectionRow()` includes notification `id`; Blade does not expose it in controls. Constraint for later contract: mutation trigger needs notification identifier transport without UI-side business interpretation.

6. **No `can_mark_read` capability flag on projection DTO today.** UI Anti-Leak guidance prefers backend-provided capability where action availability matters. Inspection does not prove DTO expansion is required (unread rows could show action; read rows could omit it), but this is an open governance topic for review-decision, not pre-decided here.

## Gap classification

**`UI_BEHAVIOR_GAP`**

Backend mark-read mutation capability is implemented and tested. The inbox UI exists and displays read/unread state. The gap is the absence of user-facing mutation interaction: no trigger, no presentation delegation, no UI regression tests.

Not `UI_DISCOVERABILITY_GAP` — the primary missing behavior is mark-read action on the inbox, not discovery of the inbox entrypoint (though nav link absence is a separate deferred item).

Not `MIXED_UI_AND_BACKEND_GAP` — inspection does not identify missing backend write-model work required to enable per-notification mark-read; Application action, domain, persistence, and backend tests are present.

Not `NO_GAP_CONFIRMED` or `INSUFFICIENT_EVIDENCE` — repository evidence is consistent and sufficient.

## Analysis verdict

**`UI_ONLY_GAP`**

P5 missing work is concentrated in the presentation layer: user-triggered mark-read from the existing inbox surface, with supporting UI tests and updated architecture-guard expectations. Backend write-model capability for mark-read is already in repository and does not appear to be the blocking gap for this feature.

`MIXED_UI_AND_WRITE_MODEL_GAP` is not supported because inspection confirms an operational `MarkNotificationReadContract` path with persistence and feature tests, without evidence that contract, schema, or repository changes are prerequisites.

## Recommended next step

**Review decision**

Proceed to `docs/ui/decisions/notifications/notification-mark-read-mutation.review-decision.md` (not yet present) to establish:

- disposition on whether a P5 feature contract is required before implementation
- confirmed in-scope mutation surface (e.g., per-row mark-read on inbox only vs. mark-all-as-read, which P2 explicitly excluded)
- authorization stance for P5 (recipient-scoped auth-only vs. formal MPEP gate resolution)
- relationship to P2 frozen boundaries and required test/guard updates
- whether capability-flag delivery (`can_mark_read`) is in scope or deferred

Do not draft contract, lock, or implementation in this analysis step.

## Rationale

1. **Inspection is unambiguous on what exists vs. what is missing.** Backend mark-read is complete; inbox UI is read-only by design under P2; no presentation mutation path or UI tests exist.

2. **P2 governance deliberately deferred mutation.** Closeout, contract, and lock all exclude mark-read from the reconciled baseline. P5 requires its own approval path rather than implicit scope expansion.

3. **UI-focused gap aligns with spec09 completion.** Spec09 delivered the write model; the employee-facing FR-002 mark-read user story (recipient marks notification read) is not fulfilled at the UI layer.

4. **Active constraints require governance resolution before coding.** The P2 architecture guard forbids `MarkNotificationReadContract` in `NotificationInboxPage`; implementing mark-read without review-decision would conflict with enforced test expectations and frozen P2 lock language.

5. **Review-decision is the correct gate.** Evidence is sufficient to classify the gap; open topics (contract necessity, action affordance shape, capability flags, MPEP pending status) are disposition questions for review, not feature-analysis conclusions. Contract drafting, if required, follows review-decision — not this document.
