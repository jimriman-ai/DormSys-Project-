# P9 — Notification Inbox Pagination — Review Decision

## Feature

| Field | Value |
|---|---|
| **Feature id** | `notification-inbox-pagination` |
| **Feature title** | P9 — Notification Inbox Pagination |
| **Domain area** | notifications / mixed presentation + read-model |
| **Classification** | `MIXED_UI_AND_READ_MODEL_GAP` |
| **Decision date** | 2026-07-10 |
| **Previous gate** | `REPO_INSPECTION_COMPLETE_READY_FOR_FEATURE_ANALYSIS` → feature-analysis `READY_FOR_REVIEW_DECISION` |

## Review objective

Resolve contract-blocking architectural decisions for P9, confirm successor scope and predecessor boundaries, and determine readiness for **feature-contract** drafting. This review does **not** authorize implementation, draft a contract or lock, or modify application code or tests.

---

## 1. Feature Identity

| Field | Value |
|---|---|
| **Feature id** | `notification-inbox-pagination` |
| **Title** | P9 — Notification Inbox Pagination |
| **Domain** | notifications |
| **Current gate** | `review-decision` (this artifact) |
| **Previous gate result** | `REPO_INSPECTION_COMPLETE_READY_FOR_FEATURE_ANALYSIS` |
| **Target user problem** | Authenticated users with more than 50 delivered, non-archived notifications cannot reach older rows; the inbox shows no pagination affordance and no indication that additional items exist |

---

## 2. Repository Evidence Reviewed

### Inputs

| Artifact | Role |
|---|---|
| `docs/ui/analysis/notifications/notification-inbox-pagination.repo-inspection.md` | Primary repository truth |
| `docs/ui/review/governance-next-candidate-triage.md` | Candidate selection and P2–P8 boundary context |
| `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` | P2 deferred pagination |
| `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md` | P2 pagination prohibition |
| P5/P6/P7/P8 contracts, locks, closeouts | Predecessor exclusions and frozen deliveries |
| In-repo Request P4 precedent (`PaginatedRequestSummaryListDTO`, `listByEmployeePaginated`, `forPage()`, URL `page`) | Internal analogy only — not Notification implementation |

### Confirmed repository facts

| Assertion | Evidence | Confirmed |
|---|---|---|
| `NotificationInboxPage` hard-limits to 50 records | `private const LIST_LIMIT = 50`; `listForRecipient($employeeId, null, self::LIST_LIMIT)` | **Yes** |
| `NotificationInboxReadContract` is limit-only | `listForRecipient(..., int $limit = 50): array` — no offset, cursor, or page parameter | **Yes** |
| Repository has no pagination metadata | `NotificationRepository::listForRecipient()` — `orderByDesc('created_at')`, `limit($limit)`, `get()`; flat domain array return | **Yes** |
| UI has no pagination controls | `notification-inbox-page.blade.php` — flat `@foreach`; no page state, `#[Url]`, or `WithPagination` on component | **Yes** |
| Records beyond first 50 unreachable | No offset/cursor/page execution path in Notification read layer | **Yes** |
| P8 badge is separate | `LayoutNavUnreadBadgeComposer` on layout; architecture guard forbids `countUnread` in `NotificationInboxPage` | **Yes** |

No repository fact contradicts P9 as a valid successor feature. No invented capabilities are assumed.

---

## 3. Architecture Decision Summary

### A. Pagination strategy — **offset / page-number pagination**

| Option | Disposition |
|---|---|
| **Offset / page-number** | **Selected** |
| Cursor pagination | **Rejected for P9 v1** |

**Justification (repository evidence):**

1. Notification inbox is a **bounded per-recipient chronological list** with existing `orderByDesc('created_at')` ordering — not a high-velocity unbounded stream requiring cursor semantics.
2. **In-repo precedent** for governed UI list pagination uses page-number offset execution: Request `RequestReadQuery::listByEmployeePaginated()` applies `forPage($page, $perPage)` and returns `total`, `currentPage`, `lastPage` metadata; `RequestListPage` transports `page` via `#[Url]`.
3. Notification module has **no cursor infrastructure** (`cursorPaginate()`, cursor columns, or cursor contract parameters are absent).
4. P9 UI requires **total and last-page metadata** for prev/next affordances — naturally expressed by offset/page pagination.
5. Mark-read mutates `read_at` in place and does not remove rows from the delivered set; with an explicit ordering tie-breaker (see D), page boundaries remain sufficiently stable for v1.

