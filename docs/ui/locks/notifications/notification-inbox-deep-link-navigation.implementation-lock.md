# Notification Inbox Deep-Link Navigation Implementation Lock

## Feature

| Field | Value |
|---|---|
| **Feature key** | `notification-inbox-deep-link-navigation` |
| **Title** | P6 — Notification Inbox Deep-Link Navigation |
| **Governing contract** | `docs/ui/contracts/notifications/notification-inbox-deep-link-navigation.feature-contract.yaml` |
| **Review decision** | `docs/ui/decisions/notifications/notification-inbox-deep-link-navigation.review-decision.md` |
| **Classification** | Successor overlay on `NotificationInboxPage` (`notifications.index`) |

## Lock Status

**APPROVED_FOR_IMPLEMENTATION**

Pre-lock contract review completed with **no contract drift** against the review decision. Implementation may proceed only within the boundaries defined in this lock.

## Contract Review

| Confirmation | Result |
|---|---|
| Contract status is `READY_FOR_IMPLEMENTATION_LOCK` | **Pass** — `contract_status.value: READY_FOR_IMPLEMENTATION_LOCK` |
| Surface limited to `NotificationInboxPage` / `notifications.index` | **Pass** |
| Allowlist is exactly `requests.show` | **Pass** — `allowlist.v1` contains only `requests.show` |
| Binding is `requests.show` with `requestId: entityId` | **Pass** — `route_binding_rules.binding` frozen |
| Eligibility requires `deepLinkRoute === 'requests.show'`, non-null `entityId`, resolvable web route | **Pass** — `scope.eligibility` / `ELIG-DL-001` |
| Non-eligible rows remain display-only | **Pass** — `ui_behavior_rules.non_eligible_row` |
| P5 mark-read behavior unchanged | **Pass** — `mark_read_preservation` / `UIR-DL-005` |
| P2 and P5 remain closed | **Pass** — `supersession_boundary.p2_status` / `p5_status: CLOSED` |
| No backend, DTO, projection, route, schema, or service changes authorized | **Pass** — `scope.out_of_scope`, `architecture_constraints.backend` |

**Drift items:** None.

## Approved Implementation Scope

Implementation is authorized to:

1. Extend `NotificationInboxPage::mapProjectionRow()` to pass through `deepLinkRoute` and `entityId` from existing `NotificationProjectionDto` (currently omitted).
2. Add presentation-only eligibility for navigation when **all** are true:
   - `deepLinkRoute === 'requests.show'`
   - `entityId` is non-null
3. Render a **dedicated text link** in the existing **عملیات** column for eligible rows only.
4. Generate destination **only** as:
   `route('requests.show', ['requestId' => entityId])`
5. Preserve all existing P2 inbox list behavior (columns, states, refresh, limit 50).
6. Preserve all existing P5 mark-read behavior (unread-only affordance, mutation delegation, post-success list reload, error surfacing).
7. Add or extend focused coverage in `NotificationInboxUiFlowTest.php` for P6 eligibility, binding, and P5 coexistence.

Implementation is **not** authorized to touch backend layers, routes, DTOs, read services, schema, or governance artifacts for P2/P5.

## Frozen Route Binding

| Property | Locked value |
|---|---|
| Route name | `requests.show` |
| Parameter key | `requestId` |
| Parameter value source | `entityId` from projection row |
| Route generation form | `route('requests.show', ['requestId' => entityId])` |
| Registration evidence | `app/Modules/Request/Presentation/Routes/web.php` |
| Forbidden forms | `route($deepLinkRoute, $entityId)`, `route($deepLinkRoute, [...])`, positional `route(..., $entityId)`, any route name derived from `entityType` |

Eligibility comparison for `deepLinkRoute` must use the **literal string** `requests.show`. No dynamic route-name variable may be passed as the first argument to `route()`.

## Eligibility Rules

| Rule ID | Requirement |
|---|---|
| ELIG-LOCK-001 | Navigation affordance rendered **only** when `deepLinkRoute === 'requests.show'` |
| ELIG-LOCK-002 | Navigation affordance rendered **only** when `entityId` is non-null |
| ELIG-LOCK-003 | Destination must target the existing authenticated **web** route `requests.show` |
| ELIG-LOCK-004 | `entityType` must **not** be read for eligibility, route name, or parameter key |
| ELIG-LOCK-005 | If `deepLinkRoute` is null → display-only, no navigation |
| ELIG-LOCK-006 | If `entityId` is null → display-only, no navigation |
| ELIG-LOCK-007 | If `deepLinkRoute` is any value other than `requests.show` (including `allocations.show`, `check-in.show`) → display-only, no navigation |
| ELIG-LOCK-008 | No disabled, placeholder, empty, or broken navigation controls for non-eligible rows |

## UI Placement Rules

