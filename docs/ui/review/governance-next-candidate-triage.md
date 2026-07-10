# Governance Queue Triage — Next Candidate

## Document metadata

| Field | Value |
|---|---|
| **Triage date** | 2026-07-10 |
| **Mode** | Queue triage only — no implementation, contract, lock, verification, or closeout authoring |
| **Runtime context** | Last completed / reconciled item: `employee-context-ui` (`FEATURE_CLOSED`) |
| **Latest authoritative artifact** | `docs/ui/closeout/employee/employee-context-ui.closeout.md` |
| **Current triage objective** | Select exactly one next valid UI governance action after Employee Context UI closeout |
| **Known concern** | Product authorization artifact still reads `AUTHORIZED` for the now-closed employee feature; closeout is higher authority and excludes reopening |

---

## 1. Review Summary

This triage reconciled the full `docs/ui/` governance tree (analysis, review/decisions, contracts, locks, verification, closeout/closeouts), product authorization artifacts under `docs/product/`, backlog/discovery records, and the prior triage artifact against authority precedence. Repository reality was used only to confirm blockers and invalidate stale assumptions — not to skip gates or invent lifecycle progress.

**Continuation vs new candidate:** No continuable in-progress chain remains. `employee-context-ui` completed closeout. No other feature has an unfinished gate with an explicit next step. No new product-authorized UI intake candidate exists after employee closeout.

**Important closed / superseded / stale items:**

- **`employee-context-ui`** — just closed (`FEATURE_CLOSED`). Full chain repo-inspection → … → closeout complete. Excluded from new candidate selection and from reopening under the same `feature_id`.
- Notification inbox successor chain **P2 → P9** remains fully closed.
- Request-surface lane (P3, P4, list/show baselines, list-detail navigation) remains closed/frozen.
- Prior triage (`NO_OPEN_CANDIDATE_FOUND` after P9) is **stale** as a queue-position record: it predates employee product authorization and the completed employee governance chain. Its *selection outcome pattern* (no open product candidate without new authorization) still applies **after** employee closeout.
- `docs/product/product-authorization-next-ui-feature.md` remains labeled `AUTHORIZED` for `employee-context-ui` only. That grant is **consumed** by the completed chain; closeout overrides any reading that would reopen or continue employee under the same slug.
- `docs/product/next-ui-feature-authorization-discovery.md` and `docs/ui/review/backlog-authority-discovery.md` predate or understate the employee authorization/closeout sequence and are advisory/stale for queue position; they correctly show no *other* authorized UI intake item.
- Deferred residuals (`notification-mark-all-as-read`, reactive badge refresh, audit explorer, identity/auth UX, etc.) remain **blocked**, **unclear**, or **not product-authorized** — not eligible `NEW_CANDIDATE` selections.

---

## 2. Canonical Feature Ledger

