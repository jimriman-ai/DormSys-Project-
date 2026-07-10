# P9 — Notification Inbox Pagination — Implementation Lock

## Feature Identity

| Field | Value |
|---|---|
| **Feature id** | `notification-inbox-pagination` |
| **Feature title** | P9 — Notification Inbox Pagination |
| **Module** | Notification |
| **Area** | notifications |
| **Classification** | successor-feature |
| **Gap type** | `MIXED_UI_AND_READ_MODEL_GAP` |
| **Version** | `0.1.0` |
| **Lock date** | 2026-07-10 |

---

## Approved Source Artifacts

| Artifact | Role |
|---|---|
| `.specify/governance/_meta/authority-model.md` | Authority vocabulary — this lock is **not** Implementation Authorization |
| `docs/ui/analysis/notifications/notification-inbox-pagination.repo-inspection.md` | Repository truth |
| `docs/ui/analysis/notifications/notification-inbox-pagination.feature-analysis.md` | Feature-analysis conclusions |
| `docs/ui/review/notifications/notification-inbox-pagination.review-decision.md` | Architectural decisions RD-P9-001–008 |
| `docs/ui/contracts/notifications/notification-inbox-pagination.feature-contract.yaml` | Governing contract |
| `docs/ui/review/notifications/notification-inbox-pagination.contract-review.md` | Contract review — `APPROVED_FOR_LOCK_DRAFTING` |

---

## Lock Status

| Field | Value |
|---|---|
| **Final lock decision** | **`LOCK_READY_FOR_REVIEW`** |
| **Coding authorized?** | **No** — coding remains forbidden until lock-review approval |
| **Implementation authorized?** | **No** |
| **Next gate** | `lock-review` |

This artifact freezes implementation constraints only. It does **not** grant Implementation Authorization under the authority model.

---

## Implementation Boundary

P9 is a **mixed UI + read-model** successor that:

1. Adds backend-authoritative **offset / page-number** pagination to the notification inbox read path.
2. Switches `NotificationInboxPage` primary list load and post mark-read refresh to the new paginated read method.
3. Preserves flat `listForRecipient` for non-inbox consumers.
4. Supersedes **only** the P2 deferred pagination-beyond-50 exclusion (and the P5 inbox reload transport on `NotificationInboxPage` only, as already frozen in the contract).

No SPA, API, Request-module, badge, filter, search, sort, realtime, archive, mark-all, or domain redesign work is authorized.

---

## Allowed Files / Areas

### Production — may create or modify

| Path | Constraint |
|---|---|
| `app/Modules/Notification/Application/Contracts/NotificationInboxReadContract.php` | Add `listForRecipientPaginated(NotificationInboxListQueryDTO $query): PaginatedNotificationInboxListDTO`. **Do not** change `listForRecipient`, `findByIdForRecipient`, or `countUnread` signatures/semantics. |
| `app/Modules/Notification/Application/Services/NotificationInboxReadService.php` | Implement `listForRecipientPaginated`; map repository page slice → `NotificationProjectionDto` items; compute/pass through envelope metadata. Preserve existing `listForRecipient` behavior. |
| `app/Modules/Notification/Application/Contracts/NotificationRepositoryContract.php` | Add paginated list method (see Interface / Read-Contract Changes). Preserve `listForRecipient` and `countUnread`. |
| `app/Modules/Notification/Infrastructure/Repositories/NotificationRepository.php` | Implement paginated query: same recipient/delivered/non-archived filters; `orderByDesc('created_at')->orderByDesc('id')`; count + `forPage($page, $perPage)`. Preserve existing `listForRecipient` (may add `id DESC` tie-breaker there for consistency — optional, not required for flat consumers). |
| `app/Modules/Notification/Application/DTOs/NotificationInboxListQueryDTO.php` | **New** readonly query DTO (see DTO constraints). |
| `app/Modules/Notification/Application/DTOs/PaginatedNotificationInboxListDTO.php` | **New** readonly envelope DTO (see DTO constraints). |
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Replace flat `listForRecipient(..., 50)` load with `listForRecipientPaginated`; add URL-bound `page` state and pagination metadata properties; post mark-read refresh must reload **current page**; remove hard total-cap semantics (`LIST_LIMIT` as total ceiling). Preserve P5 mark-read mutation delegation and P6 deep-link row mapping. **Forbidden:** `countUnread`, repository/Eloquent access. |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Add prev/next pagination controls when `lastPage > 1`; hide when `total === 0` or `lastPage <= 1`. Preserve existing columns, empty/ready/error states, P5 mark-read affordance, P6 deep-link. |

