# P6 — Notification Inbox Deep Link Navigation — Feature Analysis

## Feature

| Field | Value |
|---|---|
| **Feature code** | `notification-inbox-deep-link-navigation` |
| **Feature title (working)** | P6 — Notification Inbox Deep Link Navigation |
| **Domain area** | notifications |
| **Analysis date** | 2026-07-09 |

## Analysis objective

Define the precise scope, behavior boundaries, dependencies, exclusions, and governance implications for **deep-link navigation from the notification inbox**, based strictly on repository evidence and prior P2/P5 governance history — without authorizing implementation, drafting contracts or locks, or prescribing UI design or code changes.

---

## Inputs considered

| Input | Role |
|---|---|
| `docs/ui/analysis/notifications/notification-inbox-deep-link-navigation.repo-inspection.md` | Primary repository truth (inspection wins on conflict) |
| `docs/ui/analysis/feature-next-candidate.md` | Backlog positioning; not authoritative over inspection |
| `app/Modules/Notification/Presentation/Livewire/NotificationInboxPage.php` | Inbox mapping and load path |
| `resources/views/livewire/notification/notification-inbox-page.blade.php` | Row rendering and existing affordances |
| `app/Modules/Notification/Application/DTOs/NotificationProjectionDto.php` | Read projection shape |
| `app/Modules/Notification/Application/Services/NotificationInboxReadService.php` | Projection population |
| `tests/Feature/Modules/Notification/NotificationDeepLinkTest.php` | Backend deep-link persistence/projection evidence |
| `tests/Feature/Modules/Notification/NotificationInboxUiFlowTest.php` | Inbox UI behavior; no deep-link coverage |
| `resources/views/livewire/request/request-list-page.blade.php` | Precedent for web `route()` navigation to `requests.show` |
| `docs/ui/contracts/notifications/notification-inbox-read-only-list.feature-contract.yaml` | P2 deep-link exclusion |
| `docs/ui/contracts/notifications/notification-mark-read-mutation.feature-contract.yaml` | P5 deep-link exclusion |
| `docs/ui/locks/notifications/notification-mark-read-mutation.implementation-lock.yaml` | P5 forbidden surfaces |
| `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md` | P2 closed baseline |
| `docs/ui/closeouts/notifications/notification-mark-read-mutation.reconciliation.md` | P5 closed overlay |
| `specs/009-notification-delivery/contracts/notification-intent-dto.md` | `deepLinkRoute` defined as presentation route token, not validated by Notification |
| `specs/009-notification-delivery/contracts/notification-inbox-read-contract.md` | Projection field documentation |

---

## Confirmed repository facts

1. **Inbox UI exists** at `GET /notifications` (`notifications.index`) via `NotificationInboxPage` and `notification-inbox-page.blade.php`.

2. **Deep-link fields exist on the read projection** — `NotificationProjectionDto` includes nullable `entityType`, `entityId`, and `deepLinkRoute`; `NotificationInboxReadService::toProjection()` populates them for `listForRecipient()` and `findByIdForRecipient()`.

3. **Inbox UI currently discards deep-link fields** — `NotificationInboxPage::mapProjectionRow()` maps only `id`, `notification_type`, `title`, `message`, `is_read`, `read_at`, `created_at`, `priority`. No navigation affordance exists in Blade (no `href`, `route()`, or row navigation).

4. **Backend persistence and projection tests exist** — `NotificationDeepLinkTest.php` confirms delivery stores and reads back `entityType`, `entityId`, `deepLinkRoute`; notifications without link data return nulls; mark-read does not mutate link fields.

5. **`deepLinkRoute` is a Laravel route name token** — documented in `notification-intent-dto.md` as optional and **not validated by Notification**. No Notification-module URL builder, presenter, or route-parameter mapper exists.

6. **Observed `deepLinkRoute` values in tests** include `requests.show` (web), `allocations.show` (API-only registration), and `check-in.show` (API-only registration). Parameter names differ: `requestId` vs `allocationId`.

7. **One confirmed web navigation precedent** — Request list uses `route('requests.show', ['requestId' => $request['id']])` in `request-list-page.blade.php`.

8. **P5 mark-read affordance occupies عملیات** for unread rows. P2/P5 governance explicitly excluded deep-link consumption from their approved scopes.

9. **Feature is not implemented** in the inbox presentation layer. Inspection decision: `READY_FOR_FEATURE_ANALYSIS`.

