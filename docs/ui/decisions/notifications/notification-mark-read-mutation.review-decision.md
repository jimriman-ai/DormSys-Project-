# P5 — Notification Mark-Read Mutation — Review Decision

## Feature

- **Feature code:** `notification-mark-read-mutation`
- **Feature title:** P5 — Notification Mark-Read Mutation
- **Domain area:** notifications

## Review date

2026-07-09

## Inputs reviewed

- `docs/ui/analysis/notifications/notification-mark-read-mutation.feature-analysis.md` (primary)
- `docs/ui/analysis/notifications/notification-mark-read-mutation.repo-inspection.md`
- `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md`
- `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` (mutation exclusions)
- `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` (mark-read forbidden changes)

## Decision summary

**Verdict:** `CONTRACT_REQUIRED_BEFORE_IMPLEMENTATION`

P5 is a **UI behavior gap** atop an **already-sufficient backend mark-read capability**. Implementation is **not authorized** under current governance. P2 closed the inbox as read-only and explicitly excluded mark-read mutation UI. No P5 contract, lock, or approved scope boundary exists. Unresolved scope and interaction questions must be locked in a P5 feature contract before implementation or lock drafting.

| Item | Disposition |
|---|---|
| Direct implementation | **Not authorized** |
| Feature contract | **Required before implementation** |
| Implementation lock | Required after contract (not in this step) |
| P2 reopening | **Not required** — P5 proceeds as separate feature |

## Key findings

### Review question 1 — Is this primarily a `UI_BEHAVIOR_GAP`?

**Yes.** Inspection and feature analysis are consistent:

- Backend: `MarkNotificationReadContract`, `MarkNotificationReadAction`, domain `markRead`, `read_at` persistence, backend tests present
- UI: inbox exists; read/unread labels render; **no** mark-read trigger, Livewire mutation method, or UI tests
- Classification `UI_BEHAVIOR_GAP` / analysis verdict `UI_ONLY_GAP` is supported by repository evidence

### Review question 2 — Is backend mark-read capability sufficient for the baseline?

**Yes, for per-notification mark-read.** Inspection confirms:

- Callable Application action with recipient-scoped enforcement
- Persistence and projection read-back (`isRead`, `readAt`) after mutation
- Feature tests: mark read, unread filter/count, cross-recipient denial
- Spec09 tasks T017/T019 marked complete

No inspected evidence shows that schema, repository, or `MarkNotificationReadContract` changes are prerequisites for wiring inbox UI to the existing action. A **new HTTP/API endpoint** is not evidenced as required; Livewire delegation to the existing Application contract is the established mutation pattern (`RequestCreatePage` precedent).

**Caveat (contract-level, not backend insufficiency):** `MarkNotificationReadAction` is in `PendingMutationAuthorizationRegistry`. Recipient-scoped lookup is implemented; formal MPEP gate adoption is pending. This is an **authorization stance** question for the contract, not proof that the write model is missing.

### Review question 3 — Does P2 require P5 as separately governed?

**Yes.**

- P2 closeout: `IMPLEMENTED_RECONCILED` / `CLOSED`; scope not added includes mutation actions and mark-as-read
- P2 contract: forbids UI consumption of `MarkNotificationReadContract`; no mutation controls
- P2 lock: mark-read / mark-all-as-read out of scope and forbidden
- P2 UI test: architecture guard **requires** `NotificationInboxPage.php` not contain `MarkNotificationReadContract`

P5 cannot be treated as implicit approval or a silent extension of P2. It requires its own governance path.

### Review question 4 — Are unresolved scope questions significant enough to require a contract?

**Yes.**

The analysis identifies open items that are **scope and boundary decisions**, not implementation-time details:

| Open topic | Why contract-level |
|---|---|
| Per-row mark-read only vs. mark-all-as-read | P2 explicitly excluded mark-all; P5 title implies single-item mutation but contract must state boundary |
| Action affordance shape | Button, row action, column control — interaction model undecided |
| Capability flag (`can_mark_read`) vs. `is_read`-derived UI | UI Anti-Leak prefers backend capability; DTO has no flag today — contract must decide in-scope vs. deferred |
| Post-mutation refresh behavior | List refresh vs. optimistic row update — UX contract matter |
| Error surfacing | Whether `HandlesUiMutationFeedback` pattern applies — presentation contract matter |
| Test/guard obligation changes | P2 guard forbids contract reference; P5 must explicitly authorize reversal and define new test obligations |

These are sufficient to require a contract before coding.

### Review question 5 — Do mutation-wiring constraints block implementation until scope is locked?

**Yes.**

Active repository constraints:

1. **P2 implementation lock** forbids mark-read actions and `MarkNotificationReadContract` usage in presentation scope
2. **`NotificationInboxUiFlowTest` architecture guard** asserts `NotificationInboxPage.php` must not contain `MarkNotificationReadContract`
3. **P2 feature contract** forbids mutation controls on the inbox list

Implementing mark-read without a P5 contract would conflict with frozen P2 artifacts and fail existing tests. Scope, permitted presentation changes, and test obligation updates must be explicitly governed first.

## Scope considerations

