# P9 — Notification Inbox Pagination — Repository Inspection

## Document metadata

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-pagination` |
| **Feature title (working)** | P9 — Notification Inbox Pagination |
| **Domain area** | notifications |
| **Inspection date** | 2026-07-10 |
| **Inspection mode** | Repository inspection only — no solution design, no code changes |
| **Triage reference** | `docs/ui/review/governance-next-candidate-triage.md` (`NEXT_CANDIDATE_SELECTED`) |

---

## 1. Review Summary

This inspection reviewed the Notification module presentation, application, infrastructure, and test surfaces relevant to inbox list pagination, plus governance artifacts from P2 through P8 and the current governance triage record.

**Scope inspected:**

- `NotificationInboxPage` and `notification-inbox-page.blade.php`
- `NotificationInboxReadContract`, `NotificationInboxReadService`, `NotificationProjectionDto`
- `NotificationRepositoryContract`, `NotificationRepository`
- `NotificationPrincipalEmployeeResolver`, notification routes, shared layout nav (P7/P8 context)
- `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php`, `NotificationInboxTest.php`, related notification tests
- P2/P5/P6/P7/P8 governance contracts, locks, closeouts, and triage artifact

**Repository conclusion:** Notification inbox pagination **beyond the first capped result set is absent**. The inbox delivers a **fixed maximum of 50 rows** per load via `listForRecipient($employeeId, null, 50)`. There are **no pagination controls**, **no page/cursor state**, and **no pagination metadata envelope** in the Notification read path. Backend list queries support **limit only** with **stable `created_at` descending order**; **offset, cursor, page number, and total/last-page metadata are not implemented**.

P8 layout unread badge (`LayoutNavUnreadBadgeComposer`) is a **separate presentation surface** and does not implement or intersect with inbox list pagination.

This artifact records repository facts only. It does not authorize feature analysis conclusions, contract drafting, or implementation.

---

## 2. Current Repository Findings

### Presentation findings

| Finding | Evidence |
|---|---|
| Single inbox Livewire surface | `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` |
| Fixed list cap in component | `private const LIST_LIMIT = 50`; passed to `listForRecipient($employeeId, null, self::LIST_LIMIT)` |
| No pagination UI | `notification-inbox-page.blade.php` — table `@foreach ($notifications as $notification)` only; no page controls, no prev/next, no page indicator |
| No page/cursor Livewire state | No `$page`, `$perPage`, `#[Url]`, or `WithPagination` on `NotificationInboxPage` |
| UI states | `loading`, `empty`, `ready`, `error` — no paginated-empty or truncated-list state |
| Empty state | Generic copy when `$notifications === []` — no distinction between zero total vs. filtered-empty |
| Mark-read refresh | `markNotificationRead()` calls `refreshList()` — reloads first page only; no pagination position preserved |
| Route | `GET /` → `notifications.index` via `NotificationInboxPage` (`app/Modules/Notification/Presentation/Routes/web.php`) |
| Layout nav (P7) | **اعلان‌ها** link in `app.blade.php` — transport only; unrelated to inbox list paging |
| P8 unread badge | `LayoutNavUnreadBadgeComposer` on `components.layouts.app` — `countUnread` on layout render; **does not read inbox list rows or page state** |
| No shared pagination Blade component | No pagination partial under `resources/views/` for Notification module |

### Application findings

| Finding | Evidence |
|---|---|
| Read contract list signature | `NotificationInboxReadContract::listForRecipient(string $recipientEmployeeId, ?bool $unreadOnly = null, int $limit = 50): array` |
| Return shape | `list<NotificationProjectionDto>` — flat array; **no pagination envelope** |
| Optional unread filter | `$unreadOnly` supported on contract; **UI always passes `null`** (all delivered notifications) |
| No offset / page / cursor parameters | Contract signature has `limit` only |
| No total / lastPage metadata | `countUnread()` exists separately; not coupled to list paging |
| Service delegation | `NotificationInboxReadService` maps repository domain models → `NotificationProjectionDto` |
| Principal resolution | `NotificationPrincipalEmployeeResolver::requireEmployeeId()` in `refreshList` and `markNotificationRead` |
| No Notification paginated DTO | Unlike Request module `PaginatedRequestSummaryListDTO`, Notification has no paginated list DTO |