| Rule ID | Requirement |
|---|---|
| PLACE-LOCK-001 | Navigation affordance belongs in the existing **عملیات** column |
| PLACE-LOCK-002 | Affordance form: **dedicated text link** (precedent: `مشاهده` in `request-list-page.blade.php`) |
| PLACE-LOCK-003 | Suggested link label: **مشاهده** — implementation may use this copy; no other localization changes authorized |
| PLACE-LOCK-004 | Unread **eligible** rows: P5 mark-read button **and** navigation link both present in عملیات |
| PLACE-LOCK-005 | Read **eligible** rows: navigation link may appear alone in عملیات |
| PLACE-LOCK-006 | P5 mark-read button text, wiring, loading state, and `markNotificationRead` method must remain unchanged |
| PLACE-LOCK-007 | Row-click navigation is forbidden |
| PLACE-LOCK-008 | Title column remains plain text for all rows (no linked-title substitution) |
| PLACE-LOCK-009 | Navigation must not trigger mark-read or mutate read state |

## Allowed Files

### Allowed to inspect (read-only reference)

| Path | Purpose |
|---|---|
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Current row mapping and P5 mutation surface |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Current table and عملیات column |
| `app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php` | Confirm field names; **do not modify** |
| `app/Modules/Request/Presentation/Routes/web.php` | Confirm `requests.show` / `requestId` |
| `resources/views/livewire/request/request-list-page.blade.php` | Navigation link precedent |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Existing P2/P5 UI tests and architecture guard |
| `tests/Feature/Modules/Notification/NotificationDeepLinkTest.php` | Backend projection evidence; **do not modify** |
| `docs/ui/contracts/notifications/notification-inbox-deep-link-navigation.feature-contract.yaml` | Governing contract |

### Allowed to modify

| Path | Constraint |
|---|---|
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Extend `mapProjectionRow()` to include `deep_link_route` and `entity_id` (or equivalent snake_case keys matching existing row shape). Optional: add presentation-only derived fields such as `request_show_url` **only** when eligibility passes, using literal `route('requests.show', ['requestId' => ...])`. No new services, helpers, or mutation methods. Do not alter `markNotificationRead`, `refreshList`, or list-limit behavior. |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Add navigation text link in عملیات for eligible rows; coexist with P5 mark-read. Use frozen `route('requests.show', ['requestId' => $entityId])` or a pre-mapped URL field generated under the same frozen rule. Preserve all existing columns and P5 controls. |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Add P6 deep-link UI tests and extend architecture guard assertions for P6 within this file only. |

**Execution note:** If repository layout differs at implementation time, confirm paths match the above before editing. No additional files may be modified without a new governance artifact.

## Forbidden Files

### Backend, domain, infrastructure (do not modify)

- `app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php`
- `app/Modules/Notification/Application/Services/NotificationInboxReadService.php`
- `app/Modules/Notification/Application/Contracts/NotificationInboxReadContract.php`
- `app/Modules/Notification/Application/Contracts/MarkNotificationReadContract.php`
- `app/Modules/Notification/Application/Services/MarkNotificationReadAction.php`
- `app/Modules/Notification/Application/Services/NotificationPrincipalEmployeeResolver.php`
- `app/Modules/Notification/Application/**` (other Application files)
- `app/Modules/Notification/Domain/**`
- `app/Modules/Notification/Infrastructure/**`
- `app/Modules/Notification/Infrastructure/Providers/NotificationServiceProvider.php`
- `database/migrations/**` (notification schema)
- Notification write-side / delivery / domain classes

### Routes and unrelated modules (do not modify)

- `app/Modules/Notification/Presentation/Routes/web.php`
- `routes/web.php`
- `routes/api.php`
- `app/Modules/Request/**` (except read-only inspection of `Presentation/Routes/web.php`)
- Allocation, check-in, and other unrelated module files

### Layout, nav, governance (do not modify)

- `resources/views/components/layouts/app.blade.php`
- `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml`
- `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml`
- `docs/ui/locks/notifications/notification-inbox-read-only-list.implementation-lock.md`
- `docs/ui/locks/notifications/notification-mark-read-mutation.implementation-lock.yaml`
- `docs/ui/closeouts/notifications/**`
- Other P2/P5 governance artifacts

### Backend and architecture tests (do not modify)

- `tests/Feature/Modules/Notification/NotificationInboxTest.php`
- `tests/Feature/Modules/Notification/NotificationDeepLinkTest.php`
- `tests/Feature/Modules/Notification/NotificationDeliveryTest.php`
- `tests/Feature/Modules/Notification/NotificationIdempotencyTest.php`
- `tests/Feature/Modules/Notification/NotificationRetentionTest.php`
- `tests/Feature/Modules/Notification/NotificationCheckInReminderTest.php`
- `tests/Architecture/NotificationBoundaryTest.php`

### New abstractions (do not create)

