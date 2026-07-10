# P9 — Notification Inbox Pagination — Feature Analysis

## Feature

| Field | Value |
|---|---|
| **Feature id** | `notification-inbox-pagination` |
| **Feature title** | P9 — Notification Inbox Pagination |
| **Domain** | notifications |
| **Analysis date** | 2026-07-10 |
| **Current gate** | `feature-analysis` |
| **Previous gate** | `repo-inspection` → `REPO_INSPECTION_COMPLETE_READY_FOR_FEATURE_ANALYSIS` |
| **Gap classification** | `MIXED_UI_AND_READ_MODEL_GAP` |

## Analysis objective

Determine whether notification inbox pagination beyond the current 50-item cap is a **valid, bounded successor feature** that should proceed to **review-decision**, and define governance-safe scope boundaries — without authorizing implementation, drafting contracts or locks, or selecting a final pagination implementation strategy.

---

## 1. Feature Identity

| Field | Value |
|---|---|
| **Feature id** | `notification-inbox-pagination` |
| **Title** | P9 — Notification Inbox Pagination |
| **Domain** | notifications / mixed presentation + read-model |
| **Current gate** | `feature-analysis` (this artifact) |
| **Previous gate result** | `REPO_INSPECTION_COMPLETE_READY_FOR_FEATURE_ANALYSIS` |
| **Target user problem** | Authenticated users with more than 50 delivered, non-archived notifications cannot view or act on notifications beyond the first 50 rows; the UI provides no pagination affordance and no indication that additional items exist |

---

## 2. Repository Evidence Summary

Condensed from `docs/ui/analysis/notifications/notification-inbox-pagination.repo-inspection.md` and triage — not a full restatement.

| Area | Confirmed fact |
|---|---|
| **Presentation** | `NotificationInboxPage` hard-codes `LIST_LIMIT = 50`; inbox Blade renders a flat table with no pagination controls, page state, or URL sync |
| **Application** | `NotificationInboxReadContract::listForRecipient($recipientEmployeeId, ?$unreadOnly, $limit)` returns `list<NotificationProjectionDto>` — limit only, no offset/cursor/page, no metadata envelope |
| **Infrastructure** | `NotificationRepository::listForRecipient()` filters delivered/non-archived rows for recipient, `orderByDesc('created_at')`, `limit($limit)`, `get()` |
| **Tests** | UI tests lock `listForRecipient(..., 50)` and cap display at 50; no multi-page or beyond-50 tests; P8 guard forbids `countUnread` in inbox page |
| **Governance** | P2 contract/lock **deferred** pagination beyond 50; P5–P8 explicitly **excluded** pagination; triage selected this slug after P8 closeout |
| **P8 separation** | Layout unread badge uses `LayoutNavUnreadBadgeComposer` + `countUnread()` on shared layout — separate from inbox list load path |

**Precedent (not requirement):** Request P4 implements paginated list via `listByEmployeePaginated`, `PaginatedRequestSummaryListDTO`, `forPage()`, and prev/next UI — **not present in Notification module**.

---

## 3. Current Capability Gap

**Confirmed gap classification:** `MIXED_UI_AND_READ_MODEL_GAP`

| Dimension | Current state | Gap |
|---|---|---|
| Fixed 50-item cap | UI and contract default to 50 | Users cannot access row 51+ |
| Reach beyond first set | Not possible via UI or read API | Older notifications exist in DB but are unreachable |
| Pagination state | Absent on Livewire component | No page/cursor transport |
| Pagination metadata | Absent on read contract | No `total`, `lastPage`, `currentPage`, or equivalent |
| Pagination controls | Absent in inbox Blade | No next/previous or page indicator |
| Offset / cursor / page support | Absent in contract and repository | Backend cannot return arbitrary slices |
| Truncation signal | Absent | UI does not indicate more items exist |

Pagination is **not** a presentation-only enhancement. Inspected read paths expose no paging parameters and no metadata; UI has no navigation beyond the first capped slice. Both layers must change for P9 to deliver value.