Cursor pagination is not mandated by repository evidence and would introduce contract complexity without an evidenced requirement.

### B. Read contract evolution — **new paginated read method**

| Option | Disposition |
|---|---|
| Evolve existing `listForRecipient` signature | **Rejected** |
| Add new paginated read method on `NotificationInboxReadContract` | **Selected** |

**Rationale:**

- `listForRecipient` is **actively consumed** by `NotificationInboxPage`, `NotificationInboxTest`, P2/P5 UI flow tests, and P2/P5/P6 governance contracts referencing flat list refresh.
- P2 tests **lock** `listForRecipient($employeeId, null, 50)` expectations; changing the existing signature risks breaking non-UI consumers and complicates successor supersession.
- Request module precedent preserves `listByEmployee()` while adding `listByEmployeePaginated()` — parallel pattern is evidenced and appropriate for Notification.
- P9 inbox UI will consume the **new paginated method**; flat `listForRecipient` remains available for bounded non-paginated reads until a future governance cycle retires it.

Exact method name and query DTO shape are **contract-stage pins**; review approves the dual-method principle only.

### C. Response model — **pagination envelope DTO**

| Element | Decision |
|---|---|
| Flat `list<NotificationProjectionDto>` return for paginated path | **Rejected** |
| Dedicated paginated envelope DTO | **Required** |

The paginated read method must return a **backend-authoritative envelope** containing at minimum:

- `items` — `list<NotificationProjectionDto>` for the current page slice
- `total` — count of matching rows for the recipient query (delivered, non-archived, same filters as list)
- `currentPage` — clamped effective page index
- `perPage` — page size used for the query
- `lastPage` — derived last page boundary

This is analogous to Request `PaginatedRequestSummaryListDTO` but **Notification-specific** — no Request DTO reuse, no `statusOptions` or filter metadata (out of P9 scope). Implementation class naming is a contract/lock pin.

### D. Ordering guarantee — **stable descending chronological order with tie-breaker**

| Rule | Requirement |
|---|---|
| Primary sort | `created_at DESC` (evidenced current behavior) |
| Secondary tie-breaker | **`id DESC`** on notification primary key (UUID) — **required for P9** |

Repository evidence shows `orderByDesc('created_at')` only. Equal `created_at` timestamps can produce **unstable page boundaries** under offset pagination. Contract must require `orderByDesc('created_at')->orderByDesc('id')` (or equivalent) so page slices are deterministic across requests.

Filter scope for ordering and counting remains: recipient match, `delivery_status = delivered`, `archived_at IS NULL`, `unreadOnly` when applicable (UI continues to pass `null` in v1).

### E. Page size policy — **fixed contract-defined page size of 50**

| Policy | Decision |
|---|---|
| Fixed page size | **Selected — 50 per page** |
| User-configurable page size | **Rejected for v1** |
| Repository-defined variable limit | **Rejected for v1 UI** |

P2 established 50 as the presentation slice size. P9 converts the prior **total cap** into **per-page size** without changing the user-visible row count per page. Page size is **contract-frozen** (constant), not a runtime UI control.

### F. URL state — **page number belongs in URL state**

| Policy | Decision |
|---|---|
| URL-synced `page` parameter | **Required for v1** |
| Component-local page only | **Rejected** |

Request `RequestListPage` uses `#[Url(as: 'page', except: 1)]` for list page transport. P9 inbox pagination should follow the same **presentation transport pattern**: page index in URL for refresh/bookmark restoration, defaulting to page 1 when absent or invalid. Backend remains authoritative for clamping out-of-range page requests via envelope metadata.

### G. Mark-read interaction — **refresh current page after successful mark-read**

| Behavior | Decision |
|---|---|
| Reset to page 1 after mark-read | **Rejected** |
| Reload **current page** after mark-read | **Required** |

Current behavior reloads the first (and only) page via `refreshList()` after `markNotificationRead()`. P9 must preserve the user's **pagination position**: after successful mark-read, re-fetch the **same page number** so row read-state updates in place without displacing the user to page 1.