- Route resolver services, helpers, or classes
- New PHP files under `app/Modules/Notification/` other than edits to the two allowed presentation files above
- New test files outside `NotificationInboxUiFlowTest.php`

## Test Boundaries

### May add or update (in `NotificationInboxUiFlowTest.php` only)

| Test obligation | Maps to |
|---|---|
| Eligible row (`deepLinkRoute = requests.show`, non-null `entityId`) renders navigation affordance | AC-DL-001 |
| `deepLinkRoute = requests.show` with null `entityId` renders no navigation | AC-DL-002 |
| Non-allowlisted `deepLinkRoute` renders no navigation | AC-DL-003, AC-DL-008 |
| Null `deepLinkRoute` renders no navigation | AC-DL-004 |
| Generated href/URL uses `requests.show` with `requestId` bound from `entityId` | AC-DL-007 |
| Unread eligible row still renders P5 mark-read affordance unchanged | AC-DL-006 |
| Architecture guard: no `route($` with dynamic deep-link variable; no `entityType` routing; no persistence smells | AC-DL-005, SAF-DL-* |

Suggested test techniques:

- Mock `NotificationInboxReadContract::listForRecipient` with `NotificationProjectionDto` rows carrying varied `deepLinkRoute` / `entityId` combinations.
- Assert rendered HTML contains navigation link only for eligible rows.
- Assert `markNotificationRead` affordance still present for unread rows.

### Must not introduce in tests

- Backend projection or DTO change requirements
- New route registration tests
- API-route navigation coverage (`allocations.show`, `check-in.show`)
- Generic dynamic route coverage for arbitrary `deepLinkRoute` values
- Mark-all, badge, layout nav, or detail-page scenarios
- Modifications to backend feature tests listed under Forbidden Files
- New test files unless separately approved by governance

## Explicit Non-Goals

- Backend changes of any kind
- DTO or projection shape changes
- `NotificationInboxReadService` changes
- Generic notification deep-link framework
- Route resolver abstraction or discovery logic
- `entityType`-based routing
- Route expansion beyond `requests.show`
- Notification detail page
- Layout navigation link or unread badge
- Mark-all-as-read
- Schema or migration changes
- Mark-read mutation semantic changes
- Read/unread business rule changes
- Pagination, filtering, sorting, or ordering changes
- New packages or frontend framework changes
- Pre-navigation authorization logic in Notification UI

## Execution Guardrails

Stop implementation immediately if:

1. **Forbidden file modification required** — any change outside Allowed to modify list.
2. **`requests.show` not resolvable** — route missing or not registered as web Livewire destination.
3. **Projection fields unavailable** — `deepLinkRoute` or `entityId` cannot be consumed from existing `NotificationProjectionDto` via `mapProjectionRow()` without backend changes.
4. **P5 regression** — preserving mark-read requires changing `markNotificationRead`, affordance visibility rules, or mutation semantics.
5. **Generic route mapping temptation** — implementation would need `route($deepLinkRoute, ...)` or `entityType` inference to proceed.
6. **Scope expansion** — support for additional routes, API targets, linked-title navigation, or row-click navigation appears necessary.
7. **New abstraction pressure** — a resolver/helper class seems required; revisit governance instead of coding.

Prefer **minimal surgical diff**: extend existing row array shape; render link in Blade; no new public API on Livewire beyond mapping changes.

## Acceptance Mapping

| Contract AC | Lock enforcement |
|---|---|
| AC-DL-001 | ELIG-LOCK-001/002 + PLACE-LOCK-001/002 + test: eligible row renders navigation |
| AC-DL-002 | ELIG-LOCK-006 + test: null `entityId` → no navigation |
| AC-DL-003 | ELIG-LOCK-007 + test: non-allowlisted route → display-only |
| AC-DL-004 | ELIG-LOCK-005 + test: null `deepLinkRoute` → display-only |
| AC-DL-005 | ELIG-LOCK-004 + architecture guard: no `entityType` routing |
| AC-DL-006 | PLACE-LOCK-004/006 + test: mark-read unchanged on unread eligible rows |
| AC-DL-007 | Frozen route binding + test: `requestId` URL binding |
| AC-DL-008 | ELIG-LOCK-007 + test: API/unsupported route names → no web navigation |

## Final Lock Statement

P6 implementation is authorized as a **presentation-only, surgical overlay** on the existing notification inbox: pass through `deepLinkRoute` and `entityId` in row mapping, render a **مشاهده** text link in **عملیات** for rows where `deepLinkRoute === 'requests.show'` and `entityId` is non-null, generate the destination only via `route('requests.show', ['requestId' => entityId])`, leave all non-eligible rows display-only, and leave P5 mark-read behavior untouched. No other files, routes, backend contracts, or governance artifacts may change.

---

Implementation lock status: APPROVED_FOR_IMPLEMENTATION