---

## Feature intent and minimum valid scope

### User-facing capability (analysis conclusion)

Allow an authenticated employee, while viewing their notification inbox, to **navigate from an eligible inbox row to a related entity context** when the notification read projection already carries sufficient backend-supplied deep-link data — without adding notification detail pages, new routes, or backend contract changes beyond what repository evidence already supports.

This is **navigation consumption**, not notification management. It completes the inbox follow-through loop deferred since P2 and explicitly excluded from P5.

### Minimum valid scope supported by current evidence (analysis conclusion)

The narrowest scope defensible without inventing backend behavior or deriving routes from `entityType`:

| Dimension | Minimum scope |
|---|---|
| **Surface** | `NotificationInboxPage` on `notifications.index` only |
| **Data source** | Existing `NotificationInboxReadContract::listForRecipient()` → `NotificationProjectionDto` |
| **Eligibility rule** | Render navigation only when **both** `deepLinkRoute` and `entityId` are non-null on the projection (partial rows with only one field are **not** eligible — repository does not prove safe consumption) |
| **Consumption model** | UI maps projection fields to a navigable URL; UI does **not** infer route names from `entityType` |
| **Row coverage** | **Eligible rows only** — not all notification rows. Rows without complete link data remain display-only (same as today) |
| **Coexistence** | Must not remove or alter P5 mark-read behavior on the same inbox surface |

### Scope that cannot be elevated from current evidence

The following are **not** part of minimum valid scope without additional authority or clarification:

- Generic consumption of **any** `deepLinkRoute` value present in test fixtures
- UI derivation of route parameter names from `entityType`
- Navigation to API-only named routes (`allocations.show`, `check-in.show`) from a web Livewire inbox
- Automatic navigation on row click without explicit governance decision on affordance form
- Pre-navigation authorization beyond what destination routes already enforce

### Gap classification (analysis conclusion)

**`UI_BEHAVIOR_GAP`**

Backend read projection already carries deep-link fields. Inbox UI intentionally omits them. The gap is absence of presentation consumption and navigation affordance — not missing persistence, read contract, or delivery pipeline.

**Verdict: `UI_ONLY_GAP`**

Inspection does not identify missing Application read-model work required to enable inbox deep-link display. `MIXED_UI_AND_READ_MODEL_GAP` is not supported.

---

## Dependency and authority analysis

### Backend authority — sufficient for field delivery

| Dependency | Status | Evidence |
|---|---|---|
| `NotificationInboxReadContract` | **Present** | List read used by inbox today |
| `NotificationProjectionDto` deep-link fields | **Present** | `entityType`, `entityId`, `deepLinkRoute` on DTO and in `toProjection()` |
| Persistence | **Present** | `notification_logs.entity_type`, `entity_id`, `deep_link_route` |
| Delivery pipeline | **Present** | `NotificationIntentDto`, `DeliverNotificationAction`, `NotificationDeepLinkTest` |
| Route validation by Notification | **Absent by design** | Intent contract: token not validated |
| Pre-built URL on projection | **Absent** | No `deepLinkUrl` or `routeParameters` field |
| Capability flag (e.g. `can_navigate`) | **Absent** | No backend-provided navigation eligibility flag |
| Route parameter key on projection | **Absent** | Only `entityId` UUID; no `requestId` / `allocationId` key name |

**Analysis conclusion:** Backend authority is **sufficient to supply raw deep-link tokens** to the UI. Backend authority is **not sufficient, on its own, to define safe generic URL construction** for arbitrary `deepLinkRoute` values without either (a) governance-frozen per-route binding rules, or (b) upstream projection extension.

### Are `deepLinkRoute` + `entityId` sufficient for safe UI navigation?

**Partially — only under narrow conditions (analysis conclusion).**

| Condition | Assessment |
|---|---|
| `deepLinkRoute` + `entityId` both non-null | Necessary but not sufficient alone |
| Known web route with known parameter name | **Sufficient for that route only** — `requests.show` + `entityId` maps to `['requestId' => $entityId]` per existing Request list precedent |
| Unknown or API-only route names | **Insufficient** — no parameter key on DTO; API routes are not web inbox targets |
| Using `entityType` to choose parameter name | **Not safe under Thin UI / Anti-Leak** — reconstructs navigation semantics in presentation |

### Route parameter binding (analysis conclusion)

