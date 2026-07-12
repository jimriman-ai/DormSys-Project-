# Feature Analysis — Request List Detail Navigation

**Artifact type:** Feature analysis (non-authorizing)  
**Analysis date:** 2026-07-11  
**Checkpoint:** `request-list-detail-navigation.feature-analysis`

This artifact does **not** grant Design Approval, Feature Contract approval, Implementation Authorization, Quickstart, Batch Execution Permission, or coding authority.

---

## 1. Status

`FEATURE_ANALYSIS_COMPLETED`

---

## 2. Work Item

`Request List Detail Navigation`

| Field | Value |
| ----- | ----- |
| Selection status | `NEXT_APPROVED_WORK_ITEM_SELECTED` |
| Selection purpose (as recorded) | Presentation/read-flow discoverability improvement for the existing Request flow |
| Module | Request (Presentation / read journey) |

---

## 3. Purpose

Enable employees to reach the existing read-only Request Show (detail) surface from the Request List, completing the list→detail read journey and improving discoverability of detail without changing Request domain/mutation behavior.

Repository evidence (see §5) shows this purpose was previously framed and, for the core list-row → `requests.show` affordance, already implemented and closeout-recorded. This analysis records current state for Feature Analysis Review; it does not invent a new product problem beyond the selection purpose and repo evidence.

---

## 4. Governance Basis

| Artifact | Role |
| -------- | ---- |
| [next-approved-work-item-selection.md](../handoff/next-approved-work-item-selection.md) | Manual project-owner selection — `NEXT_APPROVED_WORK_ITEM_SELECTED`; next step named: Create Feature Analysis for Request List Detail Navigation |
| [spec03-us4-post-batch-governance-transition-decision.md](../handoff/spec03-us4-post-batch-governance-transition-decision.md) | Prior transition — `POST_BATCH_GOVERNANCE_TRANSITION_WAITING_FOR_AUTHORITY` (selection later recorded separately) |
| [spec03-us4-batch1b-completion-handoff.md](../handoff/spec03-us4-batch1b-completion-handoff.md) | Prior completed work — Spec03 US4 Batch 1b; does not authorize this Request UI item |
| [spec-catalog.md](../spec-catalog.md) | `spec05` Request Management inventory / status mirror — not used as UI authorization |
| [catalog-decisions.md](../catalog-decisions.md) | Canonical CD-* / Authority Map — not overridden; this analysis does not reopen Spec03/EmployeeRead/Dependent/Allocation/Spec04–Spec07 |

Selection authority (as recorded): `Manual project-owner selection recorded after governance transition waiting state.`

Selection scope boundary (as recorded): allows entry into future governance steps only; does **not** authorize Feature Analysis completion as implementation, contracts, quickstarts, authorization, UI/code/test changes.

---

## 5. Current Repository Findings

### Routes / entrypoints

| Evidence | Location |
| -------- | -------- |
| Authenticated home redirects to `/requests` | `routes/web.php` |
| Request web group prefix `requests` | `routes/web.php` |
| `GET /requests` → `RequestListPage` (`requests.index`) | `app/Modules/Request/Presentation/Routes/web.php` |
| `GET /requests/create` → `RequestCreatePage` (`requests.create`) | same |
| `GET /requests/{requestId}` → `RequestShowPage` (`requests.show`, UUID) | same |
| App layout nav links to `requests.index` | `resources/views/components/layouts/app.blade.php` |

### List surface

| Evidence | Location |
| -------- | -------- |
| Livewire list component; read-only refresh/filter/sort/pagination | `app/Modules/Request/Presentation/Livewire/RequestListPage.php` |
| Uses `RequestReadContract::listByEmployeePaginated` + `RequestPrincipalEmployeeResolver` | same |
| Maps summary rows including `id` for linking | same (`mapContractRow`) |
| Blade renders table with column **مشاهده** and per-row `<a href="{{ route('requests.show', ...) }}" wire:navigate>` | `resources/views/livewire/request/request-list-page.blade.php` |
| Create entrypoint links present; list has no submit/cancel/approve/reject `wire:click` mutation affordances | same + `RequestListDetailNavigationUiFlowTest` |

### Detail destination

| Evidence | Location |
| -------- | -------- |
| Livewire show component; loads summary + approval history via `RequestReadContract` | `app/Modules/Request/Presentation/Livewire/RequestShowPage.php` |
| Ownership enforced via `RequestPrincipalEmployeeResolver::assertOwnsSummary` | same |
| Read-only detail Blade; **بازگشت به فهرست** → `requests.index` | `resources/views/livewire/request/request-show-page.blade.php` |
| Create flow redirects to `requests.show` after create | `RequestCreatePage.php` |

### Read models / contracts (reused; not list-nav-specific)

| Evidence | Location |
| -------- | -------- |
| `RequestReadContract` — `getRequestSummary`, `listByEmployeePaginated`, approval history | `app/Modules/Request/Application/Contracts/RequestReadContract.php` |
| List serialization via `RequestApiResponseFactory::serializeSummary` | Presentation Livewire pages |

### Tests

| Evidence | Location |
| -------- | -------- |
| List row exposes `href` to `requests.show` and label **مشاهده**; show loads owned request | `tests/Feature/Modules/Request/RequestListDetailNavigationUiFlowTest.php` |
| Non-owned detail still unauthorized | same |
| List remains read-only except navigation (+ refresh/create/filter/sort presentation) | same |
| Separate show UI flow coverage | `tests/Feature/Modules/Request/RequestShowUiFlowTest.php` |

### Prior UI governance artifacts for this same feature id

