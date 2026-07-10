# Notification Inbox Unread Badge — Feature Analysis

## Feature

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-unread-badge` |
| **Feature title (working)** | P8 — Notification Inbox Unread Badge |
| **Domain area** | notifications / presentation |
| **Analysis date** | 2026-07-10 |
| **Classification (gap type)** | `UI_PRESENTATION_GAP` / `UI_ONLY_GAP` (aggregate unread visibility) |

## Analysis objective

Determine whether unread badge presentation is a valid, bounded successor feature that should proceed to **review-decision**, based on repo-inspection evidence and predecessor governance — without authorizing implementation, drafting contracts or locks, or modifying application code.

---

## 1. Feature Analysis Summary

Repository evidence confirms **`countUnread` backend support exists** and **no unread badge is rendered** anywhere in presentation. P7 delivered layout discoverability (**اعلان‌ها** nav link); users can reach the inbox but receive **no aggregate unread indicator** outside per-row status on the inbox page itself.

This feature can remain **presentation-only** for a first cycle by consuming the existing `NotificationInboxReadContract::countUnread()` through an application-facing boundary — **no new backend counting behavior is required**.

**Recommended first-cycle placement:** unread count badge on the **shared layout navigation item beside اعلان‌ها** only (single surface). This is the narrowest placement that addresses the cross-page visibility gap created once P7 made the inbox globally reachable.

**Recommended first-cycle refresh minimum:** server-rendered count on **full page load** only; no real-time, polling, or cross-page reactive update guarantee in v1.

**Recommendation:** **`READY_FOR_REVIEW_DECISION`**

No repository or architecture blocker prevents review-decision. Open product choices (zero-count display, principal-without-employee behavior, formal contract requirement) are review-decision and contract matters, not analysis blockers.

---

## 2. Inputs Reviewed

| Input | Role |
|---|---|
| `docs/ui/analysis/notifications/notification-inbox-unread-badge.repo-inspection.md` | Primary repository truth |
| `docs/ui/review/governance-next-candidate-triage.md` | Queue selection context |
| `docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml` | P7 exclusions (AC-LN-005) |
| `docs/ui/closeouts/notifications/notification-inbox-layout-navigation.closeout.md` | P7 closed baseline |
| `docs/ui/locks/notifications/notification-mark-read-mutation.implementation-lock.yaml` | P5 inbox-page `countUnread` prohibition |
| `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` | P2 deferred badge/count |
| `docs/ui/analysis/notifications/notification-inbox-layout-navigation.feature-analysis.md` | P7 successor-overlay precedent |

Prior P2/P5/P6/P7 artifacts were consulted for deferral/exclusion language only. **Closed features are not reopened.**

---

## 3. Problem Statement

### User-facing gap

Authenticated users can open the notification inbox via P7 layout navigation or direct URL, and can see **per-row** read/unread status on the inbox page. They **cannot see an aggregate unread count** on shared chrome (layout nav) or in the inbox page header.

The gap is **visibility of unread volume**, not inbox access or mark-read capability:

| Capability | Status (repository) |
|---|---|
| Reach inbox (`notifications.index`) | Delivered (P2 + P7) |
| List notifications | Delivered (P2) |
| Mark individual notification read | Delivered (P5) |
| Deep-link from row to related entity | Delivered (P6) |
| **Aggregate unread count in UI** | **Absent** |

### Evidence and governance support

- Repo-inspection: no badge/`countUnread` in views; backend `countUnread` implemented.
- P2–P7 artifacts repeatedly **deferred** `countUnread` consumption and badge surfaces as a **separate governance cycle**.
- Governance triage selected this slug as next candidate after P7 closeout.

### Backend dependency

**None for counting semantics.** `NotificationInboxReadContract::countUnread(string $recipientEmployeeId): int` is implemented and tested (`NotificationInboxTest.php`). First cycle needs only **presentation consumption** of that contract (or a thin presentation adapter delegating to it).

---

## 4. Repository Baseline

Condensed from repo-inspection (not restated in full):

| Dimension | Baseline |
|---|---|
| Unread semantics | `read_at IS NULL`, non-archived, `delivery_status = Delivered`, scoped to `recipient_employee_id` |
| Count API | `NotificationInboxReadContract::countUnread()` → `NotificationInboxReadService` → `NotificationRepository` |
| Presentation usage | **None** — `NotificationInboxPage` calls `listForRecipient` only |
| Layout nav (P7) | **اعلان‌ها** → `notifications.index`; text link only; no badge |
| Inbox UI | Per-row `is_read` labels; no aggregate count in header |
| Badge UI patterns | **None** — no badge/counter Blade components |
| Layout reactivity | Static Blade layout; inbox is Livewire; no `wire:poll` or view composers evidenced |
| Auth | `auth:api`, `request.mutation.principal`, `audit.principal` on notification routes |
| Principal scoping | `NotificationPrincipalEmployeeResolver::requireEmployeeId()` on inbox Livewire actions |
| Blocking tests | P7 negative: layout nav must not show badge/`countUnread`; P5 guard: inbox page source must not contain `countUnread` |

---

## 5. Scope Analysis

### Can this remain presentation-only?

**Yes**, for a first cycle. Unread counting rules already live in Application/Infrastructure. Presentation may display a backend-supplied integer without redefining unread semantics, provided implementation:

- delegates counting to `NotificationInboxReadContract` (or equivalent application read boundary), and
- does not query `notification_logs` or infer unread state from partial list rows in UI.

### Reuse without new backend behavior

**Yes.** No migration, repository query change, new contract method, or mark-read mutation change is evidenced as required for v1 badge display.

### Recommended in-scope (first cycle)

| Item | Rationale |
|---|---|
| Display aggregate unread count for authenticated recipient | Core gap |
| Single approved surface (see Placement) | Bound blast radius |
| Consume `countUnread` via application read contract | Preserve module boundary |
| Recipient scoping via existing principal → employee resolution pattern | Matches inbox flows |
| Successor supersession of P2/P5/P6/P7 **badge/count exclusions only** | Required to authorize display |
| Focused UI + successor test updates | P7 negative tests and P5 guard must be amended if proceeding |

### Explicitly out of scope (first cycle)

| Item | Basis |
|---|---|
| Mark-all-as-read | No backend batch contract (deferred/blocked elsewhere) |
| Pagination, filter, sort on inbox | P2/P5/P6 excluded |
| Inbox list/mark-read/deep-link behavior changes | Closed P2/P5/P6 |
| New routes, API endpoints, middleware | Repo-inspection: not required |
| Backend/`countUnread` query changes | Presentation-only sufficient |
| Real-time push, polling, `wire:poll`, event bus | Defer refresh sophistication |
| Badge on both layout nav **and** inbox header | Wider than narrowest viable placement |
| Request-module surfaces | Out of notification scope |
| New notification Policy/Gate for badge visibility | Not evidenced as required |
| Reusable global badge component library | Implementation detail; not authorized here |

---

## 6. Placement Options

### Option A — Shared layout nav badge beside **اعلان‌ها**

| Dimension | Assessment |
|---|---|
| **User value** | **High** — unread signal visible on request and other layout-wrapped pages without opening inbox |
| **Scope size** | **Small–medium** — primarily `resources/views/components/layouts/app.blade.php`; likely needs a presentation hook to supply count into static layout (mechanism deferred to contract/lock) |
| **Implementation risk** | **Medium** — cross-module layout surface; count injection into non-Livewire layout; P7 AC-LN-005 successor supersession required |
| **Test impact** | **High** — must replace/amend P7 test `does not render unread badge or countUnread output in layout nav`; add positive count render tests |
| **Prior guard interaction** | Supersedes **P7 badge exclusion only**; P5 inbox guard **unchanged** if count logic stays out of `NotificationInboxPage.php` |
| **Presentation-only** | **Yes** — if count fetched via application contract at render boundary, not repository in Blade |

### Option B — Inbox page header / count display only

| Dimension | Assessment |
|---|---|
| **User value** | **Low–medium** — useful on inbox page only; does not signal unread while user remains on `/requests` or other modules |
| **Scope size** | **Small** — `NotificationInboxPage.php` + `notification-inbox-page.blade.php` |
| **Implementation risk** | **Medium** — P5 architecture guard **forbids `countUnread` in inbox page source**; successor amendment required; natural refresh after `markNotificationRead` → `refreshList` |
| **Test impact** | **Medium** — amend P5 architecture guard; add inbox header count tests; P7 negative tests unchanged |
| **Prior guard interaction** | Supersedes **P5 `countUnread` prohibition in inbox Livewire**; P7 unchanged |
| **Presentation-only** | **Yes** — Livewire delegates single contract read |

### Option C — Both layout nav and inbox header

| Dimension | Assessment |
|---|---|
| **User value** | **High** | 
| **Scope size** | **Large** — two surfaces, two refresh contexts |
| **Implementation risk** | **High** — supersedes both P5 and P7 guards; duplicate count consumption |
| **Test impact** | **High** |
| **Presentation-only** | Possible but **not recommended** for first cycle |

### Recommended narrowest viable placement

**Option A — layout navigation badge beside اعلان‌ها only.**

Reasoning:

1. Post-P7, the product gap is **global unread awareness**, not inbox-page-only summary (per-row status already exists on inbox).
2. Option B avoids P7 test changes but **does not address cross-page visibility**, which is the primary user value after layout nav delivery.
3. Option C exceeds first-cycle boundary.
4. P7 established that **layout-level notification presentation** requires successor governance with explicit supersession — badge on the P7 nav item is structurally analogous.

**Analysis note:** Count delivery into static layout is an **implementation mechanism** (e.g., view composer, layout Livewire island, middleware-shared view data). Feature-analysis does not select the mechanism; contract/lock must pin allowed surfaces and forbid repository/Blade query leakage.

---

## 7. Refresh Semantics Analysis

### Repository constraints

- Layout (`app.blade.php`) is **static Blade** on each full HTTP response.
- Inbox mark-read refreshes **inbox list only** via Livewire `refreshList()`; no cross-page nav update path exists today.
- No `wire:poll`, view composers, or event-driven nav refresh evidenced.

### First-cycle minimum acceptable behavior (recommendation)

| Behavior | First-cycle expectation |
|---|---|
| Count on initial page render | **In scope** — display `countUnread` when layout renders for authenticated user with resolvable employee |
| Count after mark-read on inbox page | **Accept stale layout badge until next full page load** for v1 unless contract explicitly authorizes more |
| Real-time accuracy | **Out of scope** — no guarantee |
| Polling / Livewire nav island / browser events | **Defer** to later cycle or contract optional enhancement |
| Count after new notification delivery while user on another page | **Stale until next navigation** acceptable for v1 |

This minimum keeps the first cycle presentation-only and avoids inventing a reactive architecture not present in the repository.

### Deferred refresh concerns

- Post-mark-read layout badge update without full reload
- Post-delivery push/update while user on non-inbox pages
- `wire:navigate` interaction effects (layout nav uses plain `href` per P7)

---

## 8. Authorization / Visibility Analysis

| Question | Analysis |
|---|---|
| Who sees the badge? | **All authenticated users who render shared layout** — parity with P7 nav visibility (`app.blade.php` has no nav-level role checks) |
| Employee principal required? | **Count fetch requires `recipientEmployeeId`** — same scoping as inbox. Repo shows inbox surfaces error when principal has no linked employee (`NotificationInboxUiFlowTest`). Badge behavior for unlinked principal is **not evidenced** |
| Show when count is zero? | **Not determined in repository** — product/governance choice |
| Guest access | Guests do not render authenticated layout pages (redirect to login) |

### Classification of visibility questions

| Topic | Classification |
|---|---|
| Authenticated layout parity with P7 | **Safe assumption** for review-decision |
| Principal without linked employee (hide vs 0 vs omit nav) | **Must resolve in review-decision** |
| Hide badge when count = 0 vs show "0" | **Must resolve in review-decision** (or contract if review delegates) |
| New role/permission gating | **Out of scope** — not evidenced |

None of the open visibility questions are **blocking** for proceeding to review-decision; they are decision inputs.

---

## 9. Architecture / Anti-Leak Analysis

### Leakage risk

| Risk | Assessment |
|---|---|
| Layout owns unread business rules | **Mitigated** if layout only renders an integer supplied by Notification application read boundary |
| Direct repository/DB access from Blade/layout | **Forbidden** — must not appear in implementation |
| UI infers unread count from list row data | **Forbidden** — aggregate must come from `countUnread`, not `listForRecipient` length |
| Cross-module coupling | Layout is shared chrome; **acceptable** if Notification module owns count resolution and exposes data through presentation adapter/composer registered in Notification presentation/infrastructure — not Request module |

### Dependency boundary to preserve

```
Layout Blade (render only)
  ← presentation-supplied unread_count (int)
    ← NotificationInboxReadContract::countUnread($employeeId)
      ← NotificationInboxReadService → NotificationRepository
