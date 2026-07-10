# P7 — Notification Inbox Layout Navigation — Feature Analysis

## Feature

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-layout-navigation` |
| **Feature title (working)** | P7 — Notification Inbox Layout Navigation |
| **Domain area** | notifications |
| **Analysis date** | 2026-07-10 |
| **Classification (gap type)** | `UI_DISCOVERABILITY_GAP` / `UI_ONLY_GAP` |

## Analysis objective

Determine whether and how to add **user-visible discoverability** for the existing Notification Inbox route through **shared layout navigation**, based on repository inspection evidence and predecessor governance — without authorizing implementation, drafting contracts or locks, or modifying application code.

---

## Inputs considered

| Input | Role |
|---|---|
| `docs/ui/analysis/notifications/notification-inbox-layout-navigation.repo-inspection.md` | Primary repository truth |
| `docs/ui/closeout/notifications/notification-inbox-deep-link-navigation.closeout.md` | P6 closed scope; deferred layout nav |
| `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` | P2 layout-nav deferral and forbidden change |
| `docs/ui/contracts/notifications/notification-inbox-deep-link-navigation.feature-contract.yaml` | P6 explicit layout-nav exclusion |
| `docs/ui/verification/requests/request-create-entrypoint-discoverability.implementation-verification.md` | P3 page-level discoverability precedent (partial) |
| `resources/views/components/layouts/app.blade.php` | Shared layout nav owner |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Inbox page title/copy reference |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Inbox access tests; no layout-nav coverage |

---

## Repository facts consumed from inspection

The following are **confirmed** in the repo-inspection artifact and underpin this analysis:

1. **`GET /notifications`** is registered as **`notifications.index`**, handled by **`NotificationInboxPage`**, using layout **`components.layouts.app`**.

2. **Shared authenticated layout** is **`resources/views/components/layouts/app.blade.php`**. Header `<nav>` exposes one module link: **درخواست‌ها** → `requests.index`, with `request()->routeIs('requests.*')` active styling.

3. **No user-visible notification inbox link** exists in inspected Blade views outside the inbox page content itself.

4. **Home (`/`) redirects to `/requests`**, not notifications.

5. **No Notification-specific Policy, Gate, or role check** governs navigation visibility in layout or Notification presentation code.

6. **Inbox page execution** requires a linked employee via **`NotificationPrincipalEmployeeResolver::requireEmployeeId()`** on load — a **page-level** constraint, not a nav render gate today.

7. **Existing tests** cover inbox route access, middleware, and inbox UI states (P2/P5/P6); **no test** asserts layout navigation presence or absence.

8. **P2 deferred layout navigation**; **P5 and P6 explicitly excluded** layout nav to inbox. P6 is **IMPLEMENTED_VERIFIED** and does not add inbox discoverability from other surfaces.

9. **P3 discoverability** was delivered on the **request list page** (header + empty state), **not** in shared layout nav.

10. **Unread badge / `countUnread`** is deferred and not consumed in layout (separate future candidate).

---

## Feature summary

P7 addresses the **remaining inbox discoverability gap**: authenticated users can reach a fully functional notification inbox by direct URL, but **no shared navigation surface** directs them there. P7 would add a **global header nav link** to the existing `notifications.index` route — presentation-only, reusing current route and page ownership, without changing inbox list, mark-read, deep-link, or badge behavior.

---

## Problem statement

### What exact discoverability gap does P7 address?

**Gap:** **Reachable inbox route/page** vs **absence of in-app navigation affordances** on the shared authenticated layout.

Users on request list, create, or show pages (and any future page using `components.layouts.app`) have no header/nav path to the notification inbox. The inbox is **route-only discoverable** unless the user knows `/notifications` or receives an external link.

### Why is this not covered by P2, P5, or P6?

| Predecessor | What it delivered | Why it does not solve P7 |
|---|---|---|
| **P2 — Read-only inbox list** | Greenfield `notifications.index`, inbox table, loading/empty/ready/error states | P2 **explicitly deferred** layout navigation link; created the destination, not the global entrypoint |
| **P5 — Mark-read mutation** | Per-row mark-read in **عملیات** on the inbox page | Mutation overlay on inbox content only; **excluded** layout nav |
| **P6 — Deep-link navigation** | Row-level **مشاهده** link from eligible inbox rows to `requests.show` | Navigation **from inbox outward** to related entities; **excluded** layout nav **to inbox** |

P2/P5/P6 govern **inbox page behavior** (list, mark-read, row deep-link). P7 governs **how users find the inbox** from shared chrome. These are orthogonal presentation concerns.

### Why is direct route access insufficient from a UI/navigation perspective?

Repository evidence shows the product surface assumes **layout-mediated module navigation**:

- Home redirects to `/requests`, establishing requests as the default authenticated landing module.
- The only global nav item today is **درخواست‌ها**, reinforcing requests as the visible module boundary.
- Notification delivery, mark-read, and deep-link flows assume users can **arrive at the inbox**; without layout nav, that arrival depends on undocumented URL knowledge or out-of-band links.

Direct URL access is **technically sufficient** for testing and power users; it is **not sufficient for consistent in-app discoverability** within the current layout model.

---

## Scope recommendation

### Recommended scope: **layout navigation only**

P7 should add a **single header nav link** in `components/layouts/app.blade.php` pointing to `route('notifications.index')`, with active-route styling consistent with the existing **درخواست‌ها** item.

### Explicitly out of recommended scope (unless separately authorized)

| Surface | Recommendation | Justification |
|---|---|---|
| Request list/show/create page headers | **Out of scope** | Feature title and P2 deferral target **layout navigation**; page-level entrypoints are a different discoverability surface (P3 precedent applies to request create, not notification inbox) |
| Inbox page self-entrypoint | **Out of scope** | User is already on destination |
| Home redirect change | **Out of scope** | Not layout navigation; would alter default landing behavior |
| Unread badge / `countUnread` | **Out of scope** | Explicitly deferred in P2/P5/P6; separate candidate |
| New routes, backend, resolver changes | **Out of scope** | Route and page exist; no repository gap for linking |

### Scope boundary statement

**Layout navigation plus page-level entrypoints is not recommended for P7.** Page-level discoverability on request surfaces would expand blast radius into Request module views and duplicate the global nav responsibility. P3 authorized page-level create affordances on the request list; it did **not** modify shared layout nav and is **not** automatic authorization for cross-module layout changes (per analysis mandate).

---

## Non-goals

| Non-goal | Basis |
|---|---|
| P2 read-only list behavior changes | Closed baseline |
| P5 mark-read mutation changes | Closed overlay |
| P6 row deep-link navigation changes | Closed overlay |
| Unread badge / `countUnread` display | Deferred; conflation risk |
| Mark-all-as-read | Not evidenced; excluded in P2/P5/P6 |
| Notification detail page | No route evidenced |
| Pagination, filter, sort | P2/P5/P6 excluded |
| New HTTP/API routes | Route exists |
| Notification-specific Policy/Gate/permission model | Not evidenced as required; P2 excluded new permission model |
| Nav-level employee linkage gating (unless contract explicitly authorizes) | No repository precedent; separate product/governance item |
| Home redirect to notifications | Outside layout-nav scope |

---

## Ownership analysis

### Is `resources/views/components/layouts/app.blade.php` the correct primary surface?

**Yes (analysis conclusion).** Repository inspection identifies it as the **sole evidenced global navigation surface** for all authenticated Livewire full pages (`RequestListPage`, `RequestCreatePage`, `RequestShowPage`, `NotificationInboxPage`). No sidebar, drawer, or secondary menu exists under `resources/views/components/`.

### Should `NotificationInboxPage` or request pages be modified?

**No (analysis conclusion)** for minimum valid P7 scope.

| File | Modification needed for layout nav? |
|---|---|
| `components/layouts/app.blade.php` | **Yes** — add nav link and active state |
| `NotificationInboxPage` / inbox Blade | **No** — destination page; no self-entrypoint |
| Request list/show/create Blade | **No** — page-level entrypoints out of recommended scope |

### Does this feature require backend/application/domain changes?

**No (analysis conclusion).** Repository inspection finds no gap requiring Application, Domain, Infrastructure, route registration, DTO, or read-contract changes to **link** to the existing inbox. `NotificationPrincipalEmployeeResolver` and inbox read path already support page execution once the user navigates there.

**Gap classification:** **`UI_ONLY_GAP`** — presentation discoverability only.

---

## Visibility / authorization analysis

### Should the link be visible to all authenticated users using the shared layout?

**Recommended default (analysis): Yes — match existing nav parity.**

Repository evidence:

- **درخواست‌ها** nav link renders for all users who reach authenticated pages using `components.layouts.app`.
- No `@can`, `@role`, or employee-linkage conditional wraps the requests nav item.
- Both requests and notifications routes share the same authenticated middleware stack.

P7 nav visibility should **follow the same pattern** unless product explicitly requires otherwise.

### Should it be hidden for principals without a linked employee?

**Not recommended in P7 (analysis conclusion).** Hiding the nav link would introduce **nav-level authorization mirroring** not present for requests today. No repository evidence supports nav-level employee gating.

**Risk:** A principal without a linked employee could click the nav link and hit inbox **error state** when `NotificationPrincipalEmployeeResolver` throws on `refreshList()`.

| Option | Assessment |
|---|---|
| **A — Show nav to all authenticated users (parity with requests nav)** | **Recommended for P7** — minimal scope; consistent with current layout model |
| **B — Hide nav when no linked employee** | Requires new nav-level conditional logic; no precedent; approaches permission model P2 excluded |
| **C — Backend capability flag (e.g. `can_view_inbox`)** | Out of scope; would require Application/presentation contract extension |

**Resolution recommendation:** Adopt **Option A** in P7 contract. Document accepted parity risk. If product requires Option B or C, treat as **separate governance item** — not a P7 implementation blocker if contract freezes Option A.

### Is there existing repository evidence for nav-level employee gating?

**No.** Inspection found no nav-level employee or role checks in `app.blade.php` or Notification presentation code.

### Should this be resolved in P7 or deferred?

**Resolve in P7 contract** with frozen rule: *nav link visible to all authenticated users on shared layout pages; page-level employee resolution unchanged.* Defer nav-level gating or capability delivery to a future authorization/discoverability feature if product rejects Option A.

---

## Nav label and placement analysis

### Label options

| Label | Assessment |
|---|---|
| **اعلان‌ها** | **Recommended** — short module noun; parallels **درخواست‌ها** (plural module label, not possessive page title) |
| **اعلان‌های من** | Page-scoped copy; matches inbox `<x-ui.page-header title="اعلان‌های من">` but **longer for nav**; "my" semantics belong on page, not global chrome |
| **اعلان** (singular) | Inconsistent with **درخواست‌ها** plural module pattern |

**Analysis conclusion:** Contract should freeze nav label **`اعلان‌ها`**. Page title **`اعلان‌های من`** remains unchanged on the inbox page.

### Placement relative to **درخواست‌ها**

**Recommended:** Second item in header `<nav>`, immediately after **درخواست‌ها**.

Justification:

- Home redirects to `/requests` — requests remain primary module.
- No second-module precedent exists; placing notifications adjacent to requests follows natural module ordering.
- RTL layout uses horizontal `gap-4` flex nav; order **درخواست‌ها** then **اعلان‌ها** reads left-to-right in DOM (visual order follows RTL rules).

---

## Expected UI behavior (analysis — not implementation)

When P7 is implemented under recommended scope:

| Behavior | Expected |
|---|---|
| **Nav affordance** | Plain `<a href="{{ route('notifications.index') }}">` in header `<nav>` |
| **Label** | **اعلان‌ها** (recommended) |
| **Active state (on notification routes)** | `request()->routeIs('notifications.*')` → `font-semibold text-sky-700`; else `text-slate-600 hover:text-slate-900` — mirrors requests pattern |
| **Active state (on request routes)** | Existing `requests.*` logic unchanged |
| **wire:navigate** | **Not used on layout nav** — existing **درخواست‌ها** link uses plain `href` without `wire:navigate`; page-level links (P3, P6) use `wire:navigate`. P7 should **match layout nav pattern** for consistency |
| **Guest users** | Nav not rendered — guest routes use `guest:api` group; authenticated layout not shown |
| **Badge/count** | None — no numeric unread indicator |
| **Inbox page content** | Unchanged — P2 list, P5 mark-read, P6 deep-link preserved |

---

## Governance recommendation

### P7 vs P3 precedent

| Dimension | P3 (request create discoverability) | P7 (notification inbox layout nav) |
|---|---|---|
| Surface | Request list Blade (module page) | Shared layout (cross-module) |
| Prior exclusion | Request List contract out-of-scope only | P2 deferral + P5/P6 explicit exclusion |
| Governance path | Eventually `DIRECT_UI_IMPLEMENTATION_AUTHORIZED` | Requires **successor supersession** of deferred/excluded item |
| Blast radius | Request list views + tests | All pages using `components.layouts.app` |

**Analysis conclusion:** P3 is **insufficient precedent** for skipping contract on P7. Shared layout changes warrant explicit successor governance.

### Recommended governance path

**Proceed to formal feature contract** (`notification-inbox-layout-navigation.feature-contract.yaml`), then review decision and implementation lock — following the **P5/P6 successor-overlay pattern**:

1. **Contract** — freeze scope (layout nav only), label, placement, active-state rule, visibility rule (authenticated parity), exclusions, and explicit supersession of P2/P5/P6 layout-nav deferrals/exclusions only.
2. **Review decision** — approve contract scope and confirm no badge/backend expansion.
3. **Implementation lock** — authorize changes to `app.blade.php` and designated test file(s) only.

### Why not direct UI implementation authorization?

- P2 lock **forbids** layout navigation changes without separate authorization.
- P5/P6 contracts and locks **explicitly exclude** layout nav.
- Cross-module layout blast radius exceeds P3 page-level scope.
- Contract can freeze label, visibility, and non-goals before code changes.

### Why not implementation lock first?

Lock requires contract-approved scope boundaries. Contract should precede lock.

### Classification for governance step

**READY_FOR_CONTRACT**

---

## Test strategy (if implementation proceeds — specification only)

### Most appropriate test file(s)

| File | Rationale |
|---|---|
| **`tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php`** | **Primary** — already owns inbox UI access, middleware, and architecture guards; layout nav is inbox discoverability concern |
| **`tests/Feature/Modules/Request/RequestUiFlowTest.php`** | **Secondary** — assert nav appears on a representative request page using shared layout (cross-module regression) |

No new test file required unless governance separately approves; extending existing feature tests matches P3/P6 patterns.

### Recommended coverage

| Scenario | Assert? | Notes |
|---|---|---|
| Authenticated `GET /requests` shows nav link to inbox | **Yes** | Representative shared-layout page; `assertSee('اعلان‌ها')` and `assertSeeHtml('href="'.route('notifications.index').'"')` |
| Authenticated `GET /notifications` shows nav link | **Yes** | Destination page also uses shared layout |
| Active styling on notification page | **Yes (optional but recommended)** | Assert active class or `font-semibold text-sky-700` on notification nav when on `notifications.index` — may use HTML structure assertion |
| Guest `GET /requests` or `/notifications` | **Yes (existing guest redirect tests)** | Guest redirect already tested for inbox; nav not visible because layout not reached — no separate nav assertion strictly required |
| Principal without linked employee — nav presence | **Conditional** | If contract adopts Option A: assert nav **present** on authenticated layout; page load error remains separate inbox test concern |
| Principal without linked employee — nav hidden | **Only if contract authorizes Option B** | Not recommended for P7 |
| Unread badge / count assertions | **No** | Explicitly out of scope |
| Mark-read / deep-link / inbox table behavior | **No change** | Existing P5/P6 tests remain authoritative |
| Layout nav on request create/show | **Optional spot-check** | Same layout; one request test may suffice |

### Tests to avoid

- `countUnread` or badge text/numbers
- Inbox row mark-read or deep-link regressions (unless incidental page load)
- New route registration tests (route unchanged)

---

## Risks and mitigations

| Risk | Severity | Mitigation |
|---|---|---|
| **Shared layout blast radius** | Medium | Limit diff to one nav anchor + active class; contract lists allowed file (`app.blade.php`) only |
| **Governance conflict with P2/P5/P6** | **High** | Mandatory successor contract with explicit supersession language for layout-nav deferral/exclusion only |
| **Employee resolver failure after nav click** | Medium | Contract documents Option A parity; inbox error state already exists; defer nav gating to separate feature if unacceptable |
| **Badge/countUnread scope creep** | Medium | Contract and lock forbid `countUnread` consumption; tests must not assert counts |
| **Persian label inconsistency** | Low | Freeze **اعلان‌ها** in contract; keep page title **اعلان‌های من** unchanged |
| **Insufficient regression coverage** | Medium | Require nav assertions on at least one request page and notification page |
| **P6/P5 regression on inbox page** | Low | Do not modify inbox Livewire/Blade; layout-only change |
| **wire:navigate inconsistency** | Low | Follow layout nav pattern (no `wire:navigate`) per repository evidence |
| **Accidental page-level scope expansion** | Medium | Contract `out_of_scope`: request pages, inbox page, home redirect |

---

## Open questions

| ID | Question | Analysis disposition |
|---|---|---|
| **OQ-01** | Nav label: **اعلان‌ها** vs **اعلان‌های من**? | **Recommend `اعلان‌ها`** in contract — freeze at contract stage |
| **OQ-02** | Nav visibility for principals without linked employee? | **Recommend Option A (show to all authenticated)** — freeze in contract; defer gating if product rejects |
| **OQ-03** | Contract required vs direct implementation? | **Contract required** — see governance recommendation |
| **OQ-04** | Include page-level entrypoints on request surfaces? | **Recommend no** — out of P7 scope |
| **OQ-05** | Nav ordering after **درخواست‌ها**? | **Recommend yes** — freeze in contract |
| **OQ-06** | Assert active state in tests? | **Recommend yes** — contract/lock test obligations |
| **OQ-07** | Use `wire:navigate` on layout nav link? | **Recommend no** — match existing layout nav for requests |

Items OQ-01, OQ-02, OQ-05, OQ-07 have **recommended resolutions** sufficient for contract drafting. OQ-02 product rejection would trigger a **separate governance item**, not P7 analysis blockage, if contract defaults to Option A.

---

## Relationship to predecessor features

### P2 — Read-only inbox list (CLOSED)

Created `notifications.index` and inbox presentation. **Deferred** layout navigation link. P7 is the **successor** that would supersede only the layout-nav deferral — not reopen P2 list behavior.

### P5 — Mark-read mutation (CLOSED)

Added mark-read on inbox rows. **Excluded** layout nav. P7 does not alter mark-read.

### P6 — Deep-link navigation (IMPLEMENTED_VERIFIED)

Added row-level navigation from inbox to `requests.show`. **Excluded** layout nav to inbox. P7 is the inverse direction (global chrome → inbox), not a P6 amendment.

### Deferred unread badge

Separate candidate. P7 must not consume `countUnread` or display badge UI. Layout nav link text only.

---

## Decision status

### Primary recommendation

**READY_FOR_CONTRACT**

### Rationale

Repository evidence confirms a **UI-only discoverability gap** on the shared layout surface. Route, page, and inbox behaviors (P2/P5/P6) are operational. Predecessor governance **explicitly deferred or excluded** layout navigation, requiring a **successor contract** before implementation. Recommended scope (layout nav only), label (**اعلان‌ها**), visibility (authenticated parity), and nav pattern are sufficiently constrained for contract drafting. No architecture or backend change is evidenced as required.

P7 is **not** blocked pending product decision if contract adopts recommended defaults (Option A visibility, layout-only scope). Product rejection of Option A would require a **follow-on governance item**, not reclassification of this analysis to blocked status.

---

Recommended next governance step: **feature contract** (`docs/ui/contracts/notifications/notification-inbox-layout-navigation.feature-contract.yaml`)