| Evidence | Finding |
| -------- | ------- |
| `docs/features/request/request-list-detail-navigation.feature-contract.yaml` | Purpose: navigate from list rows to existing show; exclude Show reopen / backend changes / mutations |
| `docs/ui/contracts/requests/request-list-detail-navigation.feature-contract.yaml` | Parallel contract path under `docs/ui` |
| `docs/ui/locks/requests/request-list-detail-navigation.implementation-lock.yaml` | Implementation lock present |
| `docs/ui/closeouts/requests/request-list-detail-navigation.closeout.yaml` | `implementation: completed`; `closeout: recorded` (2026-07-08); changed list Blade + navigation test only; show/backend unchanged |
| Git history | Commit message: `feat(requests): add request list detail navigation with approved contract and lock` |

---

## 6. Problem Framing

**Historical problem (as documented in prior feature contract purpose):** Request Show existed as a read-only detail surface; Request List existed but (at that time) forbade or lacked list-to-detail navigation; employees could not complete the list→detail read journey from the list.

**Current repository state (evidence-based):**

- A detail destination **exists** (`RequestShowPage` / `requests.show`).
- List rows **do expose** a navigation affordance (**مشاهده** link to `requests.show` with `wire:navigate`).
- Bidirectional list↔show navigation affordances exist (show → **بازگشت به فهرست**).
- Feature-specific tests assert owned navigation, ownership denial, and list read-only discipline.
- A UI closeout records this feature as **implementation completed**.

**What is weak or ambiguous today (for review, not asserted as mandatory residual product gaps):**

- Why this item was **re-selected** after a recorded closeout is not explained in the selection artifact.
- Whether any **residual** discoverability polish remains (e.g., whole-row click, stronger visual CTA, mobile affordance, a11y labeling) is **not** evidenced as an open requirement in the selection artifact; current Blade uses a text-style link in a dedicated column.
- Duplicate contract/lock paths under `docs/features` and `docs/ui` may create governance-path ambiguity for any future residual work.

This analysis does **not** invent a missing link that the list Blade and tests already contain.

---

## 7. Scope Assessment

Likely classification for the **original / closed** scope of this work item:

- read-only discoverability / list-to-detail navigation improvement
- presentation-layer (Request List row affordance) + feature tests
- route wiring already existed (`requests.show`); no new domain mutation
- no new backend read contract required (reused list `id` + existing show)

For the **current re-selection**, scope is **not yet redefined**. Review must determine whether:

1. residual presentation-only polish is intended, or  
2. the selection should be treated as already satisfied by existing closeout evidence, or  
3. another Request discoverability concern was intended but not stated.

---

## 8. Dependency Assessment

| Dependency | Required for core list→show navigation? |
| ---------- | ---------------------------------------- |
| Deferred Request Dependent live integration | **NOT required** (evidence: list/show use Request read + ownership; Dependent stub not involved in these surfaces) |
| EmployeeRead (T049–T052) | **NOT required** |
| New domain behavior | **NOT required** for closed scope |
| New mutation behavior | **NOT required** (explicitly excluded; list remains non-mutating except navigation) |
| Authorization / policy rewrite | **NOT required** (ownership enforcement reused on show) |
| Live Allocation / Spec04–Spec07 reopen | **NOT required** |
| Spec03 Batch 1b reopen | **NOT required** |

---

## 9. Boundaries / Non-Goals

Must remain untouched relative to governance selection + prior feature exclusions:

- Request Show behavior/layout/fields/authorization beyond existing frozen read surface
- Backend Domain / Application / Infrastructure Request read contracts, DTOs, queries (unless a future residual IA explicitly expands — not authorized here)
- Request mutations (submit/cancel/approve/reject) from the list
- Dependent live integration / `DependentSnapshotSourceStub` replacement
- EmployeeRead; live Allocation; Spec04–Spec07 reopen; Spec03 US4 Batch 1b reopen
- UI Anti-Leak: UI must not invent ownership/capability authority; consume existing prepared read data only

---

## 10. Risks / Ambiguities

| Risk / ambiguity | Note |
| ---------------- | ---- |
| Re-selection vs closeout | Item appears already implemented and closeout-recorded; review must avoid double-implementing or silently reopening Show |
| Undefined residual scope | Selection states “discoverability improvement” without listing residual gaps beyond the closed list-row link |
| Dual contract locations | `docs/features/request/...` and `docs/ui/contracts/requests/...` both present for the same feature id |
| Catalog Authority Map gap | Next-spec/batch selection ownership remains undefined in `catalog-decisions.md`; this item proceeded via recorded manual project-owner selection — do not treat that as Implementation Authorization |
| Status string in feature-contract draft vs closeout approved | Contract files may show draft/approved status differences across paths — review should treat closeout + repo behavior as primary implementation evidence |

---

## 11. Recommendation for Next Governance Step

`Proceed to Feature Analysis Review Decision`

Review-dependent possibilities (not pre-decided):

- `Feature Analysis Review Decision` may conclude the selected item is **already delivered** (closeout + tests + Blade evidence) and require no further Feature Contract / IA
- `Feature Contract may be considered during review` **only if** review identifies explicit residual discoverability scope not covered by the closed implementation
- `Further repo inspection may be required before review` only if residual UX/a11y claims are asserted without evidence in this package

Do **not** treat this analysis as authorization to implement, create contracts, quickstarts, or locks.

---

## 12. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or authorization files were modified.`

Only this analysis artifact was created:

- `.specify/docs/analysis/request-list-detail-navigation.feature-analysis.md`

---

## Document Control

- Version: 1.0.0  
- Status: **`FEATURE_ANALYSIS_COMPLETED`**  
- Work item: `Request List Detail Navigation`  
- Owner: Governance / Feature Analysis  
- Last Updated: 2026-07-11  
- Checkpoint: `request-list-detail-navigation.feature-analysis`