### Infrastructure findings

| Finding | Evidence |
|---|---|
| Repository list query | `NotificationRepository::listForRecipient()` — `where recipient`, `delivery_status = delivered`, `archived_at IS NULL`, optional `read_at IS NULL` when `$unreadOnly === true` |
| Ordering | `orderByDesc('created_at')` — **stable descending chronological order evidenced**; no explicit tie-break column |
| Limit handling | `->limit($limit)` then `get()` — **first-N slice only** |
| No offset | No `offset()`, `skip()`, or `forPage()` in Notification repository |
| No cursor | No cursor column or `cursorPaginate()` usage |
| No Laravel paginator | No `paginate()`, `simplePaginate()`, or `cursorPaginate()` in Notification module |
| Archive exclusion | Archived rows excluded from list — affects total countable set |
| `countUnread` query | Separate count query; same recipient/delivered/archive filters plus `read_at IS NULL` |

### Test findings

| Finding | Evidence |
|---|---|
| P2 UI flow — limit locked to 50 | `calls listForRecipient with the locked limit of 50` — expects `->with($employeeId, null, 50)` |
| P2 UI flow — cap assertion | `presents at most 50 inbox items` — mock returns 50 projections; component shows 50 |
| No pagination UI tests | No test asserts page controls, `goToPage`, or multi-page navigation in Notification tests |
| No beyond-50 truncation test | No test seeds 51+ DB rows and asserts 51st item inaccessible or truncation messaging |
| Backend list tests | `NotificationInboxTest.php` — list, unread filter, mark-read, cross-recipient, archive exclusion; uses default `listForRecipient` limit; **no pagination assertions** |
| P5 mark-read UI tests | Mock `listForRecipient` with limit 50 on refresh paths |
| P6 deep-link tests | Unchanged list behavior assumptions |
| P7 layout-nav tests | Layout transport only |
| P8 badge tests | Layout nav HTML; architecture guard forbids `countUnread` in `NotificationInboxPage` |
| Architecture guard (P5/P8) | `NotificationInboxUiFlowTest` guard block forbids `countUnread` in inbox page source — **retained constraint for P9** |
| Request module precedent (comparison only) | P4 uses `listByEmployeePaginated`, `PaginatedRequestSummaryListDTO`, `forPage()`, URL `page` state, prev/next UI in `request-list-page.blade.php` — **not present in Notification module** |

### Governance artifact findings

| Artifact | Pagination relevance |
|---|---|
| `docs/ui/review/governance-next-candidate-triage.md` | Selected `notification-inbox-pagination`; next gate was `repo-inspection` |
| P2 contract (`notification-inbox-read-only-list.feature-contract.yaml`) | `pagination.current_backend_capability: listForRecipient accepts limit (default 50); no offset or cursor`; **pagination deferred** beyond 50-item cap |
| P2 lock (`notification-inbox-read-only-list.implementation-lock.md`) | Pagination forbidden; limit `50` initial cap; introducing pagination requires separate governance |
| P5 contract/lock/reconciliation | Pagination explicitly **out of scope** |
| P6 contract/lock/closeout | Pagination **out of scope** |
| P7 contract/lock/closeout | Pagination **out of scope**; layout nav only |
| P8 contract/lock/closeout/verification | Pagination in `non_goals`; `NotificationInboxPage` frozen for P8; **P8 close does not prohibit a separate P9 pagination cycle** |
| `docs/ui/analysis/feature-next-candidate.md` | Stale — predates P7/P8 closeout; still lists pagination as deferred (directionally consistent) |

**No P9-specific analysis, contract, lock, or verification artifacts exist.**

---

## 3. Current List Semantics