| Scope item | Review disposition |
|---|---|
| **Per-row mark-read** | **Presumed in-scope candidate** for P5 (aligns with feature title, spec09 FR-002 user story, existing `markRead(notificationId, …)` signature). Must be **explicitly confirmed** in contract — not assumed from title alone. |
| **Mark-all-as-read** | **Out of scope unless contract explicitly adds it.** P2 contract and lock list mark-all-as-read as excluded. No backend batch contract inspected. Default: **exclude** from P5 v1 contract unless deliberately reopened. |
| **Additional HTTP/API endpoint** | **Not required by inspection evidence.** Existing `MarkNotificationReadContract` is sufficient for Livewire delegation on `notifications.index`. Contract should state: presentation consumes Application contract via Livewire; no new route required unless contract deliberately adds API surface. |
| **Backend contract/schema changes** | **Not required for baseline** per inspection. Contract should forbid unauthorized expansion of `MarkNotificationReadContract`, repository, or schema unless a future amendment proves necessity. |
| **Layout nav to inbox** | **Out of scope for P5** (deferred P2 item; discoverability, not mark-read mutation). |
| **countUnread / badge** | **Out of scope for P5** (excluded in P2; separate feature if needed). |

## Governance considerations

### P2 conflict assessment

**P5 conflicts with P2 frozen presentation boundaries if implemented without new governance.**

| Artifact | Conflict |
|---|---|
| P2 closeout | Mutation not approved in reconciled baseline |
| P2 contract | Mark-read forbidden; no mutation controls |
| P2 lock | Mark-read actions forbidden |
| P2 UI architecture test | Negative assertion on `MarkNotificationReadContract` in inbox page |

P5 contract must define how P5 scope relates to P2 (separate feature overlay on same inbox surface) without reopening P2 closeout.

### Authorization stance

Contract must capture one of:

- **Recipient-scoped auth-only** (current `findByIdForRecipient` enforcement; authenticated employee marks own notifications), or
- **Explicit deferral** of formal `NotificationMutationAuthorizationGate` / MPEP resolution with documented acceptance of `PendingMutationAuthorizationRegistry` pending status

This must not be left to implementer discretion.

### Test and guard updates

Contract must require:

- Replacement or revision of P2 read-only architecture guard expectations for governed mutation wiring
- New UI tests: mark-read trigger, post-mutation read/unread display, error propagation
- Preservation of backend tests (`NotificationInboxTest` etc.) without unauthorized modification scope

Prior governance approval of these test obligation changes is part of contract → lock chain, not ad hoc at implementation time.

### UI Anti-Leak alignment

Contract should address whether action availability is:

- derived from existing `is_read` for display-only gating (show action on unread rows only), or
- delivered via explicit backend capability field (preferred by governing contract; may require DTO mapping decision)

Contract must pick a governed stance; implementation must not infer capability authority in UI.

## Verdict

**`CONTRACT_REQUIRED_BEFORE_IMPLEMENTATION`**

Implementation is **not authorized**. A P5 feature contract must be drafted and approved before implementation lock or coding.

`APPROVED_FOR_IMPLEMENTATION` is **not** supported: no P5 scope artifact exists; P2 explicitly forbids the target behavior; active test guards block mutation wiring; multiple interaction and authorization questions remain open.

`INSUFFICIENT_CLARITY_FOR_IMPLEMENTATION` is **not** supported: inspection and analysis provide sufficient evidence to classify the gap and determine that contract is the correct gate. Ambiguity affects **contract content**, not whether a contract is needed.

## Required next step

**Draft P5 feature contract**

Create `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml` (or equivalent governed artifact) defining at minimum:

1. In-scope mutation: per-row mark-read on `NotificationInboxPage` via `MarkNotificationReadContract`
2. Explicit exclusions: mark-all-as-read, new API route (unless deliberately added), backend contract/schema changes, countUnread/badge, layout nav
3. Authorization stance (recipient-scoped auth-only vs. MPEP gate requirement)
4. Capability / action-availability rules (`is_read` gating vs. `can_mark_read`)
5. Interaction model boundaries (affordance type, post-mutation refresh, error feedback pattern)
6. Relationship to P2 frozen artifacts and required test/guard obligation updates
7. Forbidden changes aligned with inspection evidence (no repository/orchestration leakage in Livewire)

After contract approval: proceed to implementation lock, then implementation.

Do **not** implement code in this review step.

## Rationale

1. **Evidence supports UI-only gap with ready backend.** The correct delivery path is presentation wiring to existing Application capability — but that path is **currently forbidden** by P2 governance and enforced by tests.

2. **P2 closeout deferred mutation deliberately.** Closeout states mark-as-read requires separate approval. P5 is that separate feature; approval must be explicit, not inferred from backend existence.

3. **Scope questions are material and unresolved.** Per-row vs. mark-all, capability delivery, authorization stance, and guard/test changes are contract-level decisions identified in feature analysis. Implementing without locking them risks scope creep and P2 boundary violation.

4. **Governance-safe default.** When backend exists but UI behavior, boundaries, and mutation interaction pattern are not yet governed — and predecessor artifacts forbid the behavior — `CONTRACT_REQUIRED_BEFORE_IMPLEMENTATION` is the correct verdict per DormSys workflow guidance.

5. **Contract precedes lock and code.** Sufficient clarity exists to mandate contract drafting; insufficient **governance authorization** exists to permit implementation today.
