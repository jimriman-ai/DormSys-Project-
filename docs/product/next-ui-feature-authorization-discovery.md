# Decision Status

| Field | Value |
|---|---|
| **STATUS** | **`BLOCKED_BY_MISSING_PRODUCT_DECISION`** |
| **Discovery date** | 2026-07-10 |
| **Mode** | Product authorization discovery and assessment only |
| **Runtime context** | Last closed UI feature: `employee-context-ui` (`FEATURE_CLOSED`) |
| **Feature selected** | none |
| **Feature authorized (for next intake)** | none |
| **NEXT ACTION** | **`REQUEST_PRODUCT_AUTHORIZATION`** |

## Summary

Repository authority sources were re-inspected after Employee Context UI closeout for any explicit product authorization that would allow a **next** UI governance candidate to enter the pipeline.

**Finding:**

1. `docs/product/product-authorization-next-ui-feature.md` still reads **`AUTHORIZED`** for `employee-context-ui` only.
2. That grant is **consumed**: `docs/ui/closeout/employee/employee-context-ui.closeout.md` records **`FEATURE_CLOSED`** and forbids further work under the same `feature_id`. Closeout is higher authority for lifecycle disposition.
3. No separate Product Authorization names a **successor** UI feature for governance intake.
4. Deferred OA / presentation language still documents possible future work but does **not** authorize UI governance.
5. Active Implementation Authorization for spec11 still **excludes** Operator Explorer UI (E-03) and KPI dashboards (E-04).
6. Queue triage (`docs/ui/review/governance-next-candidate-triage.md`) already records **`NO_OPEN_CANDIDATE_FOUND`**.

Therefore the UI governance queue remains frozen pending a **new** product decision.

---

# Repository Authority Evidence

Authority conflicts resolved in this order:

1. Explicit Product Authorization decisions
2. Active approved specifications / implementation authorizations
3. Governance authority documents
4. Historical documents and reconstructed lists

## 1. Explicit Product Authorization decisions

| Artifact | Finding |
|---|---|
| `docs/product/product-authorization-next-ui-feature.md` | Status **`AUTHORIZED`** for canonical slug **`employee-context-ui`** only. Permits UI governance intake starting at `repo-inspection`. Does **not** name any other feature. |
| Same artifact vs closeout | Grant is **lifecycle-consumed**. Closeout §9: no further work under `employee-context-ui`; any new employee UI capability requires a **separate** product authorization and new feature slug. |
| Other `docs/product/*` | Only this authorization path plus this discovery artifact. **No `AUTHORIZED` decision for a next/successor UI feature.** |

**Conclusion (tier 1):** No product authorization currently authorizes a **next** UI feature for intake. The only on-file authorization applies to a feature that is already closed.

## 2. Active approved specifications / implementation authorizations

| Artifact | Finding relevant to UI |
|---|---|
| `.specify/docs/spec-catalog.md` | Authoritative **program** roadmap for numbered specs. Lists deferred presentation notes (OA-02-01, Livewire admin, OA-10-05). Does **not** define a UI feature backlog or authorize UI governance intake. |
| `specs/011-reporting-projections/implementation-authorization-decision.md` | Implementation **AUTHORIZED** for Reporting module read/API/export scope. **NON_AUTHORIZED_SCOPE** includes **E-03 Operator Explorer UI** and **E-04 KPI dashboards**. |
| `specs/011-reporting-projections/architecture-clarification.md` | DL-02: explorer UI is a later presentation wave; clarification does not authorize UI. |
| `.specify/docs/handoff/spec10-final-closure.md` | spec10 CLOSED/FROZEN. OA-10-05 presentation UI deferred to separate authorization. |
| Spec OA deferrals (`OA-02-01`, `OA-06-04`, `OA-08-05`, `OA-09-05`, `OA-10-05`, Phase F/H Livewire, etc.) | Document deferred presentation. **Deferral ≠ authorization.** |
| `specs/003-employee-context` Phase F / R-15 | Historical source for the **closed** `employee-context-ui` MVF. Residual employee surfaces (list/search/profile/etc.) remain deferred and **not** authorized. |

**Conclusion (tier 2):** Specifications name deferred UI possibilities; none currently authorize UI governance intake for a next feature. One active authorization (spec11) **excludes** explorer UI.

## 3. Governance authority documents

| Artifact | Finding |
|---|---|
| `.specify/docs/catalog-decisions.md` § Governance Decision Authority Map | Owns Design Approval, Implementation Authorization, Batch Execution Permission for **spec/batch** execution. Does not grant UI feature intake from deferred OAs. |
| `.specify/governance/_meta/authority-model.md` | Normative vocabulary. Authorization requires explicit scoped grant. Nomination ≠ authorization. |
| `.specify/governance/execution-policy.md` | HALT / Governance Transition rules. Missing selection/authorization → Case B posture for next work. Does not invent UI candidates. |