```

`NotificationPrincipalEmployeeResolver` remains the employee scoping entry consistent with inbox flows.

### UI Anti-Leak posture

Displaying a backend-computed unread integer is **presentational consumption**, not business-authoritative derivation, **if**:

- UI does not redefine what "unread" means,
- UI does not compute count from partial state,
- UI does not mirror authorization beyond existing authenticated layout model.

A dedicated presentation DTO field (e.g., `unread_count`) is optional; direct contract int is acceptable for v1 if contract pins delegation.

### Blockers for review-decision?

**No.** Architecture concerns are **constrainable in contract/lock** using P7 successor-overlay pattern. They do not block advancing to review-decision.

---

## 10. Prior Governance Interaction

Successor feature only — **does not reopen** closed P2/P5/P6/P7 deliveries.

| Predecessor | Closed delivery | P8 successor impact (if approved) |
|---|---|---|
| **P2** read-only inbox | Inbox list baseline | Supersedes P2 **deferred** `countUnread`/badge exclusion only |
| **P5** mark-read | Per-row mark-read on inbox page | **No inbox behavior change** if Option A (layout-only). P5 guard on `countUnread` in `NotificationInboxPage.php` **remains** unless scope shifts to Option B |
| **P6** deep-link | Row **مشاهده** to `requests.show` | **No impact** |
| **P7** layout nav | **اعلان‌ها** link without badge | Supersedes P7 **AC-LN-005 / badge exclusion only**; nav link, label, order, active state, plain `href` **unchanged** |

### Required successor amendments (preview — not executed here)

| Artifact / test | Change if Option A approved |
|---|---|
| P7 contract AC-LN-005 / forbidden badge language | Successor supersession in P8 contract |
| `NotificationInboxUiFlowTest` layout-nav negative badge test | Replace with positive badge assertions + zero-count case |
| P5 inbox architecture guard (`countUnread` forbidden in inbox page) | **No change** (layout-only path) |
| P2/P5/P6 contracts | Unchanged except explicit P8 supersession note for badge deferral |

---

## 11. Test Strategy Preview

If review-decision approves (no tests written in this step):

| Category | Likely needs |
|---|---|
| **Positive UI render** | Layout nav shows numeric badge when `countUnread > 0` (authenticated, linked employee, seeded notifications) |
| **Zero-count behavior** | Assert hide vs show-zero per review-decision outcome |
| **P7 successor** | Amend `does not render unread badge...` test in `NotificationInboxUiFlowTest.php` |
| **Cross-module regression** | Extend `RequestUiFlowTest` layout assertions if badge appears on `/requests` |
| **Principal without employee** | Assert badge omission or error-safe render per review decision |
| **Mark-read refresh** | v1: optional test that layout count unchanged until reload; or defer |
| **Architecture guard** | Ensure `NotificationInboxPage.php` still excludes `countUnread` if layout-only; update guard only if inbox scope authorized |
| **Backend tests** | **Likely none** — `NotificationInboxTest.php` already covers `countUnread` |

---

## 12. Risks And Open Questions

| Issue | Classification |
|---|---|
| Layout count injection mechanism undefined | **Can resolve in contract** |
| Zero-count display policy | **Must resolve in review-decision** |
| Unlinked principal badge behavior | **Must resolve in review-decision** |
| Stale badge after mark-read on other pages | **Can defer** (acceptable v1) |
| P7 test/guard supersession governance | **Must resolve in review-decision** (authorize successor) |
| Formal contract required vs direct UI path | **Must resolve in review-decision** — P7 precedent favors **contract required** for layout overlay |
| Badge visual design / component extraction | **Can resolve in contract/lock** |
| Polling/real-time refresh | **Defer** |
| Both layout + inbox surfaces | **Out of scope** for recommended v1 |
| Repository ambiguity | **None blocking** |

### Blocking for review-decision?

**None identified.**

---

## 13. Recommendation

| Field | Value |
|---|---|
| **Verdict** | **`READY_FOR_REVIEW_DECISION`** |
| **Feature validity** | Valid bounded presentation successor |
| **Backend readiness** | Sufficient — existing `countUnread` |
| **Recommended v1 placement** | Layout nav badge beside **اعلان‌ها** only |
| **Recommended v1 refresh** | Full page load render only; stale cross-page acceptable |
| **Contract expectation** | Likely **required** (P7 layout successor precedent); final call in review-decision |

This feature should proceed to review-decision. It does not require additional repo-inspection, product blocking, or architecture blocking at this stage.

---

## 14. Authorized Next Artifact

When review-decision is the authorized next step:

**`docs/ui/review/notifications/notification-inbox-unread-badge.review-decision.md`**

Review-decision should resolve: zero-count policy, unlinked-principal behavior, contract requirement confirmation, successor supersession scope (P2/P7 minimum; P5 if inbox surface considered), and explicit rejection of deferred refresh/polling for v1.

---

*This document performs feature analysis only. It does not approve the feature, authorize implementation, or create contract, lock, or review-decision artifacts.*