| Canonical slug | Aliases | Lifecycle | Highest gate | Latest authoritative artifact | Latest status | Next valid gate |
|---|---|---|---|---|---|---|
| `employee-context-ui` | Employee Context UI; employee-hr-admin-ui; spec03 Phase F / R-15 Livewire HR admin | `closed` | `closeout` | `docs/ui/closeout/employee/employee-context-ui.closeout.md` | `FEATURE_CLOSED` | none |
| `notification-inbox-pagination` | P9; Notification Inbox Pagination; notification pagination; inbox pagination | `closed` | `closeout` | `docs/ui/closeout/notifications/notification-inbox-pagination.closeout.md` | `IMPLEMENTED_VERIFIED_CLOSED` (`P9_CLOSED_IMPLEMENTED_VERIFIED`) | none |
| `notification-inbox-unread-badge` | P8; Notification Inbox Unread Badge; unread badge; countUnread badge | `closed` | `closeout` | `docs/ui/closeout/notifications/notification-inbox-unread-badge.implementation-closeout.md` | `IMPLEMENTED_VERIFIED` | none |
| `notification-inbox-layout-navigation` | P7; Notification Inbox Layout Navigation; layout nav; اعلان‌ها nav | `closed` | `closeout` | `docs/ui/closeouts/notifications/notification-inbox-layout-navigation.closeout.md` | `P7_CLOSED_SUCCESSFULLY` | none |
| `notification-inbox-deep-link-navigation` | P6; Notification Inbox Deep Link Navigation; deep-link; مشاهده | `closed` | `closeout` | `docs/ui/closeout/notifications/notification-inbox-deep-link-navigation.closeout.md` | `IMPLEMENTED_VERIFIED` | none |
| `notification-mark-read-mutation` | P5; Notification Mark-Read Mutation; mark-read; per-row mark-read | `closed` | `closeout` | `docs/ui/closeouts/notifications/notification-mark-read-mutation.reconciliation.md` | `IMPLEMENTED_RECONCILED` | none |
| `notification-inbox-read-only` | P2; Notification Inbox (Read-Only); notification-inbox-read-only-list | `closed` | `closeout` | `docs/ui/closeouts/notifications/notification-inbox-read-only.reconciliation.md` | `IMPLEMENTED_RECONCILED` | none |
| `request-list-filtering-sorting-pagination` | P4; Request List Filtering / Sorting / Pagination | `closed` | `closeout` | `docs/ui/closeouts/requests/request-list-filtering-sorting-pagination.reconciliation.md` | `IMPLEMENTED_WITH_VERIFICATION_BLOCKER` | none (optional verification hygiene only; not product continuation) |
| `request-create-entrypoint-discoverability` | P3; Request Create Entrypoint Discoverability | `closed` | `closeout` | `docs/ui/closeouts/requests/request-create-entrypoint-discoverability.reconciliation.md` | `IMPLEMENTED_RECONCILED` (prior re-intake: `SUPERSEDED`) | none |
| `request-list-detail-navigation` | Request List Detail Navigation; list→show navigation | `closed` | `closeout` | `docs/ui/closeouts/requests/request-list-detail-navigation.closeout.yaml` | closeout `recorded` / implementation `completed` | none |
| `request-list` | Request List; requests.index baseline | `closed` | `implementation-lock` (frozen baseline) | `docs/ui/contracts/requests/request-list.feature-contract.yaml` + lock | `approved` / frozen; superseded in part by P4 + detail-nav | none |
| `request-show` | Request Show; requests.show baseline | `closed` | `implementation-lock` (frozen baseline) | `docs/ui/contracts/requests/request-show.feature-contract.yaml` + lock | `approved`; mutations explicitly out of scope | none |
| `notification-mark-all-as-read` | mark-all-as-read; Mark All as Read; batch mark-read | `blocked` | none (no feature governance chain) | Deferred/excluded language in P2/P5/P8/P9; backlog/discovery | Backend batch mutation contract absent | none until backend capability + product authorization |
| `notification-badge-reactive-refresh` | reactive badge refresh; wire:poll badge; post-mark-read badge refresh | `unclear` | none (not a named governed feature) | P8 review/contract/closeout refresh policy | Deferred/rejected for P8 v1; no dedicated feature slug/chain | none |
| `audit-explorer-ui` | OA-10-05; E-03 Operator Explorer UI; Audit explorer | `blocked` | none | spec10 closure; spec11 IA excludes E-03; discovery | Not product-authorized for UI intake; IA exclusion | none |
| `reporting-kpi-dashboards` | E-04; Reporting Compliance KPI Dashboards | `blocked` | none | spec11 IA non-authorized scope | Not product-authorized | none |
| `identity-auth-ux` | OA-02-01; Identity Authentication UX | `blocked` | none | Spec OA deferral; discovery | Not product-authorized; auth stack deferred | none |
| `identity-livewire-admin` | Identity Livewire Admin; T035–T037 | `blocked` | none | Spec02 Phase E deferral; discovery | Not product-authorized; coupled to auth posture | none |
| `dormitory-admin-ui` | Dormitory Catalog Admin UI; Phase H | `blocked` | none | Spec04 Phase H; discovery | Not product-authorized; module dependency | none |
| `lottery-operator-ui` | Lottery Operator Livewire UI; OA-06-04 | `blocked` | none | Spec06 OA; discovery | Not product-authorized | none |
| `voucher-presentation-ui` | Voucher Employee/Operator Presentation; OA-08-05 | `blocked` | none | Spec08 OA; discovery | Not product-authorized | none |
| `allocation-checkin-operator-ui` | Allocation / Check-In Operator UI | `blocked` | none | Spec07 Livewire exclusion; discovery | Not product-authorized | none |
| `request-show-workflow-mutations` | Request Show workflow mutations | `blocked` | none | Frozen by `request-show` contract | Outside authorized UI backlog without product reopen | none |

---

## 3. Candidate Inventory