Mark-read mutation semantics (`MarkNotificationReadContract`) are **unchanged**. Only the post-mutation list reload transport changes from flat first-page load to paginated current-page reload.

Edge case (page becomes empty after future filtering — not in v1): contract may note that with `unreadOnly` unused in UI, mark-read does not remove rows from the current delivered list page.

### H. Unread badge interaction — **P8 remains completely independent**

| Constraint | Decision |
|---|---|
| `NotificationInboxPage` must not call `countUnread()` | **Mandatory — unchanged from P5/P8 guard** |
| Layout badge via `LayoutNavUnreadBadgeComposer` | **Unchanged by P9** |
| Inbox pagination metadata must not drive badge count | **Mandatory** |

P8 aggregate unread visibility on shared layout chrome is a **separate read path**. P9 list pagination operates only on the inbox paginated list method. No coupling between page index, list slice, and layout badge refresh is authorized.

---

## 4. Decision Matrix

| ID | Decision area | Resolution | Gate |
|---|---|---|---|
| RD-P9-001 | Pagination strategy | Offset / page-number (`forPage` pattern) | Review-decision |
| RD-P9-002 | Read contract shape | New paginated method; retain flat `listForRecipient` | Review-decision |
| RD-P9-003 | Response model | Paginated envelope DTO with items + metadata | Review-decision |
| RD-P9-004 | Ordering | `created_at DESC`, `id DESC` tie-breaker | Review-decision |
| RD-P9-005 | Page size | Fixed 50 per page, contract-defined | Review-decision |
| RD-P9-006 | URL state | `page` in URL via Livewire URL binding | Review-decision |
| RD-P9-007 | Mark-read reload | Refresh current page, not page 1 | Review-decision |
| RD-P9-008 | P8 badge boundary | Fully independent; no `countUnread` in inbox page | Review-decision |
| RD-P9-009 | Exact method/DTO names | Contract-stage pin | Contract |
| RD-P9-010 | Prev/next UI markup and Persian copy | Lock-stage pin | Lock |
| RD-P9-011 | Successor test replacement for limit-50 assertions | Lock/implementation | Lock |

All contract-blocking decisions from feature-analysis are **resolved at this review**. No item blocks contract drafting.

---

## 5. Scope Confirmation

### Approved scope (for contract drafting)

| Dimension | Boundary |
|---|---|
| **Feature type** | Mixed UI + read-model successor |
| **Supersedes** | P2 deferred pagination beyond first 50 notifications **only** |
| **Primary surfaces** | `NotificationInboxReadContract` (new paginated method), repository query execution, `NotificationInboxPage`, `notification-inbox-page.blade.php` |
| **User capability** | Navigate pages when total delivered notifications exceed 50; access rows beyond the first page |
| **Pagination UX (v1)** | Backend-paged prev/next (or equivalent page navigation) when `lastPage > 1`; hidden/disabled when single page |
| **Page size** | Fixed 50 per page |
| **Ordering** | Backend `created_at DESC`, `id DESC` |
| **URL** | Page number in URL state |
| **Mark-read** | Unchanged mutation; post-success reload of current page |
| **Empty state** | Zero total notifications — distinct from paginated ready state with rows |

### Explicitly excluded from P9

| Item | Disposition |
|---|---|
| Mark-all-as-read | **Rejected** |
| Unread-only filtering UI | **Rejected** — contract parameter exists; UI not in scope |
| Realtime / polling / websocket refresh | **Rejected** |
| Notification search | **Rejected** |
| Notification sorting controls | **Rejected** — ordering backend-fixed |
| Notification archive management UI | **Rejected** |
| Request-list pagination changes | **Rejected** |
| Notification domain redesign | **Rejected** |
| New API endpoints | **Rejected** |
| P8 layout badge changes | **Rejected** |
| SPA architecture | **Rejected** |
| Backend mutation expansion beyond existing mark-read | **Rejected** |
| `countUnread()` in inbox page | **Rejected** |

---

## 6. Preserved Predecessor Boundaries