### Tests — may create or modify (later implementation gate only)

| Path | Constraint |
|---|---|
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Replace P2 inbox `listForRecipient(..., 50)` expectations with paginated-method expectations; add pagination UI/state/mark-read current-page tests; **retain** architecture guard forbidding `countUnread` in `NotificationInboxPage`. |
| `tests/Feature/Modules/Notification/NotificationInboxTest.php` | **Add** coverage for `listForRecipientPaginated` envelope, ordering, and page slicing. **Do not** break or remove existing `listForRecipient` consumer tests. |

### Allowed to inspect (read-only)

| Path | Purpose |
|---|---|
| `app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php` | Row projection shape — **do not modify** |
| `app/Modules/Notification/Application/Services/NotificationPrincipalEmployeeResolver.php` | Principal scoping — **do not modify** |
| `app/Modules/Notification/Application/Contracts/MarkNotificationReadContract.php` | Mark-read delegation — **do not modify** |
| `app/Modules/Notification/Presentation/View/Composers/LayoutNavUnreadBadgeComposer.php` | P8 boundary evidence — **do not modify** |
| `resources/views/components/layouts/app.blade.php` | P7/P8 layout evidence — **do not modify** |
| `resources/views/livewire/request/request-list-page.blade.php` | Pagination UI precedent (labels/pattern) — **do not modify** |
| `app/Modules/Request/Presentation/Livewire/RequestListPage.php` | URL page / `goToPage` precedent — **do not modify**; no Request imports in Notification |

**File-scope rule:** No other production, test, route, provider, migration, or governance files may be modified under this lock.

---

## Forbidden Files / Areas

### Must not modify

- `app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php`
- `app/Modules/Notification/Application/Contracts/MarkNotificationReadContract.php`
- `app/Modules/Notification/Application/Services/MarkNotificationReadAction.php`
- `app/Modules/Notification/Application/Services/NotificationPrincipalEmployeeResolver.php`
- `app/Modules/Notification/Presentation/View/Composers/LayoutNavUnreadBadgeComposer.php`
- `app/Modules/Notification/Presentation/Providers/NotificationPresentationServiceProvider.php`
- `resources/views/components/layouts/app.blade.php`
- `app/Modules/Notification/Presentation/Routes/web.php`
- `app/Modules/Notification/Domain/**` (except no domain redesign; do not touch)
- `app/Modules/Notification/Infrastructure/Persistence/**`
- `app/Modules/Request/**`
- `routes/web.php`, `routes/api.php`
- `database/migrations/**`
- `bootstrap/providers.php` (no P9 provider changes required)
- P2/P5/P6/P7/P8 contracts, locks, closeouts (do not amend in place)

### Must not create

- New API/HTTP controllers or routes for inbox pagination
- Shared cross-module pagination package coupling Notification to Request DTOs
- SPA / client-router layers
- Realtime / polling / websocket / SSE / `wire:poll` refresh for list or badge
- Mark-all-as-read surfaces
- Filter / search / sort UI controls
- Archive management UI
- Second unread-badge surface on inbox page

---

## Required Behavior