**Conclusion (tier 3):** Governance docs require explicit authorization; they do not select the next UI feature.

## 4. Historical / reconstructed / UI governance records

| Artifact | Finding |
|---|---|
| `docs/ui/closeout/employee/employee-context-ui.closeout.md` | **Authoritative** for employee UI lifecycle: **`FEATURE_CLOSED`**. Excludes reopening under same slug. |
| `docs/ui/review/governance-next-candidate-triage.md` | **`NO_OPEN_CANDIDATE_FOUND`** after employee closeout. Advisory for queue state; correctly finds no successor authorization. |
| `docs/ui/review/backlog-authority-discovery.md` | Confirms no UI backlog register; recommends `REQUEST_PRODUCT_AUTHORIZATION`. Partially stale relative to completed employee cycle; still correct that **no successor** is authorized. |
| Prior version of this discovery artifact | Stale — understated employee authorization/closeout sequence. **Superseded by this rewrite.** |
| `docs/ui/analysis/feature-next-candidate.md` | Stale reconstructed register (pre-P7–P9 and employee closeouts). **Not** product authorization. |
| Closed UI closeouts/contracts (P2–P9, request surfaces, employee) | Authoritative for **closed** work only. Residual exclusions are historical notes, not intake authority. |

**Conclusion (tier 4):** Historical lists may suggest candidates for product consideration but cannot authorize intake. Closeouts exclude closed features from selection.

## Explicit non-authority (not used to authorize)

Per task rules, the following were **not** treated as authorization evidence:

- Existing code, routes, Livewire components, or file existence (including delivered Employee Hub)
- TODO comments
- Deferred OA sections alone
- Spec catalog ordering / roadmap sequence
- Implementation completeness of backend modules

---

# Candidate Inventory

Discovered candidates for **future product decision support only**. None are authorized for **next** intake. Closed UI features are listed separately as excluded.

## A. Open / deferred presentation candidates (not authorized)