| State | Finding |
|---|---|
| **Explicit on projection** | **Missing** — no route parameter key field |
| **Implicit in repository** | **Ambiguous** — different routes use different parameter names (`requestId` vs `allocationId`) |
| **Documented in Notification module** | **Missing** — no mapper contract |
| **Frozen in prior UI governance** | **One precedent only** — `requests.show` / `requestId` on Request list (different module, different feature) |

Binding is therefore **missing or ambiguous** for generic route consumption and **explicit only for the single web precedent** observed outside the Notification module.

### Web-target validity (analysis conclusion)

| `deepLinkRoute` (test evidence) | Registered surface | Web-consumable from inbox? |
|---|---|---|
| `requests.show` | `routes/web.php` → `requests` prefix | **Yes** — Livewire `RequestShowPage` |
| `allocations.show` | `routes/api.php` → `allocations` prefix | **No web registration found** — API JSON controller path |
| `check-in.show` | `routes/api.php` → `check-in` prefix | **No web registration found** — API controller path |

Repository evidence supports **at least one web-routable target** and **at least two API-only targets** in deep-link test fixtures. Inbox is a **web** surface. Treating all persisted `deepLinkRoute` values as inbox navigation targets would include **route-shape / surface mismatches** not resolved by current evidence.

### UI authority — consumer only

Inbox must remain a read-contract consumer. No repository evidence supports adding repository access, route introspection, or cross-module orchestration in `NotificationInboxPage`. Architecture guard in `NotificationInboxUiFlowTest` already blocks persistence smells; P6 should not expand component authority beyond mapping and rendering backend-supplied fields.

---

## Ambiguities / blockers

| ID | Topic | Type | Analysis |
|---|---|---|---|
| **AB-01** | Route parameter binding for generic `deepLinkRoute` | **Blocker for generic scope** | DTO lacks parameter key. Safe generic `route($deepLinkRoute, …)` binding cannot be defined without governance allowlist or upstream DTO extension. |
| **AB-02** | Web vs API route eligibility | **Blocker for broad scope** | Test fixtures include API-only route names. Product scope for v1 must exclude or defer non-web targets explicitly. |
| **AB-03** | v1 route allowlist | **Clarification required** | Minimum evidence supports `requests.show` only. Whether P6 v1 is intentionally limited to that route, or requires backend-delivered navigation payload for other routes, is undecided. |
| **AB-04** | Affordance placement | **Governance clarification** (not upstream code) | Linked title, dedicated column, separate action, or row click — not determined by repository. Must be fixed in contract/lock, not inferred here. |
| **AB-05** | Partial link data in production | **Missing evidence** | Tests prove all-null and complete pairs. Frequency of partial pairs in real delivery paths is not inventoried. |
| **AB-06** | `entityType` consumption | **Resolved for analysis** | Must **not** be used to derive routes or parameter names in UI. Field may be mapped for display or omitted; navigation eligibility must not depend on it. |
| **AB-07** | Successor framing to P2/P5 | **Governance requirement** | P6 must be authorized as successor overlay (P5 pattern). No P6 artifacts exist yet. |
| **AB-08** | Destination authorization | **Clarification** | Notification does not validate targets. Destination routes (e.g. `requests.show`) own access control. Whether inbox navigation requires additional capability delivery is not evidenced as required today. |

**Primary blockers preventing contract-ready precision:** AB-01, AB-02, AB-03.

---

## Explicit exclusions

The following remain **out of scope** for `notification-inbox-deep-link-navigation` regardless of analysis intent:

| Exclusion | Basis |
|---|---|
| Layout navigation link to inbox | Deferred P2/P5 item; separate discoverability feature |
| Unread badge / `countUnread` | P2/P5 excluded |
| Mark-all-as-read | P5 excluded; no batch backend contract |
| Notification detail page / `notifications.show` | No route evidenced |
| Row-click navigation as default (unless contract explicitly authorizes) | P2 forbidden; affordance undecided |
| Backend contract, DTO, schema, migration changes | Fields already exist; P5 closeout pattern preserves backend freeze unless new analysis proves necessity |
| New HTTP/API routes | Not required by inspection |
| UI inference of routes from `entityType` | Anti-Leak; no backend capability |
| Generic navigation to API-only `deepLinkRoute` values | Web/API surface mismatch |
| Pagination, filter, sort, realtime refresh | P2/P5 excluded |
| Mark-read behavior changes | P5 closed scope |
| Reopening or amending P2/P5 closeouts, contracts, or locks | Successor overlay only |
| Navigation for rows missing `deepLinkRoute` or `entityId` | Not evidenced as safe |