| Rule ID | Requirement |
|---|---|
| BEH-P9-001 | Inbox primary list load uses `NotificationInboxReadContract::listForRecipientPaginated` only — not `listForRecipient`. |
| BEH-P9-002 | Flat `listForRecipient` signature and semantics remain unchanged for non-inbox consumers. |
| BEH-P9-003 | Pagination strategy is **offset / page-number** via repository `forPage($page, $perPage)` plus total count. Cursor pagination is forbidden. |
| BEH-P9-004 | Every paginated response returns envelope fields: `items`, `total`, `currentPage`, `perPage`, `lastPage`. |
| BEH-P9-005 | Pagination metadata is **backend-authoritative**. UI must not compute `total` or `lastPage` from visible rows. |
| BEH-P9-006 | Ordering is `created_at DESC`, then `id DESC`, applied before count and `forPage`. |
| BEH-P9-007 | Fixed page size is **50**. Not user-configurable. |
| BEH-P9-008 | Default page is **1**. Livewire URL state represents current page (`#[Url(as: 'page', except: 1)]` or equivalent). Page 1 omitted from URL when Livewire conventions support `except: 1`. |
| BEH-P9-009 | Out-of-range pages are clamped by backend (to `lastPage` when `total > 0`; to `1` when `total === 0`). UI adopts envelope `currentPage` after load. |
| BEH-P9-010 | Pagination controls render only when `lastPage > 1`. Hidden when `total === 0` or `lastPage <= 1`. |
| BEH-P9-011 | Empty inbox (`total === 0`) uses existing empty UI state — distinct from paginated ready with rows. |
| BEH-P9-012 | After successful mark-read, reload **current page** via `listForRecipientPaginated`. **Do not** reset to page 1. |
| BEH-P9-013 | Mark-read mutation semantics (`MarkNotificationReadContract`) remain unchanged. |
| BEH-P9-014 | P6 deep-link and P5 mark-read affordances remain on paginated rows. |
| BEH-P9-015 | P7 layout nav and P8 unread badge remain unchanged and independent. |
| BEH-P9-016 | UI must not fetch the full recipient set and paginate in memory. |
| BEH-P9-017 | Inbox UI continues to pass `unreadOnly = null` (no unread-only filter UI). |

### Pagination UI pins (lock-stage)

| Element | Locked value |
|---|---|
| Pattern | Previous / next page navigation (Request list precedent) |
| Previous label | **قبلی** |
| Next label | **بعدی** |
| Page indicator | **صفحه {{ $page }} از {{ $lastPage }}** (or equivalent consuming backend `page` / `lastPage`) |
| Navigation method | Livewire `goToPage(int $page)` (or equivalent) that sets page ≥ 1 and reloads via paginated read |

---

## Interface / Read-Contract Changes

### `NotificationInboxReadContract`

```
listForRecipientPaginated(
    NotificationInboxListQueryDTO $query
): PaginatedNotificationInboxListDTO
```

Preserved unchanged:

- `listForRecipient(string $recipientEmployeeId, ?bool $unreadOnly = null, int $limit = 50): array`
- `findByIdForRecipient(...)`
- `countUnread(string $recipientEmployeeId): int`

### `NotificationRepositoryContract`

Add a paginated list method that:

- Accepts recipient employee id, optional `unreadOnly`, `page`, `perPage`
- Applies filters: recipient, `delivery_status = delivered`, `archived_at IS NULL`, optional unread
- Orders: `created_at DESC`, `id DESC`
- Returns page slice of domain `Notification` models plus `total` count

Exact repository method name may be `listForRecipientPaginated`; return shape may be a small Application/Infrastructure result object or an associative structure consumed only by `NotificationInboxReadService`. Lock forbids returning Application projection DTOs from repository if that would reverse current Domain→Application mapping direction — service remains responsible for `NotificationProjectionDto` mapping.

### `NotificationInboxListQueryDTO` (new)

Frozen fields:

| Field | Type | Rules |
|---|---|---|
| `recipientEmployeeId` | `string` | Required; from `NotificationPrincipalEmployeeResolver::requireEmployeeId` |
| `unreadOnly` | `?bool` | Default `null`; inbox UI always passes `null` in v1 |
| `page` | `int` | Default `1`; minimum `1` |
| `perPage` | `int` | Fixed **50** for inbox UI |