---

## 4. User Impact

### Problem for users

1. **Visibility ceiling** — Users see at most 50 notifications per inbox load, ordered newest first. Any notification older than the 50th visible row is **invisible** in the current UI.

2. **Silent truncation** — When more than 50 notifications exist, the UI shows a full table with **no affordance** that additional items exist. Users may incorrectly believe they have seen their complete history.

3. **Operational friction** — Users who need to find, mark-read, or deep-link to older notifications (P5/P6 capabilities on visible rows only) cannot reach those rows without a pagination or equivalent backend-paged navigation path.

4. **Mark-read does not solve reachability** — P5 mark-read operates on **currently rendered rows**. Marking items read on page 1 does not surface page-2 items into the first 50 without a paging model.

5. **Unread badge does not solve list reachability** — P8 layout badge shows **aggregate unread count** via `countUnread()`. It does not expose or navigate to older list rows. Badge and list pagination address different visibility problems.

### When impact is low

If a recipient has ≤50 delivered, non-archived notifications, current behavior is sufficient. P9 matters when inbox volume exceeds the cap — a realistic scenario for active employees over time.

---

## 5. Backend Impact Analysis

Analysis only — **no final pagination strategy selected**.

| Question | Assessment |
|---|---|
| Must the read contract evolve? | **Yes.** Current `listForRecipient` cannot express page boundaries or return pagination metadata. |
| New paginated method vs. changing existing signature? | **Open decision.** Adding a dedicated paginated read method may preserve backward compatibility for non-UI callers (`NotificationInboxTest`, retention tests) that use flat `listForRecipient`. Evolving the existing method risks breaking contract consumers. Repository evidence does not mandate one approach. |
| Must repository query support paging? | **Yes.** `limit()` + `get()` returns only the first N rows. Additional pages require offset, page index, or cursor semantics at the query layer. |
| Is metadata/envelope support needed? | **Yes** for page-navigation UX. UI needs backend-authoritative signals such as total count, current position, and whether more pages exist — analogous to P4's `PaginatedRequestSummaryListDTO` pattern, but Notification-specific. |
| Ordering stability | `orderByDesc('created_at')` is evidenced. **Tie-breaking is not explicit** (e.g., by `id`). Contract should consider whether stable ordering across pages requires a secondary sort key. |
| Is `countUnread()` relevant? | **Separate concern.** Aggregate unread count (P8) uses a different query path. Pagination metadata for the **list** is not satisfied by `countUnread()`. List `total` for paging may require a separate count or paginator metadata — not evidenced today. |

**Not in scope for backend (P9 analysis):** mark-read mutation changes, deep-link field changes, archive semantics changes, delivery pipeline changes.

---

## 6. UI Impact Analysis

Expected surface needs — **not final UI design**.

| Need | Rationale |
|---|---|
| Pagination controls or navigation affordance | Users must move beyond the first 50 rows when more exist |
| Page or cursor state on `NotificationInboxPage` | Transport layer for which slice to request |
| Distinction: empty inbox vs. end of list | Current `empty` state conflates zero total with zero rows on current page only |
| ≤50 total behavior | Controls should be hidden or disabled when a single page suffices |
| >50 total behavior | Controls visible; user can access additional slices |
| Mark-read refresh interaction | `markNotificationRead()` calls `refreshList()` today — reloads **page 1 only**. Paginated inbox must define whether mark-read refreshes **current page**, resets to page 1, or uses another backend-driven rule |
| URL state | **Consider at contract stage.** P4 uses Livewire `#[Url]` for `page`. Inbox pagination may benefit from shareable/restorable page state — open decision, not mandated by Notification repo evidence |

**Out of UI scope for P9 (analysis):** final Persian copy, exact control markup, page-size selector UX, infinite scroll.

---

## 7. Architecture Impact

### Required boundaries

