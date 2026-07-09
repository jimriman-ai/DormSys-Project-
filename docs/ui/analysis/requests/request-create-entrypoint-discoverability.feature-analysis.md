# Request Create Entrypoint Discoverability — Feature Analysis

## Feature

P3 — Request Create Entrypoint Discoverability

## Analysis Status

This analysis is derived from repository facts captured in `docs/ui/analysis/requests/request-create-entrypoint-discoverability.repo-inspection.md`.

No dedicated P3 contract, lock, decision, closeout, or prior analysis artifact exists under `docs/ui/` for this feature name (per `docs/ui/analysis/feature-status-repository-inspection.md`).

This document does not determine final implementation scope.

---

## Repository Facts

Repository inspection confirms request creation capability is already present in code.

Confirmed evidence:

- Web route `requests.create` → `GET /requests/create` → `RequestCreatePage`
- Livewire create page `RequestCreatePage` with `save()` calling `CreatePersonalRequestAction::execute()`
- Blade view `resources/views/livewire/request/request-create-page.blade.php`
- Application action `CreatePersonalRequestAction`
- API route `requests.flow.create-personal` → `POST /api/requests/personal` → `RequestFlowController::storePersonal`
- Authenticated middleware group on web routes: `auth:api`, `request.mutation.principal`, `audit.principal`
- Principal resolution on create via `RequestPrincipalEmployeeResolver::requireEmployeeId()`
- Tests confirm direct reachability of `GET /requests/create` and successful create flow via `RequestCreatePage`

Repository inspection did not confirm:

- Any Blade link or button to `route('requests.create')` on list, show, layout nav, or empty-state surfaces
- Layout navigation entry beyond `requests.index`
- Empty-state action on request list pointing to create
- P3 governance artifacts (contract, lock, decision, closeout)
- Tests explicitly named for create-entrypoint discoverability

Request List governance text records `create request entrypoint` as out-of-scope for Request List only (`request-list.feature-contract.yaml`, `request-list.implementation-lock.yaml`).

---

## Current Request Create Backend State

Evidence indicates personal request creation is implemented at the application and API layers.

| Layer | Evidence |
|---|---|
| Application | `CreatePersonalRequestAction::execute()` creates draft personal request |
| API | `RequestFlowController::storePersonal()` delegates to same action |
| Console | `CreatePersonalRequestCommand` (`request:create-personal`) |
| Authorization on execute | `RequestPrincipalEmployeeResolver::requireEmployeeId()` in web and API create paths |
| Policy registry | `CreatePersonalRequestAction` listed in `PendingMutationAuthorizationRegistry::PENDING` |

Gap suspected at backend capability for create — not observed in inspected evidence. Further review required to confirm posture.

---

## Current Request Create UI State

Evidence indicates a dedicated create page exists and is directly reachable by URL.

| Item | Status in repository |
|---|---|
| Route `requests.create` | Present |
| `RequestCreatePage` Livewire component | Present |
| `request-create-page.blade.php` | Present |
| Create form submit (`ثبت درخواست`) | Present on create page |
| Post-create redirect | To `requests.show` |

Evidence indicates no in-app navigation affordance to the create route was found on inspected surfaces:

| Surface | Create entrypoint in inspected code |
|---|---|
| Request list page header | Not found — refresh button only |
| Request list empty state | Not found — no `action` slot on `x-ui.empty-state` |
| Request show page | Not found — back to list only |
| App layout navigation | Not found — `درخواست‌ها` links to `requests.index` only |
| Home redirect | To `/requests` (list), not create |

Tests assert list UI does not display `ثبت درخواست جدید` (`RequestUiFlowTest`, `RequestListDetailNavigationUiFlowTest`).

Gap suspected at visible UI entrypoints linking users from list, nav, or empty state to `requests.create`. Further review required to confirm posture.

---

## Principal Resolution

Evidence indicates create execution uses the same principal-to-employee resolution pattern as other Request web flows.

- `RequestCreatePage::save()` → `RequestPrincipalEmployeeResolver::requireEmployeeId()`
- `RequestFlowController::storePersonal()` → same resolver
- Failure when principal has no linked employee is tested for API create path

Gap suspected at principal resolution for create — not observed in inspected evidence. Further review required to confirm posture.

---

## Authorization Pattern

Evidence indicates create routes sit inside the authenticated web middleware group and create execution enforces employee linkage at action invocation.

- No Request-specific Gate or Policy controlling create-page visibility was found in inspected presentation files
- No `hasRole` / `canCreate` checks in inspected list, show, layout, or create Blade/ Livewire surfaces