| Canonical feature slug | Feature title | Domain | Source authority | Related spec / OA | Lifecycle | Backend readiness | Dependency status | Product authorization status | UI governance eligibility | Blocking reason |
|---|---|---|---|---|---|---|---|---|---|---|
| `notification-mark-all-as-read` | Notification Mark All as Read | Notification | Spec deferral + closed UI exclusions (historical) | OA-09-05 residual; P5/P8/P9 out_of_scope | deferred / not started | **Not ready** — no `MarkAllNotificationsRead*` Application contract in Notification module | Backend mutation capability missing | **Not authorized** | **Ineligible** | Missing product auth + backend blocker |
| `notification-badge-reactive-refresh` | Notification Badge Reactive Refresh | Notification | P8 non-goals; OA-09-05 realtime deferral | OA-09-05; P8 refresh policy | unclear / unnamed | Partial (countUnread exists; reactive transport undecided) | Product naming + refresh contract undecided | **Not authorized** | **Ineligible** | Missing product auth; not a named governed feature |
| `audit-explorer-ui` | Audit / Operator Explorer UI | Audit / Reporting presentation | Active specs + closure | OA-10-05; spec11 E-03 / DL-02 | deferred | Partial — Audit/Reporting read surfaces planned; explorer UI excluded from current IA | Requires separate presentation authorization | **Not authorized** (explicitly excluded from spec11 IA) | **Ineligible** | Missing product auth; E-03 excluded |
| `reporting-kpi-dashboards` | Reporting Compliance KPI Dashboards | Reporting | spec11 IA non-authorized scope | E-04 | deferred | Unknown / out of current IA | Excluded from current authorization | **Not authorized** | **Ineligible** | Missing product auth; E-04 excluded |
| `identity-auth-ux` | Identity Authentication UX | Identity | Approved spec OA | OA-02-01 | deferred | Not delivered (login/session out of Wave 1A) | Auth stack decision required | **Not authorized** | **Ineligible** | Missing product auth |
| `identity-livewire-admin` | Identity Livewire Admin | Identity | Spec02 plan Phase E | T035–T037; OA-02-01 related | deferred | Backend Identity Wave 1A delivered; admin UI deferred | Often coupled to auth UX posture | **Not authorized** | **Ineligible** | Missing product auth; dependency on auth posture |
| `employee-list-search-ui` | Employee List / Search UI | Employee | Employee closeout future-work boundary | spec03 residual beyond closed MVF | deferred / not started | List/search Application read models not authorized as UI-consumed surfaces for a new feature | Requires new feature slug + product auth; must not reopen `employee-context-ui` | **Not authorized** | **Ineligible** | Missing product auth; closed-feature reopen forbidden |
| `employee-profile-edit-ui` | Employee Profile / Edit UI | Employee | Employee closeout exclusions | spec03 residual | deferred / not started | Profile/edit presentation not in closed MVF | New slug + product auth required | **Not authorized** | **Ineligible** | Missing product auth |
| `dormitory-admin-ui` | Dormitory Catalog Admin UI | Dormitory | Spec04 plan Phase H | Phase H | deferred | spec04 implementation hold / planning-authorized only | Module implementation authorization prerequisite | **Not authorized** | **Ineligible** | Missing product auth + module impl dependency |
| `lottery-operator-ui` | Lottery Operator Livewire UI | Lottery | Spec06 OA | OA-06-04 | deferred | Lottery planned; UI deferred after Application MVP | Spec/program readiness for UI wave | **Not authorized** | **Ineligible** | Missing product auth |
| `voucher-presentation-ui` | Voucher Employee/Operator Presentation | Voucher | Spec08 OA | OA-08-05 | deferred | Voucher nominated; execution not authorized for full program UI | Program authorization incomplete | **Not authorized** | **Ineligible** | Missing product auth |
| `allocation-checkin-operator-ui` | Allocation / Check-In Operator UI | Allocation / CheckIn | Spec07 plan/tasks exclusion | Livewire excluded from delivered program | deferred | Backend program closed without Livewire UI | Separate UI authorization required | **Not authorized** | **Ineligible** | Missing product auth |
| `request-show-workflow-mutations` | Request Show Workflow Mutations | Request | Frozen UI contract | `request-show.feature-contract.yaml` exclusions | frozen out of pilot | Backend workflow/capability delivery not authorized for this UI reopen | Would reopen frozen pilot boundary | **Not authorized** | **Ineligible** | Missing product auth to reopen frozen scope |
| `workflow-engine-activation` | Workflow Engine Activation | Workflow | Spec catalog Deferred Components | CD-010 | deferred component | Activation criteria unmet | Not a UI feature by itself | **Not authorized** | **Ineligible** | Activation criteria + missing product auth |
| `notification-delivery-audit-events` | Notification Delivery Audit Events (R-08) | Audit ↔ Notification | spec10 closure | R-08 | deferred | Cross-cutting backend | Not primarily a UI governance candidate | **Not authorized** | **Ineligible** (UI pipeline) | Missing product auth; not UI-first |

## B. Closed / frozen UI items (excluded from new candidate discovery)

| Canonical feature slug | Lifecycle | Product authorization status | UI governance eligibility | Notes |
|---|---|---|---|---|
| `employee-context-ui` | **closed** | Prior grant **consumed** by closeout | **Ineligible** — closed | `FEATURE_CLOSED`; alias `employee-hr-admin-ui` must not be re-intaken |
| `notification-inbox-read-only` | closed | n/a (completed) | **Ineligible** — closed | P2 |
| `notification-mark-read-mutation` | closed | n/a | **Ineligible** — closed | P5 |
| `notification-inbox-deep-link-navigation` | closed | n/a | **Ineligible** — closed | P6 |
| `notification-inbox-layout-navigation` | closed | n/a | **Ineligible** — closed | P7 |
| `notification-inbox-unread-badge` | closed | n/a | **Ineligible** — closed | P8 |
| `notification-inbox-pagination` | closed | n/a | **Ineligible** — closed | P9 `IMPLEMENTED_VERIFIED_CLOSED` |
| `request-create-entrypoint-discoverability` | closed / superseded | n/a | **Ineligible** — closed | P3 |
| `request-list-filtering-sorting-pagination` | closed | n/a | **Ineligible** — closed | P4 |
| `request-list-detail-navigation` | closed | n/a | **Ineligible** — closed | Closeout recorded |
| `request-list` / `request-show` baselines | frozen closed | n/a | **Ineligible** — frozen | Approved contracts; not new intake |

## C. Non-UI / out-of-pipeline notes

| Item | Notes |
|---|---|
| spec11 Reporting backend (non-UI) | May have separate Implementation Authorization for module work; **not** a UI governance candidate while E-03 remains excluded. |
| Spec catalog next program candidate (spec11) | Program roadmap item; does not authorize UI explorer intake. |
| Stale product-auth label `AUTHORIZED` for employee | Does **not** authorize a different next feature or reopen employee. |

---

# Authorization Assessment