| Rule | Status |
|---|---|
| Livewire/Blade must not access repository or Eloquent directly | **Must retain** — pagination queries stay in Application/Infrastructure |
| Pagination must go through Notification application read contract | **Required** — extend or add paginated read API on `NotificationInboxReadContract` |
| Notification module owns read model and query | **Required** — no cross-module read adapters for inbox list |
| P8 unread badge composer remains separate | **Required** — `LayoutNavUnreadBadgeComposer` on `components.layouts.app` unchanged by P9 unless explicitly scoped (not evidenced as needed) |
| `countUnread()` must not move into `NotificationInboxPage` | **Required** — P5/P8 architecture guard |
| No direct coupling to Request module | **Required** — P4 is internal precedent only; no Request imports in Notification presentation |

### Authority chain (target model — conceptual)

```
NotificationInboxPage
  → NotificationPrincipalEmployeeResolver
  → NotificationInboxReadContract (paginated list operation)
  → NotificationInboxReadService
  → NotificationRepositoryContract / NotificationRepository
  → paginated envelope DTO → Livewire state → Blade render
```

UI Anti-Leak: pagination **transport** (page index, next/prev) is presentation; **which rows belong on a page** and **total/last-page meaning** must be backend-authoritative.

---

## 8. Predecessor Feature Boundaries

### P9 may supersede

| Predecessor deferral | Supersession scope |
|---|---|
| **P2** pagination beyond 50-item presentation cap | **Only** the deferred pagination exclusion in P2 contract/lock |

### P9 must not reopen or alter

| Predecessor | Preserved delivery |
|---|---|
| **P2** read-only inbox baseline | List, empty/ready/error states, 50-item cap semantics **until P9 contract explicitly replaces cap-with-pagination model** |
| **P5** mark-read mutation | Per-row mark-read affordance and `MarkNotificationReadContract` delegation |
| **P6** deep-link navigation | Row `requests.show` deep-link binding |
| **P7** layout navigation | **اعلان‌ها** nav link, destination, order, active state |
| **P8** unread badge | Layout-nav aggregate unread badge via composer |

### Explicitly out of P9 scope

- Mark-all-as-read
- Unread-only filtering UI (contract supports `unreadOnly` parameter; UI does not use it today)
- Realtime / polling / reactive refresh of list or badge
- Notification search
- Notification sorting controls (ordering remains backend-defined unless separately governed)
- Notification archive management UI
- Request-list pagination (P4)
- Domain redesign, new routes, or new mutation types

---

## 9. Open Decisions

| ID | Decision | Classification |
|---|---|---|
| **OQ-P9-001** | Pagination strategy: offset/page-number vs cursor vs other | **Contract-blocking** |
| **OQ-P9-002** | Metadata envelope shape (`total`, `currentPage`, `lastPage`, `perPage`, `hasMore`, etc.) | **Contract-blocking** |
| **OQ-P9-003** | New paginated read method vs evolve `listForRecipient` signature | **Contract-blocking** |
| **OQ-P9-004** | Page size: retain 50 as `perPage` or adopt different fixed size | **Contract-blocking** |
| **OQ-P9-005** | Mark-read refresh behavior on paginated inbox (stay on page vs reset to page 1) | **Contract-blocking** |
| **OQ-P9-006** | URL-synced page state (`#[Url]` or equivalent) | **Contract-blocking** (yes/no for v1) |
| **OQ-P9-007** | Ordering tie-breaker beyond `created_at DESC` | **Contract-blocking** if cursor strategy; **non-blocking clarification** if offset/page |
| **OQ-P9-008** | Truncation/more-items indicator when additional pages exist | **Non-blocking clarification** (may follow from metadata + controls) |
| **OQ-P9-009** | Exact prev/next UI pattern and Persian labels | **Implementation-lock decision** |
| **OQ-P9-010** | Allowed file enumeration and test replacement for limit-50 assertions | **Implementation-lock decision** |
| **OQ-P9-011** | Whether API/non-UI consumers of `listForRecipient` must remain on flat list | **Non-blocking clarification** (informs OQ-P9-003) |