Gap suspected at UI-side authorization mirroring for create visibility — not observed; create visibility conditions beyond route middleware and resolver-at-save were not found in inspected evidence. Further review required to confirm posture.

---

## Discoverability vs Capability

| Dimension | Repository evidence |
|---|---|
| Create capability (route + page + action) | Present |
| Direct URL access when authenticated | Confirmed by tests |
| In-app navigation to create | Not found in inspected views |
| List tests expecting absence of create label | Present (`assertDontSee('ثبت درخواست جدید')`) |
| Request List contract excludes create entrypoint | Recorded as out-of-scope for Request List |

Evidence indicates create is implemented but not surfaced through inspected navigation affordances. Gap suspected at discoverability-only exposure (link/button/menu from existing request surfaces to `requests.create`). Further review required to confirm posture.

---

## Dependencies

Evidence indicates P3 would depend on artifacts and surfaces that already exist:

- `requests.create` route and `RequestCreatePage`
- `CreatePersonalRequestAction` and existing create execution path
- `RequestPrincipalEmployeeResolver`
- Authenticated web middleware stack
- Request list, show, and app layout as candidate placement surfaces (entrypoints not presently found on those surfaces)

No repository evidence was found that a new backend create foundation or new persistence design is required for personal request creation.

---

## Risks (evidence-based)

### 1. Accidental scope expansion

Evidence indicates backend create and a full create page already exist. A discoverability change could be limited to presentation affordances, but scope boundaries are not defined by repository inspection alone.

### 2. Request List boundary overlap

Request List contract and lock explicitly list `create request entrypoint` as out-of-scope for Request List. Any list-surface affordance would intersect with that recorded boundary.

### 3. Test expectation conflict

Existing tests assert absence of `ثبت درخواست جدید` on the list page. Adding a visible create affordance would intersect with those assertions.

### 4. Surface placement ambiguity

Multiple surfaces were inspected (list header, empty state, nav, show, home). Repository evidence does not record which surface(s) P3 would target.

### 5. Authorization posture ambiguity

`CreatePersonalRequestAction` is in `PendingMutationAuthorizationRegistry`. Whether create visibility should be capability-gated in UI was not found in inspected P3 artifacts.

---

## Open Questions

1. Which surface(s) are in scope for a create entrypoint: list header, empty state, primary nav, or combination?
2. Does P3 require backend capability flags (e.g. `can_create`) or presentation-only links to the existing route?
3. How does P3 relate to Request List governance that excludes `create request entrypoint` from Request List scope?
4. Should existing tests that assert `assertDontSee('ثبت درخواست جدید')` be revised if a create affordance is added?
5. Is direct URL access to `/requests/create` sufficient for any baseline, or is in-app discoverability explicitly required?
6. Is contract/lock governance required before UI work, or is presentation-only change authorized?

---

## Analysis Conclusion

Evidence indicates request creation capability is already implemented (route, Livewire page, application action, API endpoint, tests for direct access and create flow).

Evidence indicates inspected request UI surfaces do not expose navigation to `requests.create`.

Gap suspected at in-app discoverability affordances on list, navigation, empty state, and related surfaces — not at core create capability. Further review required to confirm posture.

This analysis does not determine final implementation scope, surface selection, or governance path.

---

## Hand-off for Review Decision

Presented strictly for review decision. No implementation authorization is implied.

| Review input | Repository basis |
|---|---|
| Create capability exists | `requests.create`, `RequestCreatePage`, `CreatePersonalRequestAction`, API create, tests |
| Discoverability affordances absent on inspected surfaces | No `route('requests.create')` in inspected Blade; list tests assert no create label |
| P3 governance artifacts absent | No contract, lock, decision, or closeout for this feature name |
| Request List boundary note | `create request entrypoint` recorded out-of-scope for Request List |

**Await authorization:**

| If review determines… | Then await… |
|---|---|
| Contract / lock / open-decisions governance needed | Schema and contract instruction |
| Presentation-only discoverability change within defined scope | Direct UI implementation instruction |

**Status phrasing (summary):**

Evidence indicates request create capability and a reachable create page exist. Gap suspected at visible UI entrypoints from list, navigation, and empty-state surfaces to `requests.create`. Further review required to confirm posture.

---

## Evidence References

- `docs/ui/analysis/requests/request-create-entrypoint-discoverability.repo-inspection.md`
- `docs/ui/analysis/feature-status-repository-inspection.md`
- `docs/ui/contracts/requests/request-list.feature-contract.yaml`
- `docs/ui/contracts/requests/request-list.implementation-lock.yaml`