---

## Relationship to prior governance decisions

### P2 — Notification Inbox (Read-Only) — CLOSED

| P2 position | P6 relationship |
|---|---|
| `scope.excluded`: deep-link consumption (`deepLinkRoute`, `entityType`, `entityId`) | P6 is a **separate successor feature** that would supersede only the deep-link prohibition on `NotificationInboxPage`, not reopen P2 baseline |
| `view_model.not_consumed_in_v1` lists deep-link fields | P6 authorizes consumption of those fields under new governance |
| Closeout: `IMPLEMENTED_RECONCILED` | P2 remains closed; P6 does not amend P2 artifacts |

### P5 — Notification Mark-Read Mutation — CLOSED (`IMPLEMENTED_RECONCILED`)

| P5 position | P6 relationship |
|---|---|
| Contract/lock exclude deep-link navigation, detail view, row-click navigation | P6 supersedes **only** the deep-link navigation exclusion for inbox rows |
| P5 closeout lists deep-link as out of scope | Confirms P6 requires its own governance path |
| P5 added **عملیات** mark-read column | P6 must coexist on same table; must not regress P5 |

### Successor framing (analysis conclusion)

P6 should follow the **P5 successor-overlay pattern**:

- Narrow overlay on existing `NotificationInboxPage`
- Explicit supersession language for P2/P5 deep-link prohibitions only
- No amendment to closed P2/P5 artifacts
- Separate contract → lock → implementation authorization chain

### Is contract-ready after analysis?

**Not yet (analysis conclusion).**

Evidence supports feature viability as a **UI-only consumption gap**, but **precise contract drafting is blocked** until AB-01–AB-03 are resolved:

1. **Route binding strategy** — governance allowlist with frozen per-route parameter mapping vs upstream projection extension (e.g. URL or parameter map on DTO).
2. **v1 eligible `deepLinkRoute` set** — minimum `requests.show` only vs broader set requiring backend changes.
3. **Affordance form** — contract-level decision (not upstream), but must be recorded before lock.

---

## Risk assessment

| Risk | Severity | Notes |
|---|---|---|
| UI Anti-Leak violation via `entityType` → route mapping | **High** | Mitigation: exclude `entityType` from navigation logic; eligibility from `deepLinkRoute` + `entityId` only |
| Linking to API routes from web inbox | **High** | Mitigation: explicit v1 web-route allowlist in contract |
| Silent scope bleed into P5 mark-read | **Medium** | Mitigation: successor contract pins coexistence; regression tests |
| Implementing without P2/P5 supersession | **High** | Mitigation: mandatory successor contract before coding |
| User-visible broken links for partial data | **Medium** | Mitigation: strict eligibility rule (both fields non-null + allowlisted route) |
| Hardcoded route map drift as new routes added | **Medium** | Mitigation: contract documents allowlist maintenance rule or defers to upstream URL delivery |
| Over-scoping to “all notifications navigable” | **High** | Mitigation: eligible-rows-only rule documented above |

---

## Recommended next governance step

Proceed to **review decision** (`docs/ui/decisions/notifications/notification-inbox-deep-link-navigation.review-decision.md` — not yet present) to resolve:

1. Route binding strategy (AB-01) and v1 web-route allowlist (AB-02, AB-03)
2. Whether upstream backend/projection extension is required before contract, or a narrow governance allowlist is sufficient for v1
3. Successor supersession boundaries relative to P2/P5
4. Disposition on contract necessity and affordance placement (AB-04)

Do **not** draft contract, lock, or implementation in this analysis step.

If review decision determines upstream projection extension is required for safe navigation beyond a single known web route, **upstream clarification** must complete before contract drafting.

---

## Decision status

**NEEDS_UPSTREAM_CLARIFICATION**

The feature is viable as a UI behavior gap atop an existing read projection, but safe navigation semantics cannot be defined precisely for contract drafting until route parameter binding strategy and web-target eligibility (AB-01–AB-03) are resolved. Repository evidence alone does not prove generic `deepLinkRoute` + `entityId` consumption is safe. A narrowly allowlisted v1 (e.g. `requests.show` only) may avoid upstream code changes but still requires explicit governance resolution — not automatic contract readiness.

---

Recommended next governance step: upstream clarification required
