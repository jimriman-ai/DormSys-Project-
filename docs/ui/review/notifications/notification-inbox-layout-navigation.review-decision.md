# Notification Inbox Layout Navigation — Review Decision

## Feature

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-layout-navigation` |
| **Feature title** | P7 — Notification Inbox Layout Navigation |
| **Domain area** | notifications |
| **Classification** | `UI_DISCOVERABILITY_GAP` / `UI_ONLY_GAP` |
| **Decision date** | 2026-07-10 |

## Review objective

Decide whether P7 is authorized to proceed through governance, confirm approved scope and boundaries, resolve feature-analysis open questions, and authorize the **exact next artifact**. This review does **not** authorize implementation, draft a contract or lock, or modify application code.

---

## 1. Decision summary

| Field | Decision |
|---|---|
| **Verdict** | **Approved** |
| **Disposition** | `APPROVED_READY_FOR_CONTRACT` |
| **Implementation authorized?** | **No** — contract drafting is the next authorized step only |
| **Blockers** | **None** |

P7 is approved as a **standalone presentation-only successor feature** that supersedes only the prior **layout navigation deferral/exclusion** from P2, P5, and P6. The approved change is a single shared-layout nav link to the existing `notifications.index` route. Contract creation is the **required and authorized** next governance artifact.

---

## 2. Inputs reviewed

| Artifact | Role |
|---|---|
| `docs/ui/analysis/notifications/notification-inbox-layout-navigation.repo-inspection.md` | Repository truth |
| `docs/ui/analysis/notifications/notification-inbox-layout-navigation.feature-analysis.md` | Primary decision basis |
| `docs/ui/closeout/notifications/notification-inbox-deep-link-navigation.closeout.md` | P6 closed; layout nav deferred |
| `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` | P2 layout-nav deferral |
| `docs/ui/contracts/notifications/notification-inbox-deep-link-navigation.feature-contract.yaml` | P6 layout-nav exclusion |
| `resources/views/components/layouts/app.blade.php` | Shared layout nav owner |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Page title reference |

---

## 3. Decision question resolutions

| # | Question | Decision |
|---|---|---|
| 1 | Is P7 a valid standalone successor feature? | **Yes** — orthogonal to closed P2/P5/P6 inbox-page behavior; supersedes only layout-nav deferral/exclusion |
| 2 | Is recommended scope correct? | **Yes** — layout navigation only; no request pages, inbox page, home redirect, or badge work |
| 3 | Is `app.blade.php` the correct ownership surface? | **Yes** — sole evidenced global nav surface |
| 4 | Is nav visible to all authenticated users acceptable? | **Yes** — matches existing **درخواست‌ها** nav parity |
| 5 | Should nav-level employee gating be excluded from P7? | **Yes** — deferred to separate governance if product later requires it |
| 6 | Is **اعلان‌ها** the correct nav label? | **Yes** — page title **اعلان‌های من** remains unchanged on inbox page |
| 7 | Is placement after **درخواست‌ها** acceptable? | **Yes** |
| 8 | Should active state follow existing nav pattern? | **Yes** — `request()->routeIs('notifications.*')` with same class ternary as requests |
| 9 | Does P7 require formal contract before implementation? | **Yes** — P2 forbidden change + P5/P6 exclusions + cross-module layout |
| 10 | What exact next artifact is authorized? | **Feature contract:** `docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml` |

---

## 4. Approved scope

The following is **approved for feature contract drafting** (not implementation):

| Dimension | Approved boundary |
|---|---|
| **Feature type** | Presentation-only discoverability successor |
| **Surface** | `resources/views/components/layouts/app.blade.php` header `<nav>` only |
| **Affordance** | Plain anchor link to `route('notifications.index')` |
| **Nav label** | **اعلان‌ها** |
| **Placement** | Second nav item, immediately after **درخواست‌ها** |
| **Destination** | Existing `notifications.index` (`GET /notifications`) — no new route |
| **Visibility** | All authenticated users who render shared layout (Option A parity) |
| **Active state** | `request()->routeIs('notifications.*')` → active classes mirror **درخواست‌ها** pattern (`font-semibold text-sky-700` vs `text-slate-600 hover:text-slate-900`) |
| **Navigation transport** | Plain `href` — **no** `wire:navigate` on layout nav (match existing requests nav item) |
| **Backend / routes / middleware** | No changes |
| **Inbox page** | No changes to `NotificationInboxPage` or inbox Blade |
| **Request pages** | No changes |
| **Home redirect** | Unchanged (`/` → `/requests`) |
| **Successor supersession** | Supersedes **only** P2 deferred layout nav and P5/P6 excluded layout nav to inbox |

---

## 5. Explicit out-of-scope / rejected scope

The following are **rejected for P7** and must not appear in contract, lock, or implementation unless a future feature explicitly authorizes them:

| Rejected item | Disposition |
|---|---|
| Request list/show/create page-level entrypoints | **Rejected** — separate discoverability surface |
| Inbox page modifications (list, mark-read, deep-link) | **Rejected** — closed P2/P5/P6 |
| Home redirect to `/notifications` | **Rejected** |
| Unread badge / `countUnread` consumption or display | **Rejected** — deferred separate candidate |
| Mark-all-as-read | **Rejected** |
| Notification detail page | **Rejected** |
| New HTTP/API routes | **Rejected** |
| Backend, Application, Domain, DTO, read-contract, migration changes | **Rejected** |
| Notification Policy / Gate / permission model | **Rejected** |
| Nav-level employee-linkage gating | **Rejected for P7** — see visibility decision |
| Backend capability flag (e.g. `can_view_inbox`) for nav | **Rejected for P7** |
| `wire:navigate` on layout nav link | **Rejected** — inconsistent with existing layout nav |
| Pagination, filter, sort | **Rejected** |
| Reopening or amending P2/P5/P6 closeouts, contracts, or locks | **Rejected** |

---

## 6. Ownership decision

| Layer | P7 change authorized? |
|---|---|
| `resources/views/components/layouts/app.blade.php` | **Yes** — primary and only required presentation file |
| `NotificationInboxPage` / notification inbox Blade | **No** |
| Request module Livewire / Blade | **No** |
| Routes / middleware | **No** |
| Application / Domain / Infrastructure | **No** |

**Rationale:** Repository inspection confirms the discoverability gap exists solely on the shared layout surface. Route, page, and inbox behaviors are operational through closed predecessors.

---

## 7. Visibility decision

**Approved rule:** Nav link **اعلان‌ها** is **visible to all authenticated users** who render `components.layouts.app`, with **no nav-level conditional** on linked employee, role, or permission.

| Option | Disposition |
|---|---|
| **A — Show to all authenticated (layout parity)** | **Approved for P7** |
| **B — Hide when no linked employee** | **Rejected for P7** — no repository precedent; nav-level authorization mirroring |
| **C — Backend capability flag for nav visibility** | **Rejected for P7** — requires Application extension |

**Accepted parity risk:** Principals without a linked employee may follow the nav link and encounter inbox page error state via existing `NotificationPrincipalEmployeeResolver` behavior. This matches current layout model (requests nav is not employee-gated at nav level). If product rejects this UX, **nav-level gating is a separate future governance item** — not a P7 blocker.

---

## 8. Label and placement decision

| Element | Approved value | Notes |
|---|---|---|
| **Nav label** | **اعلان‌ها** | Module noun; parallels **درخواست‌ها** |
| **Inbox page title** | **اعلان‌های من** (unchanged) | Page-scoped possessive copy stays on inbox page header |
| **Nav order** | After **درخواست‌ها** | Requests remain primary module (home redirect evidence) |
| **Rejected nav label** | **اعلان‌های من** | Too long for global chrome; conflates nav with page title |

---

## 9. Active-state and navigation pattern decision

| Pattern element | Approved behavior |
|---|---|
| Active detection | `request()->routeIs('notifications.*')` |
| Active classes | Same ternary as requests: active → `font-semibold text-sky-700`; inactive → `text-slate-600 hover:text-slate-900` |
| Requests active state | Unchanged — `request()->routeIs('requests.*')` |
| Link mechanism | `href="{{ route('notifications.index') }}"` |
| `wire:navigate` | **Not used** — layout nav matches **درخواست‌ها** (page-level P3/P6 links use `wire:navigate`; layout nav does not) |

Contract must freeze these patterns so implementation cannot introduce divergent navigation transport or styling.

---

## 10. Governance rationale

### Why P7 is approved

- Repository and analysis confirm a **UI-only discoverability gap**: reachable inbox vs no shared nav entrypoint.
- P2 **deferred** layout nav; P5 and P6 **excluded** it explicitly. P6 is **IMPLEMENTED_VERIFIED**. Layout nav is the documented remaining deferred presentation gap.
- Scope is **minimal and bounded**: one nav anchor in shared layout; no backend or inbox behavior change.
- Feature-analysis open questions (label, placement, visibility, active state, `wire:navigate`) are **resolved in this review** with sufficient precision for contract drafting.

### Why contract is required (not direct implementation)

| Factor | Rationale |
|---|---|
| P2 lock | Forbidden layout navigation changes without separate authorization |
| P5/P6 exclusions | Layout nav explicitly out of scope — successor supersession must be documented |
| Cross-module blast radius | `app.blade.php` affects Request and Notification pages |
| P3 precedent | Page-level discoverability only; **does not** authorize shared layout changes without contract |

### P2 / P5 / P6 successor relationship

| Predecessor | P7 relationship |
|---|---|
| **P2 (CLOSED)** | P7 supersedes **only** deferred layout nav link — not read-only list behavior |
| **P5 (CLOSED)** | P7 does not alter mark-read; supersedes **only** layout-nav exclusion |
| **P6 (IMPLEMENTED_VERIFIED)** | P7 does not alter row deep-link; supersedes **only** layout-nav exclusion |
| **Unread badge** | Remains deferred; separate candidate |

P2, P5, and P6 artifacts remain **unchanged**. P7 authority flows through new P7 contract → lock → implementation chain.

### Why not blocked

- No architecture change required (`UI_ONLY_GAP`).
- Visibility Option A is consistent with repository evidence and existing nav model.
- Employee gating deferral is an **accepted parity risk**, not a product blocker for P7 contract drafting.

---

## 11. Authorized next step

| Field | Value |
|---|---|
| **Next authorized artifact** | Feature contract |
| **Path** | `docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml` |
| **Contract must freeze** | Scope (layout nav only), surface (`app.blade.php`), label (**اعلان‌ها**), placement, destination (`notifications.index`), visibility (all authenticated), active-state rule, no `wire:navigate`, explicit exclusions, P2/P5/P6 supersession boundary, test obligations (high level) |
| **Not authorized yet** | Implementation lock, code changes, test implementation |

After contract drafting: **contract review decision** → **implementation lock** → **implementation authorization**.

---

## 12. Blockers

**None.**

Product rejection of Option A visibility in a future review would require a **new governance item** for nav-level gating — it does not retroactively block this review or contract drafting under the approved Option A default.

---

## 13. Explicit non-goals (deferred / out of band)

| Item | Status |
|---|---|
| Unread badge / `countUnread` on layout nav | **Deferred** — separate feature candidate |
| Nav-level employee / role gating | **Deferred** — separate governance if product requires |
| Page-level inbox entrypoints on request surfaces | **Out of band for P7** |
| Home redirect change | **Out of band for P7** |
| P2 list / P5 mark-read / P6 deep-link changes | **Closed — not reopened** |

---

## 14. Test expectations for contract (guidance only — not test authoring)

Contract should reference that implementation tests are expected to cover, at minimum:

- Nav link **اعلان‌ها** with `href` to `notifications.index` on authenticated `GET /requests` (cross-module regression)
- Nav link present on authenticated `GET /notifications`
- Active-state behavior on notification routes (recommended)
- No badge/count assertions
- No changes to P5/P6 inbox behavior tests as authority

Primary test file: `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php`. Secondary: `tests/Feature/Modules/Request/RequestUiFlowTest.php`.

---

## 15. Final classification

**APPROVED_READY_FOR_CONTRACT**

P7 is approved as a valid standalone presentation-only successor feature. Approved scope is shared layout navigation only in `resources/views/components/layouts/app.blade.php`, linking **اعلان‌ها** to `notifications.index` after **درخواست‌ها**, visible to all authenticated users, with active state matching the existing requests nav pattern and without `wire:navigate`. Implementation is **not authorized**. The **required next artifact** is the feature contract at `docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml`.

---

Recommended next governance step: **create feature contract**