| Question | Answer |
|---|---|
| Does an explicit Product Authorization authorize a **next** UI feature? | **No** |
| Does the on-file `AUTHORIZED` grant for `employee-context-ui` still enable intake? | **No** — feature is **`FEATURE_CLOSED`**; grant consumed |
| Does any active Implementation Authorization include a next UI presentation feature? | **No** — spec11 IA excludes E-03/E-04 |
| Does any deferred OA automatically authorize UI governance intake? | **No** |
| Is any discovered candidate UI-governance-eligible now? | **No** |
| Overall product authorization posture | **`BLOCKED_BY_MISSING_PRODUCT_DECISION`** |

### Assessment rule applied

A candidate is UI-governance-eligible only if:

1. Explicit Product Authorization (or equivalent Governance Review grant) names it, **and**
2. Scope includes UI governance intake (at least through `repo-inspection`), **and**
3. No unresolved authoritative exclusion blocks it (including closeout of the same `feature_id`, and IA exclusions).

No discovered candidate meets these conditions for **next** intake.

---

# Conflicts Found

| Conflict | Evidence A | Evidence B | Authoritative resolution |
|---|---|---|---|
| Product auth still `AUTHORIZED` for employee vs employee closeout | `product-authorization-next-ui-feature.md` status `AUTHORIZED` / slug `employee-context-ui` | `employee-context-ui.closeout.md` → `FEATURE_CLOSED`; triage excludes reopen | **Closeout wins for lifecycle.** Grant is consumed. Does **not** authorize a successor feature. Product auth file is stale relative to queue position. |
| OA-09-05 “presentation deferred” vs delivered inbox/badge/pagination UI | `specs/009-notification-delivery/spec.md` OA-09-05 still lists Livewire inbox/badge/realtime as deferred | Closed P2–P9 UI chain delivered inbox, badge, pagination | **Higher-authority UI closeouts** show OA-09-05 partially absorbed. Residual candidates are mark-all + realtime/reactive only. Does **not** authorize residual items. |
| Catalog status lines vs later handoffs for some specs | `spec-catalog.md` snapshot text may lag | Closure/authorization handoffs (spec09/10/11 artifacts) | Prefer **handoff / IA / closeout** over stale catalog status prose for execution posture. Catalog remains authoritative for **roadmap membership**, not for UI intake. |
| Reconstructed UI backlog vs empty successor authorization | `feature-next-candidate.md` / prior triage rankings | No successor product authorization after employee closeout | **Product authorization for a named next feature is required** — reconstructed lists cannot authorize. |
| Technical possibility of explorer UI vs current IA | Audit read contracts / Reporting DL-02 planning | spec11 IA excludes E-03 | **IA exclusion wins** — explorer UI not authorized. |
| Discovery alias `employee-hr-admin-ui` vs closed `employee-context-ui` | Prior discovery inventory naming | Employee closeout + triage alias rule | **Same closed feature.** Do not re-intake under alias. |

No conflict was resolved by assumption. Where product selection among remaining deferred items is required, status remains blocked.

---

# Missing Product Decisions

Product / Governance Review must supply at least one of the following before UI governance may resume:

1. **Authorize a named UI feature** for governance intake, including:
   - canonical slug and title (**must not** reuse closed `employee-context-ui` without an explicit reopen decision — prefer a new slug for new capability)
   - domain
   - source specification / OA reference
   - authorized and excluded scope
   - explicit permission to start at `repo-inspection`
2. **Publish an authoritative UI feature backlog register** (owned path + ranked open items), then authorize the top eligible item, **or**
3. **Authorize prerequisite backend work first** (e.g. mark-all Application contract), then authorize the corresponding UI successor in a separate product decision, **or**
4. **Supersede / replace** the stale `AUTHORIZED` employee product-auth record with an explicit next-feature authorization (or a blocked request) so queue position is unambiguous.

Until then:

- Do not start UI feature intake
- Do not run repo-inspection as if a candidate were selected
- Do not treat deferred OA language as authorization
- Do not reopen `employee-context-ui` under the consumed grant

---

# Next Allowed Governance Gate

| Field | Value |
|---|---|
| **STATUS** | **`BLOCKED_BY_MISSING_PRODUCT_DECISION`** |
| **NEXT ACTION** | **`REQUEST_PRODUCT_AUTHORIZATION`** |
| **Next allowed UI governance gate** | **none** |
| **repo-inspection** | **Not allowed** |
| **feature-analysis / contract / lock / implementation** | **Not allowed** |

---

## Stop Boundary

This artifact is decision support only. It does not select a feature, authorize a feature, modify other files, or create downstream UI governance artifacts.

---

*End of product authorization discovery.*