---

## DTO / Envelope Constraints

### `PaginatedNotificationInboxListDTO` (new)

Frozen public properties:

| Property | Type | Meaning |
|---|---|---|
| `items` | `list<NotificationProjectionDto>` | Current page slice |
| `total` | `int` | Matching row count (same filters as list) |
| `currentPage` | `int` | Clamped effective page |
| `perPage` | `int` | Page size used (50) |
| `lastPage` | `int` | Derived last page; `1` when total is 0 |

**Forbidden in envelope:** Request-style `statusOptions`, filter metadata, sort metadata, or Request DTO reuse.

**Forbidden:** Flat `list<NotificationProjectionDto>` as the sole return type of the paginated method.

---

## UI State Constraints

| State | Rules |
|---|---|
| `page` | Positive integer; default `1`; URL-synced; minimum `1` |
| `total` | From envelope only |
| `lastPage` | From envelope only |
| `perPage` | From envelope; fixed 50 |
| `notifications` | Mapped from envelope `items` for Blade (existing row shape preserved) |
| `uiState` | `loading` / `empty` / `ready` / `error` — empty when `total === 0` |

Livewire must **not**:

- Call `countUnread`
- Import or resolve `NotificationRepositoryContract`
- Use Eloquent / `DB` / `notification_logs`
- Import Request module classes
- Use `WithPagination` trait as a substitute for the governed Application envelope (if Livewire pagination helpers are used, they must still consume backend envelope metadata and must not become an alternate authority)

---

## Mark-Read Interaction Constraints

| Rule | Requirement |
|---|---|
| Mutation | Unchanged — `MarkNotificationReadContract::markRead` only |
| Post-success refresh | `listForRecipientPaginated` with **same** `page` as before mutation |
| Forbidden | Reset `page` to `1` after mark-read success |
| Forbidden | Optimistic local `is_read` flip without backend paginated reload |
| Affordance | P5 unread-only gating preserved |

---

## P8 Unread Badge Constraints

| Rule ID | Requirement |
|---|---|
| BADGE-LOCK-P9-001 | Do not modify `LayoutNavUnreadBadgeComposer` or layout badge markup |
| BADGE-LOCK-P9-002 | `NotificationInboxPage` must not call, reference, or import `countUnread` |
| BADGE-LOCK-P9-003 | Inbox Blade must not derive unread aggregate from visible rows or pagination metadata |
| BADGE-LOCK-P9-004 | Pagination metadata must not drive badge count |
| BADGE-LOCK-P9-005 | Retain existing UI-flow architecture guard asserting no `countUnread` in inbox page source |

---

## Predecessor Preservation

| Predecessor | Lock treatment |
|---|---|
| **P2** | Supersede **only** deferred pagination beyond 50-item cap. Preserve list rendering, states, projection mapping, principal resolution. Convert total cap into per-page size 50 with multi-page navigation. |
| **P5** | Preserve mark-read affordance and mutation. Supersede only post-mutation list reload transport → paginated current-page reload. |
| **P6** | Preserve deep-link `request_show_url` / `requests.show` binding on paginated rows. |
| **P7** | Preserve **اعلان‌ها** nav link, destination, order, active state. |
| **P8** | Preserve layout unread badge architecture; fully independent. |

Do **not** reopen or amend P2/P5/P6/P7/P8 contracts, locks, or closeouts in place.

---

## Acceptance Criteria Mapping