| Canonical slug | Domain | Lifecycle | Artifact state | Readiness note |
|---|---|---|---|---|
| `employee-context-ui` | employee | `closed` | Full chain through closeout | Just closed; exclude; do not reopen under same slug |
| `notification-inbox-pagination` | notifications | `closed` | Full chain through closeout | Exclude |
| `notification-inbox-unread-badge` | notifications | `closed` | Full chain through closeout | Exclude |
| `notification-inbox-layout-navigation` | notifications | `closed` | Full chain through closeout | Exclude |
| `notification-inbox-deep-link-navigation` | notifications | `closed` | Full chain through closeout | Exclude |
| `notification-mark-read-mutation` | notifications | `closed` | Full chain through reconciliation | Exclude |
| `notification-inbox-read-only` | notifications | `closed` | Reconciled; draft contract/lock remain historical | Exclude |
| `request-list-filtering-sorting-pagination` | requests | `closed` | Contract+lock+implementation; reconciliation with verification blocker | Product closed; optional formal verification is hygiene only |
| `request-create-entrypoint-discoverability` | requests | `closed` | Reconciliation closeout; prior re-intake superseded | Exclude |
| `request-list-detail-navigation` | requests | `closed` | Closeout recorded | Exclude |
| `request-list` | requests | `closed` | Approved frozen baseline | Exclude |
| `request-show` | requests | `closed` | Approved frozen baseline; mutations excluded | Exclude |
| `notification-mark-all-as-read` | notifications | `blocked` | No dedicated `docs/ui/` chain | Deferred successor; blocked on missing backend batch mutation + missing product auth |
| `notification-badge-reactive-refresh` | notifications | `unclear` | Mentioned only as P8 v1 non-goal | Not a named backlog feature; unsafe as NEW_CANDIDATE |
| `audit-explorer-ui` | audit / reporting | `blocked` | Planning/exclusion only | Explicitly excluded from spec11 IA; needs separate product auth |
| `reporting-kpi-dashboards` | reporting | `blocked` | Planning-only | E-04 excluded from current IA |
| `identity-auth-ux` / `identity-livewire-admin` | identity | `blocked` | Spec deferral only | No product auth for UI intake |
| Other deferred presentation OAs (dormitory, lottery, voucher, allocation/check-in) | various | `blocked` | Spec deferral only | No product auth for UI intake |
| Request show workflow mutations | requests | `blocked` | Frozen by `request-show` contract | Outside authorized UI backlog |

---

## 4. Exclusions

| Feature | Reason |
|---|---|
| `employee-context-ui` | **Closed** — `FEATURE_CLOSED`; closeout forbids further work under this `feature_id`; product auth grant consumed |
| `employee-hr-admin-ui` | **Duplicate alias** of closed `employee-context-ui` (discovery naming); do not re-intake as a separate open feature |
| `notification-inbox-pagination` (P9) | Closed — `IMPLEMENTED_VERIFIED_CLOSED` |
| `notification-inbox-unread-badge` (P8) | Closed — `IMPLEMENTED_VERIFIED` |
| `notification-inbox-layout-navigation` (P7) | Closed — `P7_CLOSED_SUCCESSFULLY` |
| `notification-inbox-deep-link-navigation` (P6) | Closed — `IMPLEMENTED_VERIFIED` |
| `notification-mark-read-mutation` (P5) | Closed — `IMPLEMENTED_RECONCILED` |
| `notification-inbox-read-only` (P2) | Closed — `IMPLEMENTED_RECONCILED` |
| `request-list-filtering-sorting-pagination` (P4) | Conditionally closed — `IMPLEMENTED_WITH_VERIFICATION_BLOCKER`; not a product continuation |
| `request-create-entrypoint-discoverability` (P3) | Closed / superseded — `IMPLEMENTED_RECONCILED` |
| `request-list-detail-navigation` | Closeout recorded / completed |
| `request-list`, `request-show` | Frozen approved baselines |
| `notification-mark-all-as-read` | **Blocked** — no backend batch mark-all contract; no product auth for UI intake |
| `notification-badge-reactive-refresh` | **Unclear** — P8 v1 non-goal; not a named governed feature slug |
| `audit-explorer-ui` / `reporting-kpi-dashboards` | **Blocked** — not product-authorized; spec11 IA excludes E-03/E-04 |
| Identity / dormitory / lottery / voucher / allocation-checkin deferred UIs | **Blocked** — specification deferral ≠ UI governance authorization |
| Request show workflow mutations | Frozen out of scope by `request-show` contract |
| P4 formal verification hygiene | Process gap only; not a product feature candidate |
| Duplicate aliases (P2–P9 titles, employee-hr-admin-ui, inbox/pagination/badge labels) | Collapsed into canonical slugs above |
| Prior triage naming P9 as last completed / queue tip | **Stale** — overridden by employee authorization + closeout |
| Product auth still labeled `AUTHORIZED` for employee | **Stale relative to closeout** — does not authorize a *next* feature or reopen employee |
| Discovery artifacts claiming no product auth / blocked intake | **Stale** relative to the completed employee cycle; still correct that **no successor** is authorized |