| Dimension | Current behavior |
|---|---|
| **Fixed limit** | **Yes** — UI hard-codes `LIST_LIMIT = 50`; contract/repository default `limit = 50` |
| **Configurable limit (runtime)** | Contract/repository accept `$limit` parameter; **UI always passes 50** |
| **Offset support** | **Absent** |
| **Cursor support** | **Absent** |
| **Page-number support** | **Absent** |
| **Metadata / envelope** | **Absent** — returns flat `list<NotificationProjectionDto>`; no `total`, `currentPage`, `lastPage`, `perPage` |
| **Ordering** | `created_at DESC` on delivered, non-archived rows for recipient |
| **Unread filter (API)** | `?bool $unreadOnly` on contract/repository; UI passes `null` |
| **Reach beyond first displayed set** | **No** — records beyond the first 50 matching rows are **not retrievable** through current UI or read contract |
| **Truncation indicator** | **None** — user sees up to 50 rows with no “more items exist” affordance |
| **Mark-read interaction** | Mutations refresh full first-page load; no page position |

### Known constraints

- Delivered-status and non-archived filters apply before limit.
- `countUnread` is aggregate and independent of list page boundaries.
- P8 layout badge reflects aggregate unread on **full page load**, not inbox list page index.

### Absent capabilities (evidenced)

- Pagination controls on inbox page
- URL-synced page state
- Backend paginated read envelope for notifications
- Repository offset/cursor/page execution
- Tests proving multi-page behavior or beyond-50 access

---

## 4. Capability Classification

| Classification | Value | Rationale |
|---|---|---|
| **Backend capability** | **`BACKEND_EXTENSION_REQUIRED`** | Limit-only `listForRecipient` exists with stable ordering, but **pagination primitives (offset/cursor/page + metadata envelope) are absent**. Extending beyond the first 50 rows **requires public read-contract and repository changes** (P4 Request module shows an in-repo precedent pattern, but it is **not implemented** for Notification). |
| **UI capability** | **`UI_NOT_PRESENT`** | No pagination controls, no page state, no navigation beyond first result set on inbox page. |
| **Gap classification** | **`MIXED_UI_AND_READ_MODEL_GAP`** | Delivering inbox pagination requires **both** presentation changes (controls/state) **and** read-model/query extension (paginated envelope or equivalent). Not a presentation-only gap like P7/P8. |

---

## 5. Boundary and Ownership

### Authority chain (current inbox list load)

```
NotificationInboxPage::refreshList()
  → NotificationPrincipalEmployeeResolver::requireEmployeeId()
  → NotificationInboxReadContract::listForRecipient($employeeId, null, 50)
  → NotificationInboxReadService
  → NotificationRepositoryContract::listForRecipient(...)
  → NotificationRepository (Eloquent query + limit)
  → NotificationProjectionDto[] mapped in Livewire → Blade table render
```

### Module ownership

| Layer | Owner |
|---|---|
| Presentation | Notification — `NotificationInboxPage`, inbox Blade |
| Application | Notification — `NotificationInboxReadContract`, `NotificationInboxReadService`, DTOs |
| Infrastructure | Notification — `NotificationRepository`, `NotificationLogModel` |
| Layout badge (P8) | Notification — `LayoutNavUnreadBadgeComposer` on `components.layouts.app` (**separate from inbox list pagination**) |

### Surfaces likely affected (inspection observation only — not a design)

| Surface | Relevance |
|---|---|
| `NotificationInboxReadContract` | Public read API — pagination would extend signature or add paginated method |
| `NotificationRepositoryContract` / `NotificationRepository` | Query execution — offset/page/cursor |
| New or extended Application DTO | Pagination metadata envelope (Request `PaginatedRequestSummaryListDTO` is cross-module precedent only) |
| `NotificationInboxPage` | Page state, refresh semantics, mark-read reload behavior |
| `notification-inbox-page.blade.php` | Pagination controls and bounded empty states |
| `NotificationInboxUiFlowTest.php` | Limit-50 tests and guards would require P9-specific updates |

### Forbidden paths (current architecture)

- Direct `NotificationRepositoryContract` or Eloquent access from Livewire/Blade
- List-length or client-side slicing as authoritative pagination over full in-memory fetches (not evidenced today; would be anti-pattern if introduced without backend paging)
- `countUnread` consumption inside `NotificationInboxPage` (P5/P8 architecture guard)