| Predecessor | Preserved delivery | P9 impact |
|---|---|---|
| **P2** read-only inbox | List, states, projection mapping, principal resolution | **Successor supersession** of pagination deferral only; per-page row rendering and DTO mapping preserved |
| **P5** mark-read | `MarkNotificationReadContract`, per-row affordance, flash feedback | **Preserved** — reload transport becomes paginated current-page only |
| **P6** deep-link | Row `request_show_url` binding | **Preserved** — unchanged on paginated rows |
| **P7** layout nav | **اعلان‌ها** link, destination, order, active state | **Preserved** — no nav semantic change |
| **P8** unread badge | Layout composer + `countUnread()` | **Preserved** — fully independent of inbox pagination |

**Closed features are not reopened.** P9 does not amend P2/P5/P6/P7/P8 contracts in place; successor supersession is recorded in the P9 contract only.

---

## 7. Required Contract Inputs

The feature contract **must** freeze:

| Input | Source decision |
|---|---|
| New paginated read method on `NotificationInboxReadContract` | RD-P9-002 |
| Paginated envelope DTO fields: `items`, `total`, `currentPage`, `perPage`, `lastPage` | RD-P9-003 |
| Repository `forPage(page, perPage)` execution with count query | RD-P9-001 |
| Ordering: `created_at DESC`, `id DESC` | RD-P9-004 |
| Fixed `perPage = 50` | RD-P9-005 |
| `NotificationInboxPage` URL `page` state and clamping from backend envelope | RD-P9-006 |
| Post mark-read: reload current page via paginated method | RD-P9-007 |
| Hide pagination controls when `lastPage <= 1` | RD-P9-005 / scope |
| Empty inbox when `total === 0` (not conflated with page boundary) | Scope |
| Forbidden: `countUnread` in inbox page; direct repository access | RD-P9-008 / architecture |
| P2 pagination deferral supersession boundary | Scope |
| P5/P6/P7/P8 non-reopening and preserved behaviors | §6 |
| Non-goals: mark-all, filter UI, search, sort, realtime, archive UI, API, badge changes | §5 |
| Successor test expectations replacing P2 limit-50-only assertions | RD-P9-011 |
| Allowed/forbidden file enumeration | Lock precursor |

Contract **must not** mandate copying Request P4 filter/sort DTOs or `statusOptions` delivery.

---

## 8. Risks

| Risk | Classification | Mitigation |
|---|---|---|
| Breaking `listForRecipient` consumers if signature changed | **Non-blocking** | New paginated method; flat method retained (RD-P9-002) |
| P2/P5 UI tests assert `listForRecipient(..., 50)` only | **Non-blocking** | Authorized successor test updates in lock/implementation |
| Unstable ordering without `id` tie-breaker | **Non-blocking** | Contract requires secondary sort (RD-P9-004) |
| Mark-read reload resets user to page 1 | **Non-blocking** | Contract requires current-page reload (RD-P9-007) |
| Accidental `countUnread` in inbox page | **Non-blocking** | Retain P5/P8 architecture guard; contract forbids |
| P8 badge coupled to list page | **Non-blocking** | Explicit independence decision (RD-P9-008) |
| Client-side in-memory pagination over full fetch | **Real anti-pattern** | Contract must require backend-paged query execution |
| Stale `docs/ui/analysis/feature-next-candidate.md` | **Stale artifact** | Directionally consistent; triage is authoritative |
| P4 precedent over-applied (filter/sort/statusOptions) | **Non-blocking** | Pagination envelope analogy only; Notification scope narrower |

**No real blocker** prevents contract drafting. **No repository ambiguity** requires re-inspection.

---

## 9. Final Decision

**`READY_FOR_CONTRACT`**

P9 — Notification Inbox Pagination is approved to proceed to **feature-contract** drafting. Repository evidence is sufficient. Contract-blocking architectural decisions are resolved. Scope is bounded as a mixed UI + read-model successor superseding only P2's deferred pagination gap. Predecessor deliveries P2 (baseline), P5, P6, P7, and P8 remain preserved. Implementation is **not** authorized by this decision.

---

## 10. Next Governance Gate

| Gate | **`feature-contract`** |
|---|---|

**Next authorized artifact:**

`docs/ui/contracts/notifications/notification-inbox-pagination.feature-contract.yaml`

Contract drafting must encode decisions RD-P9-001 through RD-P9-008 and required contract inputs from §7. Lock drafting and implementation remain **unauthorized** until contract review completes per governance pipeline.

---

*This review decision authorizes feature-contract drafting only. It does not authorize implementation, lock drafting, or modification of application code or tests.*