---

## 5. Selected Next Governance Action

| Field | Value |
|---|---|
| **Selected feature slug** | none |
| **Domain** | n/a |
| **Action type** | none (neither `NEW_CANDIDATE` nor `CONTINUE_IN_PROGRESS`) |
| **Current state** | Employee chain closed; no other active UI governance chain; no newly authorized open intake candidate |
| **Authoritative artifact** | `docs/ui/closeout/employee/employee-context-ui.closeout.md` |

### Justification

1. **Prefer continuation** — After `FEATURE_CLOSED`, there is no unfinished gate on `employee-context-ui`. Continuation is invalid.
2. **Do not reopen closed work** — Closeout §9 excludes further work under this `feature_id` and requires a separate product authorization for any new employee UI capability.
3. **No eligible `NEW_CANDIDATE`** — The only product authorization on file names `employee-context-ui`, which is now closed. Discovery/backlog inventories list deferred items, but none have current product authorization for UI governance intake, and several are additionally backend-blocked or unclear.
4. **Selecting a blocked/unclear/deferred OA as `NEW_CANDIDATE` would be unsafe** — It would invent intake without product authorization or start UI governance against known blockers.

---

## 6. Recommended Next Governance Gate

**Recommended next gate: none**

No safe earliest gate exists:

- No continuation gate applies (no active in-progress feature).
- No new candidate is eligible for `repo-inspection` without a fresh product authorization naming a different feature.
- Implementation is not authorized for any feature (no pending approved lock-review awaiting execution).

Queue may resume only when a future authoritative product/governance input authorizes a new eligible UI feature (or removes a backend blocker and then authorizes the corresponding UI successor).

---

## 7. Risks / Ambiguity

| Item | Classification | Impact |
|---|---|---|
| `product-authorization-next-ui-feature.md` still status `AUTHORIZED` for closed `employee-context-ui` | **Stale artifact risk** | Non-blocking after closeout precedence; must not be read as reopen or as authorization of a different next feature |
| Prior `governance-next-candidate-triage.md` still framed around post-P9 empty queue | **Stale artifact risk** | This rewrite supersedes it for queue position |
| `next-ui-feature-authorization-discovery.md` / `backlog-authority-discovery.md` understate completed employee cycle | **Stale artifact risk** | Advisory only; do not use to reopen employee or invent candidates |
| `docs/ui/analysis/feature-next-candidate.md` predates P7–P9 and employee closeouts | **Stale artifact risk** | Overridden by closeouts |
| `employee-hr-admin-ui` vs `employee-context-ui` naming | **Alias risk** | Same closed feature; do not treat discovery alias as a new open candidate |
| `notification-mark-all-as-read` remains the only explicit deferred notification product successor | **Blocker** | Backend batch mutation absent; also lacks product auth for UI intake |
| `notification-badge-reactive-refresh` naming/status | **Alias risk** + **unclear** | Must not be invented as a governed slug without explicit backlog intake |
| Employee MVF exclusions (list/search/profile/selectors/dependents/etc.) | **Non-blocking uncertainty** | Documented future-work boundary only; require new feature slug + product auth |
| Accepted residual UX risks in employee closeout (UUID text entry, no capability flags) | **Non-blocking uncertainty** | Historical closeout notes; do not reopen feature |

No repository ambiguity requires `TRIAGE_BLOCKED_BY_REPOSITORY_AMBIGUITY`. Stale lower-authority artifacts are overridden by employee closeout and do not create competing open selections — therefore not `TRIAGE_BLOCKED_BY_STALE_GOVERNANCE`.

---

## 8. Final Decision

**`NO_OPEN_CANDIDATE_FOUND`**

| Field | Value |
|---|---|
| **Next candidate** | none |
| **Next governance gate** | none |
| **Employee disposition confirmed** | `FEATURE_CLOSED` — excluded |
| **Stop boundary** | Do not open contracts, locks, implementation, verification, or closeout work from this triage |

---

*This artifact selects the next governance target and gate only. It does not authorize implementation, contract drafting, lock drafting, verification, closeout, or code changes.*