### P8 unread badge separation

**Confirmed separate.** P8 badge uses layout view composer + `countUnread()` on shared layout render. Inbox pagination would operate on `NotificationInboxPage` list load path. No repository evidence shows badge logic depending on inbox page index or list slice. P9 pagination does not require altering P8 layout badge delivery unless future analysis explicitly couples them (not evidenced now).

---

## 6. Risks / Ambiguity

| Risk | Classification | Notes |
|---|---|---|
| Pagination strategy (offset vs cursor vs page number) undecided | **Non-blocking uncertainty** | Not implemented in repo; decision belongs to feature-analysis/contract, not inspection |
| Mark-read `refreshList()` resets to first page | **Non-blocking uncertainty** | Current behavior evidenced; P9 must address reload semantics in analysis |
| `created_at DESC` tie-breaking not explicit | **Non-blocking uncertainty** | Ordering exists; secondary key not documented in query |
| `unreadOnly` contract parameter unused by UI | **Non-blocking uncertainty** | May affect pagination scope if unread-only paging is requested later |
| P2/P5 tests lock `listForRecipient(..., 50)` | **Non-blocking uncertainty** | P9 will require test updates; expected successor supersession |
| P8 guard forbids `countUnread` in inbox page | **Non-blocking uncertainty** | Must retain; does not block pagination work |
| `docs/ui/analysis/feature-next-candidate.md` stale post-P8 | **Stale artifact risk** | Direction on pagination deferral still valid |
| P2 contract `principal_resolution: open_review` text | **Stale artifact risk** | `NotificationPrincipalEmployeeResolver` now exists and is used; reconciliation closed P2 |
| No in-repo Notification paginated DTO | **Non-blocking uncertainty** | Request P4 precedent exists; not ambiguity blocking inspection |
| Whether page size stays 50 or changes | **Non-blocking uncertainty** | Future governance decision |

**No real blocker** prevents proceeding to feature-analysis. **No repository ambiguity** blocks classification.

---

## 7. Recommended Next Governance Gate

| Gate | **`feature-analysis`** |
|---|---|

### Why this is the earliest valid next step

Repository facts are **sufficient and unambiguous**: pagination is absent, limit-only semantics are evidenced end-to-end, and the gap spans UI and read-model layers. Feature-analysis is required to classify scope boundaries, predecessor supersession (P2 deferred pagination only), interaction with P5 mark-read refresh, P8 badge separation, and whether P4 Request pagination patterns are an appropriate analogy — **without skipping to contract or lock**.

Do **not** recommend contract creation, implementation lock, or implementation from this inspection.

---

## 8. Final Decision

**`REPO_INSPECTION_COMPLETE_READY_FOR_FEATURE_ANALYSIS`**

---

## Inspection questions — evidence answers

| # | Question | Answer |
|---|---|---|
| 1 | Pagination controls on inbox UI? | **No** |
| 2 | Page/cursor state on inbox component? | **No** |
| 3 | Fixed display limit? | **Yes — 50** |
| 4 | Can UI reach records beyond first set? | **No** |
| 5 | P8 badge intersects inbox pagination? | **No evidenced intersection** |
| 6 | Read contract supports offset/cursor/page? | **No — limit only** |
| 7 | Pagination metadata returned? | **No** |
| 8 | Repository can fetch additional pages today? | **No — limit-only query** |
| 9 | Stable ordering for future paging? | **Yes — `created_at DESC` evidenced** |
| 10 | Tests assert fixed 50 limit? | **Yes** |
| 11 | Tests prove pagination absence? | **Implicitly — no pagination tests; limit 50 enforced** |
| 12 | Governance deferred pagination? | **Yes — P2 contract/lock; excluded P5–P8** |
| 13 | P9 artifacts exist? | **No** |
| 14 | Safe to proceed to feature-analysis? | **Yes** |

---

*This artifact records repository inspection only. It does not approve the feature, select a pagination strategy, or authorize implementation.*