No open decision is a **repository ambiguity blocker** for proceeding to review-decision. Strategy choices are normal contract-stage work for a `MIXED_UI_AND_READ_MODEL_GAP`.

---

## 10. Risk Assessment

| Risk | Classification | Notes |
|---|---|---|
| Read contract breaking changes if `listForRecipient` signature evolves | **Non-blocking uncertainty** | Mitigated by dedicated paginated method (OQ-P9-003) |
| Existing tests assert `listForRecipient(..., 50)` and 50-row cap | **Non-blocking uncertainty** | Expected P9 successor test updates; not a blocker to review-decision |
| `created_at DESC` without tie-breaker may cause unstable page boundaries | **Non-blocking uncertainty** | Relevant for cursor and concurrent inserts; contract should address OQ-P9-007 |
| Mark-read `refreshList()` resets to first page today | **Non-blocking uncertainty** | Must be resolved in contract (OQ-P9-005); does not invalidate feature |
| P8 `countUnread` guard in inbox page source | **Non-blocking uncertainty** | Must retain; pagination must not introduce `countUnread` in `NotificationInboxPage` |
| Stale `docs/ui/analysis/feature-next-candidate.md` | **Stale artifact risk** | Directionally consistent; superseded by triage + repo-inspection |
| Stale P2 contract text on principal resolution | **Stale artifact risk** | P2 closed; resolver exists; does not block P9 |
| Client-side in-memory pagination anti-pattern | **Real blocker** (if attempted) | Forbidden by architecture; backend-paged read required — not a governance gate blocker because analysis already classifies mixed gap |
| P4 analogy over-applied to Notification | **Non-blocking uncertainty** | P4 informs envelope concept only; Notification has no filter/sort in scope |

**No real blocker** prevents `READY_FOR_REVIEW_DECISION`. **No repository ambiguity** blocks progression.

---

## 11. Recommended Next Governance Gate

| Gate | **`review-decision`** |
|---|---|

### Why this is the earliest valid next step

Repository facts are clear and the gap is classified. Feature-analysis confirms P9 is a **valid successor feature** superseding only P2's deferred pagination gap, with explicit boundaries against P5/P6/P7/P8 and excluded scope items. Open decisions (OQ-P9-001 through OQ-P9-011) are **appropriate for review-decision and contract stages**, not evidence of product rejection or repository ambiguity.

Review-decision should determine:

- Whether P9 proceeds to **feature-contract** (expected for `MIXED_UI_AND_READ_MODEL_GAP`, analogous to P4)
- Contract requirement before implementation
- Predecessor supersession confirmation
- Resolution ownership for contract-blocking open decisions

Do **not** recommend contract drafting, implementation lock, or implementation from this artifact.

**Not selected:** `blocked-needs-product-decision` — user problem and deferral lineage are evidenced; no product rejection trigger. **Not selected:** `blocked-by-repository-ambiguity` — repo-inspection found no ambiguity.

---

## 12. Final Analysis Decision

**`READY_FOR_REVIEW_DECISION`**

P9 — Notification Inbox Pagination is a **valid, bounded successor feature** addressing the P2-deferred pagination gap. It requires both read-model extension and inbox UI changes (`MIXED_UI_AND_READ_MODEL_GAP`). It does not reopen closed P2/P5/P6/P7/P8 deliveries beyond superseding the P2 pagination deferral. No repository or architecture blocker prevents review-decision.

### Summary table

| Field | Value |
|---|---|
| Valid feature candidate | **Yes** |
| Gap type | `MIXED_UI_AND_READ_MODEL_GAP` |
| Backend extension required | **Yes** |
| UI changes required | **Yes** |
| P8 badge separation | **Confirmed** |
| Predecessor supersession | P2 deferred pagination only |
| Next gate | `review-decision` |
| Implementation authorized | **No** |

---

*This artifact records feature analysis only. It does not approve the feature for contract drafting, select a pagination implementation strategy, or authorize code changes.*