| Contract AC (summary) | Lock coverage |
|---|---|
| Inbox uses `listForRecipientPaginated` | BEH-P9-001; allowed Livewire + contract files |
| Access beyond first 50 via navigation | BEH-P9-003, BEH-P9-010; Blade controls |
| ≤50 items per page | BEH-P9-007 |
| Envelope fields present | BEH-P9-004; DTO constraints |
| Controls only when `lastPage > 1` | BEH-P9-010 |
| Empty when `total === 0` | BEH-P9-011 |
| URL page restore + clamping | BEH-P9-008, BEH-P9-009 |
| Ordering `created_at DESC`, `id DESC` | BEH-P9-006 |
| Mark-read keeps current page | BEH-P9-012 |
| `listForRecipient` preserved | BEH-P9-002 |
| P5/P6/P7/P8 preserved | Predecessor + badge sections |
| No in-memory pagination | BEH-P9-016 |
| No `countUnread` in inbox page | BADGE-LOCK-P9-002 |
| No UI repository/Eloquent | Architecture boundaries + forbidden files |

---

## Test Obligations (Later Implementation Gate)

Define coverage now; **do not write tests under this lock**.

### Required — `NotificationInboxUiFlowTest.php`

| Obligation | Intent |
|---|---|
| Inbox load mocks/calls `listForRecipientPaginated`, not `listForRecipient` | BEH-P9-001 |
| Default load is page 1, at most 50 rows | BEH-P9-007/008 |
| When total > 50, navigation reaches later pages / rows beyond first 50 | BEH-P9-003/010 |
| Pagination controls hidden when `lastPage <= 1` or total ≤ 50 | BEH-P9-010 |
| URL `page` restores paginated slice | BEH-P9-008 |
| Mark-read success reloads current page (not page 1) | BEH-P9-012 |
| Architecture guard: no `countUnread` in `NotificationInboxPage.php` | BADGE-LOCK-P9-005 |
| Architecture guard: no repository/Eloquent access in inbox Livewire/Blade | Anti-leak |

### Required — `NotificationInboxTest.php` (additive)

| Obligation | Intent |
|---|---|
| `listForRecipientPaginated` returns envelope with items/total/currentPage/perPage/lastPage | BEH-P9-004 |
| Ordering is `created_at DESC`, `id DESC` across pages | BEH-P9-006 |
| Page 2 returns distinct slice when total > 50 | BEH-P9-003 |
| Existing `listForRecipient` tests remain green / compatible | BEH-P9-002 |

### Must not require

- P8 layout badge behavior changes
- Request module test or production changes
- New API endpoint tests
- Mark-all / filter / search / sort / realtime scenarios

---

## Architecture Boundaries (Summary)

| Boundary | Status |
|---|---|
| Thin Livewire/Blade | Required |
| Notification module owns read query | Required |
| No UI → repository | Required |
| No UI → Eloquent / DB | Required |
| No Request module coupling | Required |
| No `countUnread` in inbox page | Required |
| P8 badge independent | Required |
| Backend-authoritative metadata | Required |

---

## Rollback / Abort Conditions

Abort implementation and return to governance if any of the following appear necessary:

1. Changing `listForRecipient` signature or removing it
2. Changing `MarkNotificationReadContract` or mark-read domain semantics
3. Introducing cursor pagination or configurable page size
4. Coupling Notification pagination to Request DTOs/classes
5. Calling `countUnread` from `NotificationInboxPage` or inbox Blade
6. Modifying P8 layout badge delivery
7. Adding API routes, SPA layers, realtime refresh, mark-all, filter/search/sort UI, or archive UI
8. Client-side pagination over a full in-memory fetch
9. Schema/migration changes for pagination
10. Scope expansion beyond allowed files

---

## Final Lock Decision

**`LOCK_READY_FOR_REVIEW`**

The lock is complete, aligned with the approved contract and contract-review, and ready for lock-review. Coding and tests remain unauthorized.

---

## Next Governance Gate

| Field | Value |
|---|---|
| **Next gate** | `lock-review` |
| **Expected review artifact** | `docs/ui/review/notifications/notification-inbox-pagination.implementation-lock-review.md` (or repository-equivalent) |
| **Not authorized** | Source code, tests, implementation, contract edits |

---

*This implementation lock freezes constraints for lock-review only. It does not authorize coding under the DormSys authority model.*
