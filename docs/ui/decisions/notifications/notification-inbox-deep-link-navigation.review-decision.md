# Notification Inbox Deep-Link Navigation Review Decision

## Feature

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-deep-link-navigation` |
| **Feature title** | P6 — Notification Inbox Deep Link Navigation |
| **Domain area** | notifications |
| **Scope** | Governed presentation consumption of backend-supplied deep-link data on `NotificationInboxPage` (`notifications.index`) to enable navigation to eligible web destinations from inbox rows |

## Review Objective

This review decides whether P6 has sufficient authority and precision to proceed to **feature contract drafting**, and resolves analysis blockers **AB-01**, **AB-02**, and **AB-03**.

This review does **not**:

- Authorize implementation, coding, or tests
- Draft a feature contract or implementation lock
- Reopen or amend closed P2 or P5 artifacts
- Design UI visuals or prescribe Blade/Livewire implementation
- Approve backend contract, DTO, schema, or migration changes for v1

## Evidence Reviewed

| Artifact / evidence | Use |
|---|---|
| `docs/ui/analysis/notifications/notification-inbox-deep-link-navigation.repo-inspection.md` | Primary repository truth |
| `docs/ui/analysis/notifications/notification-inbox-deep-link-navigation.feature-analysis.md` | Gap classification, blockers, minimum scope |
| `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md` | P2 closed baseline and deep-link exclusion |
| `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` | P2 `scope.excluded` / `not_consumed_in_v1` deep-link fields |
| `docs/ui/closeouts/notifications/notification-mark-read-mutation.reconciliation.md` | P5 closed overlay; deep-link listed out of scope |
| `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml` | P5 deep-link exclusion |
| `docs/ui/locks/notifications/notification-mark-read-mutation.implementation-lock.yaml` | P5 `deep_link_navigation` forbidden surface |
| `app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php` | Projection field shape |
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Current UI mapping omission |
| `app/Modules/Request/Presentation/Routes/web.php` | `requests.show` route definition (`requestId`) |
| `routes/web.php`, `routes/api.php` | Web vs API route registration surfaces |
| `resources/views/livewire/request/request-list-page.blade.php` | Precedent: `route('requests.show', ['requestId' => $id])` |
| `tests/Feature/Modules/Notification/NotificationDeepLinkTest.php` | Backend persistence/projection of deep-link fields |
| `specs/009-notification-delivery/contracts/notification-intent-dto.md` | `deepLinkRoute` as unvalidated presentation route token |

## Decision Summary

- P6 is a valid **`UI_BEHAVIOR_GAP` / `UI_ONLY_GAP`** successor feature atop existing read projection fields; implementation is **not authorized** until contract and lock complete.
- **Generic deep-link route consumption is rejected** for v1. Repository evidence does not support safe parameter binding or web-target eligibility for arbitrary `deepLinkRoute` values.
- **Narrow governance allowlist is approved for v1:** only `requests.show` with explicit frozen parameter binding `entityId` → `requestId`.
- **API-only route names** (`allocations.show`, `check-in.show`) are **excluded** from inbox web navigation in v1.
- **No upstream backend/projection extension is required** for v1 contract drafting under the approved narrow allowlist.
- P2 and P5 remain **closed**; P6 supersedes only the prior deep-link prohibition on the inbox surface and must coexist with P5 mark-read in **عملیات**.
- **Verdict:** `APPROVED_FOR_CONTRACT` — route binding, web eligibility, and v1 safety boundary are now explicit enough for contract drafting.

## AB-01 — Route Parameter Binding

**Resolution: resolved only for narrow allowlist**

| Question | Answer |
|---|---|
| Is `entityId` sufficient to call route generation safely? | **Only for allowlisted routes with a frozen parameter key.** For v1: yes when `deepLinkRoute === 'requests.show'` and `entityId` is non-null, bound as `['requestId' => $entityId]`. **Not sufficient** for generic or unknown routes. |
| Is the required route parameter key known for every allowed route? | **Known for v1 allowlist only.** `requests.show` requires `requestId` per `app/Modules/Request/Presentation/Routes/web.php` and Request list precedent. Parameter keys for other test-observed routes (`allocationId` for `allocations.show`, `check-in.show`) are **not approved** for v1 consumption. |
| Does Laravel route model binding introduce ambiguity? | **No additional ambiguity for v1 allowlist.** `requests.show` accepts a UUID `requestId` route parameter (`->whereUuid('requestId')`). UI passes the backend-supplied `entityId` string as `requestId`; no Eloquent model resolution is required in the inbox component. Destination authorization remains on `RequestShowPage` / backend read path. |
| Is generic `route($deepLinkRoute, $entityId)` allowed? | **No.** Rejected. Laravel positional binding does not map `entityId` to named keys (`requestId` vs `allocationId`). Generic dynamic construction is unsafe and forbidden. |
| If unresolved, what upstream decision or projection extension is required? | **Not required for v1** under narrow allowlist. For future routes beyond v1 allowlist, upstream must either extend projection (parameter key, parameter map, precomputed web URL, or `target_surface: web`) **or** governance must add explicit per-route frozen bindings in a successor contract amendment. |

**Rationale:** Inspection confirms `NotificationProjectionDto` carries `deepLinkRoute` and `entityId` but no parameter key. Repository precedent for safe web navigation exists for exactly one route: `requests.show` / `requestId`. Governance may freeze that binding in contract without backend change. Generic binding remains unresolved and is out of v1 scope.

## AB-02 — Web Target Eligibility

**Resolution: resolved only for narrow allowlist**

| Question | Answer |
|---|---|
| Are candidate deep-link route names confirmed as web routes? | **Partially.** `requests.show` is registered under `routes/web.php` → `requests` prefix → Livewire `RequestShowPage`. **Confirmed web destination.** |
| Are API-only routes excluded from inbox navigation? | **Yes for v1.** `allocations.show` and `check-in.show` are registered only under `routes/api.php`. They are **excluded** from P6 v1 inbox navigation. |
| Is a row navigable only when its destination is a confirmed web route? | **Yes for v1.** A row is navigation-eligible only when `deepLinkRoute` and `entityId` are both non-null **and** `deepLinkRoute` is on the v1 governance allowlist (`requests.show` only). |
| What happens to rows whose `deepLinkRoute` points to unsupported or non-web targets? | **Remain display-only** — same as today. No link affordance, no navigation attempt, no error state required. Includes: null/missing link fields, API-only route names, and any route not on the v1 allowlist. |

**Rationale:** Inbox is a web Livewire surface. Linking to API-only named routes would produce wrong-surface or non-navigable targets. Repository evidence supports one safe web target for v1; others must be excluded until web routes exist and governance extends the allowlist or upstream delivers web-safe navigation payloads.

## AB-03 — v1 Allowlist Strategy

**Approved strategy: narrow allowlist**

| Strategy | Disposition |
|---|---|
| Generic deep-link route consumption | **Rejected** — AB-01 and AB-02 block safe generic consumption |
| Explicit governance-approved allowlist only | **Approved for v1** |
| Upstream extension required before any navigation | **Rejected for v1** — not required when allowlist is limited to `requests.show` with frozen binding |

**v1 allowlist (frozen at review):**

| `deepLinkRoute` | Parameter binding | Web-eligible |
|---|---|---|
| `requests.show` | `['requestId' => $entityId]` | Yes |

Any other `deepLinkRoute` value persisted in notifications (including `allocations.show`, `check-in.show`) is **out of v1 navigation scope** — rows remain display-only.

**Rationale:** Feature analysis minimum valid scope and repository evidence converge on a single safe web precedent. Narrow allowlist resolves AB-01–AB-03 without backend projection changes and without `entityType`-based inference. Broader route support is deferred to future governance or upstream clarification — not P6 v1.

## Approved v1 Scope

The following is **approved for feature contract drafting** (not implementation):

| Dimension | Approved boundary |
|---|---|
| **Surface** | `NotificationInboxPage` on `notifications.index` only |
| **Data source** | Existing `NotificationInboxReadContract::listForRecipient()` → `NotificationProjectionDto` |
| **Eligibility** | Navigation affordance only when `deepLinkRoute` is non-null, `entityId` is non-null, and `deepLinkRoute === 'requests.show'` |
| **Binding** | Contract must freeze: `route('requests.show', ['requestId' => $entityId])` (or equivalent named-parameter form) — no generic `route($deepLinkRoute, …)` |
| **Non-eligible rows** | Display-only; no link; no fallback navigation |
| **`entityType`** | Must not determine route, parameter key, or eligibility |
| **Coexistence** | P5 mark-read in **عملیات** preserved; deep-link affordance must not remove or break mark-read for unread rows |
| **Backend** | No contract, DTO, schema, migration, or read-contract changes in v1 |
| **New routes** | None |
| **Affordance form** | Deferred to contract/lock (linked title, dedicated control, or **عملیات** action — contract must pin one) |

## Explicit Exclusions

P6 v1 contract and implementation must **not** include:

- API-route navigation from the web inbox (`allocations.show`, `check-in.show`, or any API-only named route)
- `entityType`-based routing or parameter-name inference
- UI-side route inference or discovery
- Generic dynamic `route($deepLinkRoute, $entityId)` or positional route construction
- Navigation for rows missing `deepLinkRoute` or `entityId`
- Navigation for rows whose `deepLinkRoute` is not on the v1 allowlist
- Row-click-as-default navigation (unless contract explicitly authorizes — not approved here)
- Layout navigation link to inbox
- Unread badge / `countUnread` consumption
- Mark-all-as-read or any mutation beyond existing P5 mark-read
- Notification detail page / `notifications.show`
- Pagination, filter, sort, or realtime refresh
- Backend contract, DTO, domain, repository, migration, or schema changes (v1)
- New HTTP or API routes
- Reopening or amending P2/P5 closeouts, contracts, or locks
- Pre-navigation authorization logic in Notification UI beyond destination route enforcement

## P2/P5 Successor Relationship

| Rule | Requirement |
|---|---|
| P6 classification | **Successor overlay** on existing `NotificationInboxPage` (same pattern as P5) |
| P2 status | **Remains CLOSED** (`IMPLEMENTED_RECONCILED`). P6 does not reopen P2 baseline, contract, lock, or closeout |
| P5 status | **Remains CLOSED** (`IMPLEMENTED_RECONCILED`). P6 does not reopen P5 mark-read scope |
| Supersession | P6 supersedes **only** the P2/P5 prohibition on deep-link consumption and navigation on the inbox list surface |
| Coexistence | P6 deep-link affordance must coexist with P5 per-row mark-read in **عملیات** |
| Non-reopening | P6 must not expand into general notification management, layout nav, badge, mark-all, detail page, or backend changes frozen by P2/P5 |
| Artifact policy | P2/P5 governance files remain unchanged; P6 authority flows through new P6 contract → lock → implementation chain |

## Contract Readiness Decision

**APPROVED_FOR_CONTRACT**

| Criterion | Met? |
|---|---|
| Route binding strategy explicit | Yes — frozen `requests.show` / `requestId` ← `entityId` |
| Web target eligibility resolved | Yes — web allowlist only; API routes excluded |
| v1 allowlist or equivalent safety boundary defined | Yes — single-route allowlist |
| P2/P5 successor boundary clear | Yes — overlay; supersede deep-link prohibition only |

Implementation remains **not authorized**. Contract drafting may proceed; lock follows contract review.

## Required Upstream Clarifications

None.

v1 safety boundaries are resolved within this review via narrow allowlist and frozen parameter binding. No backend projection extension is required before contract drafting.

**Deferred to contract/lock (not upstream):**

- Affordance placement (AB-04 from feature analysis)
- Persian copy for navigation control
- Exact test obligations and architecture-guard updates

## Final Governance Status

**APPROVED_FOR_CONTRACT** — P6 is a valid UI-only successor feature with explicit v1 allowlist (`requests.show` only), frozen route-parameter binding, API-route exclusion, and clear P2/P5 supersession boundaries. Feature contract drafting may proceed; implementation remains unauthorized until contract review, lock review, and implementation authorization.

---

Recommended next governance step: contract required
