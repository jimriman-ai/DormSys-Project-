# Notification Inbox Deep-Link Navigation Closeout

## Feature Status

**IMPLEMENTED_VERIFIED**

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-deep-link-navigation` |
| **Feature title** | P6 — Notification Inbox Deep-Link Navigation |
| **Domain area** | notifications |
| **Classification** | successor-feature (P2/P5 inbox overlay) |

## Governance Chain

The full DormSys UI governance path for P6 was completed:

| Stage | Artifact | Outcome |
|---|---|---|
| Repo Inspection | `docs/ui/analysis/notifications/notification-inbox-deep-link-navigation.repo-inspection.md` | Deep-link fields exist on projection; inbox UI omitted them |
| Feature Analysis | `docs/ui/analysis/notifications/notification-inbox-deep-link-navigation.feature-analysis.md` | `UI_BEHAVIOR_GAP` / `UI_ONLY_GAP`; blockers identified |
| Review Decision | `docs/ui/decisions/notifications/notification-inbox-deep-link-navigation.review-decision.md` | `APPROVED_FOR_CONTRACT`; narrow `requests.show` allowlist |
| Feature Contract | `docs/ui/contracts/notifications/notification-inbox-deep-link-navigation.feature-contract.yaml` | `READY_FOR_IMPLEMENTATION_LOCK` |
| Implementation Lock | `docs/ui/locks/notifications/notification-inbox-deep-link-navigation.implementation-lock.md` | `APPROVED_FOR_IMPLEMENTATION` |
| Repository Change | Three authorized presentation/test files | Completed |
| Verification | `docs/ui/verification/notifications/notification-inbox-deep-link-navigation.verification.md` | `IMPLEMENTED_WITHIN_LOCK` |
| Closeout | This artifact | `IMPLEMENTED_VERIFIED` |

No governance stage was skipped. No scope expansion was authorized or delivered beyond the approved narrow P6 overlay.

## Delivered Scope

P6 adds governed deep-link navigation consumption on the existing `NotificationInboxPage` surface (`notifications.index`):

- **Inbox-only deep-link navigation** — `NotificationInboxPage` only; no detail page, layout nav, or badge.
- **`requests.show` only** — single-route governance allowlist; no generic deep-link consumption.
- **Eligible rows only** — navigation when `deepLinkRoute === 'requests.show'` and `entityId` is non-null.
- **Frozen binding** — `route('requests.show', ['requestId' => entityId])` via `request_show_url` in row mapping.
- **Affordance in عملیات** — dedicated text link **مشاهده** in the existing actions column.
- **P5 mark-read preserved** — unread eligible rows retain **علامت‌گذاری به‌عنوان خوانده‌شده**; mutation semantics unchanged.

Non-eligible rows (null/missing fields, non-allowlisted routes, API-only route names) remain display-only with no navigation control.

## Verified Constraints

| Constraint | Verified |
|---|---|
| No backend changes | Yes |
| No DTO changes | Yes |
| No read service changes | Yes |
| No route changes | Yes |
| No schema/migration changes | Yes |
| No generic resolver | Yes |
| No `entityType` routing | Yes |
| No API-route navigation | Yes |
| No route expansion | Yes |

Implementation touched only:

| File | Role |
|---|---|
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Eligibility + frozen URL in `mapProjectionRow()` |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | `مشاهده` link in عملیات |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | P6 behavior + architecture guard |

## Tests

**Command run:**

```bash
php artisan test tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php
```

**Result:** `25 passed, 77 assertions`

P6-specific coverage includes: eligible `requests.show` navigation, null `entityId` exclusion, non-allowlisted route exclusion, null `deepLinkRoute` exclusion, `requestId` URL binding, P5 mark-read coexistence on unread eligible rows, and architecture guard for frozen `requests.show` binding (no dynamic `route($deepLinkRoute, ...)`, no `entityType` routing).

## Acceptance Criteria Closure

| AC | Requirement | Verified result |
|---|---|---|
| AC-DL-001 | Eligible `requests.show` + non-null `entityId` renders navigation | Pass — `مشاهده` and URL rendered |
| AC-DL-002 | `requests.show` + null `entityId` → no navigation | Pass — no link affordance |
| AC-DL-003 | Non-allowlisted `deepLinkRoute` → display-only | Pass — `allocations.show` row has no navigation |
| AC-DL-004 | Null `deepLinkRoute` → display-only | Pass — no link affordance |
| AC-DL-005 | No route derivation from `entityType` | Pass — Livewire guard; `entityType` not used in mapping |
| AC-DL-006 | P5 mark-read unchanged with P6 navigation | Pass — unread eligible row shows both affordances |
| AC-DL-007 | Destination uses `requests.show` with `requestId` binding | Pass — `request_show_url` matches frozen route form |
| AC-DL-008 | API/unsupported routes → no web navigation | Pass — non-allowlisted rows display-only |

All contract acceptance criteria AC-DL-001 through AC-DL-008 are closed.

## Supersession Boundary

- **P6 supersedes only** the prior P2/P5 prohibition on deep-link navigation consumption for approved eligible rows on the inbox list surface.
- **P2 remains CLOSED** per `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md`. P6 does not reopen or amend P2 artifacts.
- **P5 remains CLOSED** per `docs/ui/closeouts/notifications/notification-mark-read-mutation.reconciliation.md`. P6 does not reopen or amend P5 artifacts.
- **Future route expansion** (additional `deepLinkRoute` values, generic consumption, API targets, backend projection extensions) requires a new governance decision and superseding artifact.

P2, P5, and P6 coexist on `NotificationInboxPage`: read-only list baseline, governed mark-read mutation, and governed `requests.show` deep-link navigation within their respective approved boundaries.

## Final Closeout Statement

P6 — Notification Inbox Deep-Link Navigation is implemented, verified, and closed within the approved narrow v1 scope: presentation-only consumption of existing `deepLinkRoute` and `entityId` projection fields, frozen navigation to `requests.show` via `requestId: entityId`, eligible-row-only affordance in **عملیات**, and full preservation of P5 mark-read behavior—without backend, DTO, service, route, schema, generic resolver, or route-expansion changes.

Feature status: IMPLEMENTED_VERIFIED
