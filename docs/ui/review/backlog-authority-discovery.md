# DormSys Backlog Authority Discovery

## Document metadata

| Field | Value |
|---|---|
| **Discovery date** | 2026-07-10 |
| **Mode** | Discovery and inventory only — no feature selection, intake, contract, lock, or code |
| **Trigger** | `docs/ui/review/governance-next-candidate-triage.md` → `NO_OPEN_CANDIDATE_FOUND` |
| **Scope** | Identify authoritative sources that may introduce the next eligible **product / UI** feature into the UI governance pipeline |

---

## 1. Discovery Summary

**Searched locations**

- `.specify/` — spec catalog, catalog decisions, authority model, execution policy, handoffs, playbook, discovery, architecture, ADRs, governance batches
- `specs/` — numbered specification programs (`001`–`011`) including deferred presentation OAs and authorization decisions
- `docs/` — UI governance tree, architecture, AI-UI rules, features copy
- Explicit search for backlog / roadmap / priority / candidate / milestone / deferred / planned-work artifacts

**Authority model used**

1. Repository documentation over conversation history and reconstructed candidate lists.
2. Program authority ownership from `.specify/docs/catalog-decisions.md` § Governance Decision Authority Map and `.specify/governance/_meta/authority-model.md`.
3. Spec roadmap ownership from `.specify/docs/spec-catalog.md` (Hard Freeze operational catalog).
4. Operational execution posture from `.specify/governance/execution-policy.md` (authorization vs transition vs precondition).
5. UI closeouts / triage as **governance records** of completed work — not as backlog sources for new product features.
6. Deferred OA language in specs as **specification deferrals**, not as authorized UI intake.

**Was an authoritative backlog source found?**

| Question | Answer |
|---|---|
| Authoritative **program spec roadmap**? | **Yes** — `.specify/docs/spec-catalog.md` |
| Authoritative **UI feature backlog / priority register**? | **No** — none found under any searched path |
| Artifacts that can **authorize** new scoped work (including presentation when in scope)? | **Yes** — Design Approval + Implementation Authorization (Governance Review), constrained by catalog/CD/OA |
| Any deferred UI item already **authorized for UI governance intake**? | **No** |

**Bottom line:** Future DormSys features are defined at the **specification / catalog** layer. There is **no** dedicated authoritative UI product backlog that can place a feature into the UI governance pipeline. Deferred presentation items exist as specification notes and require an explicit product/governance authorization before UI intake.

---

## 2. Authority Source Inventory

| Artifact | Classification | Authority Level | Can Introduce Features | Notes |
|---|---|---|---|---|
| `.specify/docs/spec-catalog.md` | `AUTHORITATIVE_BACKLOG_SOURCE` | Program roadmap (Hard Freeze) | **Yes — numbered specs only** | Controlled specification roadmap; new numbered specs must be added here first. Does **not** define a UI feature queue or P-numbered UI backlog. |
| `.specify/docs/catalog-decisions.md` | `AUTHORITATIVE_BACKLOG_SOURCE` | Boundary + authority ownership | **Indirect** | CD-* boundaries and Governance Decision Authority Map. Does not list UI features; constrains what may be authorized. |
| `.specify/governance/_meta/authority-model.md` | `GOVERNANCE_RECORD` | Normative meta (non-tiered) | **No** | Vocabulary and authority types only; grants no operational authority. |
| `.specify/governance/execution-policy.md` | `GOVERNANCE_RECORD` | Execution HALT / transition | **No** | Controls when work may proceed; does not invent product features. |
| `.specify/docs/playbook/specification-playbook.md` | `SPECIFICATION_SOURCE` | Process authority | **No** | Guides how specs are produced; does not authorize UI features. |
| `.specify/docs/context-map.md` | `SPECIFICATION_SOURCE` | Context relationships | **Indirect** | Domain relationship map; not a feature backlog. |
| `.specify/docs/handoff/*` (nomination, design approval, implementation authorization, closure) | `GOVERNANCE_RECORD` | Instance authorization / closure | **Yes — only within declared authorized scope** | Can authorize implementation (including presentation **if** scope includes it). Current closed/deferred UI scopes do not authorize new UI intake. |
| `specs/*/spec.md` + `plan.md` + `tasks.md` | `SPECIFICATION_SOURCE` | Spec requirements | **Indirect** | Define deferred presentation OAs (e.g. OA-09-05, OA-10-05, OA-02-01, OA-06-04, OA-08-05). Deferral ≠ UI intake authorization. |
| `specs/011-reporting-projections/implementation-authorization-decision.md` | `GOVERNANCE_RECORD` | Active Reporting implementation auth | **No for explorer UI** | Authorizes Reporting module read/API/export work; **explicitly excludes** E-03 Operator Explorer UI. |
| `specs/011-reporting-projections/architecture-clarification.md` | `SPECIFICATION_SOURCE` | Planning baseline | **Indirect** | DL-02: explorer UI is a later presentation wave; not authorized by clarification. |
| `.specify/memory/constitution.md` | `SPECIFICATION_SOURCE` | Tier-0 principles | **No** | Stack/architecture constraints; not a backlog. |
| `.specify/ARCHITECTURE.md` / UI Anti-Leak contracts | `GOVERNANCE_RECORD` | UI architecture rules | **No** | Govern how UI may be built; do not select features. |
| `.specify/docs/discovery/DormSys Discovery Document.md` | `HISTORICAL_RECORD` | Pre-spec discovery | **No** | Pre-specification problem framing; superseded by catalog/specs for authority. |
| `docs/ui/review/governance-next-candidate-triage.md` | `GOVERNANCE_RECORD` | Queue triage | **No** | Records `NO_OPEN_CANDIDATE_FOUND`; cannot invent backlog. |
| `docs/ui/analysis/feature-next-candidate.md` | `STALE_REFERENCE` | Historical reconstructed register | **No** | Explicitly reconstructed from closed deferrals; predates P7–P9 closeouts. |
| `docs/ui/analysis/feature-status-repository-inspection.md` | `STALE_REFERENCE` | Partial inventory | **No** | Undercounts current artifacts; not a backlog. |
| `docs/ui/**` contracts / locks / verification / closeouts | `IMPLEMENTATION_RECORD` / `GOVERNANCE_RECORD` | Completed UI chain evidence | **No for new intake** | Authoritative for closed/frozen features only. Deferred exclusions inside them are historical notes, not backlog authorization. |
| `docs/ui/FEATURE-CONTRACT-GUIDE.md` | `GOVERNANCE_RECORD` | Contract structure guide | **No** | Template rules only. |
| `docs/features/request/request-list-detail-navigation.feature-contract.yaml` | `STALE_REFERENCE` | Duplicate contract copy | **No** | Mirror of closed UI contract path. |
| `docs/ai-ui/*` | `GOVERNANCE_RECORD` | AI execution rules | **No** | Process prompts; not product backlog. |

**Authority conclusion:** The only repository artifact that functions as an authoritative **product/program backlog** is the **spec catalog** (plus catalog decisions). There is **no** authoritative **UI feature backlog**. UI features may enter governance only after an explicit product/governance act that either (a) authorizes a deferred presentation OA as a UI program, or (b) creates/adopts an authoritative UI backlog register.

---

## 3. Candidate Inventory

Discovered candidates only — **not approved** for governance intake.

| Candidate | Source | Domain | Readiness | Notes |
|---|---|---|---|---|
| Notification mark-all-as-read (batch mutation + UI) | Deferred exclusions in P2/P5/P8/P9 UI artifacts; OA-09-05 residual realtime/management gaps | Notification | `BLOCKED_BY_BACKEND` | No `MarkAllNotificationsRead*` Application contract found. Backend capability required before UI governance. |
| Notification badge reactive refresh | P8 review/contract non-goals; OA-09-05 realtime deferral | Notification | `NEEDS_PRODUCT_DECISION` | Named only as deferred/rejected P8 v1 behavior; not a governed feature slug. |
| Audit explorer UI (OA-10-05 / E-03) | `specs/010-audit-trail/spec.md` OA-10-05; spec10 final closure; spec11 DL-02 / E-03 exclusion | Audit / Reporting presentation | `NEEDS_PRODUCT_DECISION` | Explicitly deferred from spec10; excluded from current spec11 implementation authorization. Needs separate presentation authorization. |
| Reporting compliance KPI dashboards (E-04) | spec11 implementation-authorization non-authorized scope | Reporting | `NEEDS_PRODUCT_DECISION` | Explicitly out of current authorization. |
| Identity Livewire admin (T035–T037) | `specs/002-identity-access/*`; catalog OA-02-01 note | Identity | `BLOCKED_BY_DEPENDENCY` | Deferred with OA-02-01 auth UX; login/session not in Wave 1A. |
| Identity auth UX (OA-02-01) | `specs/002-identity-access/spec.md` | Identity | `NEEDS_PRODUCT_DECISION` | Login/session/MFA deferred by OA. |
| Dormitory Livewire admin (Phase H) | `specs/004-accommodation-resource/plan.md` / `tasks.md` | Dormitory | `BLOCKED_BY_DEPENDENCY` | spec04 implementation itself still hold/planning-authorized only for catalog posture; Phase H deferred. |
| Lottery operator Livewire UI (OA-06-04) | `specs/006-lottery-selection/spec.md` | Lottery | `NEEDS_PRODUCT_DECISION` | UI deferred; Application MVP path preferred first. |
| Voucher employee/operator presentation (OA-08-05) | `specs/008-external-accommodation/spec.md` | Voucher | `NEEDS_PRODUCT_DECISION` | Presentation follow-on; not authorized as UI intake. |
| Allocation / CheckIn Livewire UI | `specs/007-allocation-checkin/plan.md` / `tasks.md` | Allocation / CheckIn | `NEEDS_PRODUCT_DECISION` | Livewire excluded from delivered program; needs separate authorization. |
| Request Show workflow mutations | `docs/ui/contracts/requests/request-show.feature-contract.yaml` | Request | `NEEDS_PRODUCT_DECISION` | Explicitly frozen out of pilot scope; would invent backlog if selected without product reopen. |
| Workflow Engine activation | `.specify/docs/spec-catalog.md` Deferred Components | Workflow | `BLOCKED_BY_DEPENDENCY` | Activation criteria not met (CD-010). |
| Notification delivery audit events (R-08) | spec10 closure / plan | Audit ↔ Notification | `NEEDS_PRODUCT_DECISION` | Backend/cross-cutting deferral; not a UI feature by itself. |
| spec11 Reporting backend (non-UI) | spec11 implementation authorization | Reporting | `UNKNOWN` (for **UI** pipeline) | Backend/API implementation may be authorized separately; **not** a UI governance candidate under current E-03 exclusion. |

**No candidate is `READY_FOR_GOVERNANCE`.**

---

## 4. Excluded Historical / Closed Items

| Item | Reason Excluded |
|---|---|
| `notification-inbox-read-only` (P2) | Closed / reconciled |
| `notification-mark-read-mutation` (P5) | Closed / reconciled |
| `notification-inbox-deep-link-navigation` (P6) | Closed / verified |
| `notification-inbox-layout-navigation` (P7) | Closed |
| `notification-inbox-unread-badge` (P8) | Closed / verified |
| `notification-inbox-pagination` (P9) | Closed — `IMPLEMENTED_VERIFIED_CLOSED` |
| `request-create-entrypoint-discoverability` (P3) | Closed / reconciled (re-intake superseded) |
| `request-list-filtering-sorting-pagination` (P4) | Conditionally closed — implementation delivered |
| `request-list-detail-navigation` | Closeout recorded |
| `request-list` / `request-show` baselines | Frozen approved contracts — not new candidates |
| Prior triage selecting P9 for `repo-inspection` | Stale — overridden by P9 closeout |
| `docs/ui/analysis/feature-next-candidate.md` reconstructed P7/P8/P9 ranking | Stale historical reconstruction |
| OA-09-05 inbox/badge presentation as “still open” | Largely absorbed by closed P2–P9 UI chain; residual items listed separately above |
| Conversation / chat assumptions about next UI feature | Not repository-authoritative |

---

## 5. Governance Readiness Assessment

**`BLOCKED_BY_MISSING_PRODUCT_DECISION`**

Rationale:

1. An authoritative **program** backlog exists (`spec-catalog.md`), but it does not authorize the next **UI governance** feature.
2. Multiple **specification-deferred** presentation items exist (OA-10-05/E-03, OA-02-01, OA-06-04, OA-08-05, residual notification mark-all/reactive refresh, request-show mutations).
3. None of those items has a current product/governance decision authorizing entry into the UI governance pipeline.
4. At least one attractive residual (`notification-mark-all-as-read`) is additionally `BLOCKED_BY_BACKEND`.
5. Therefore the UI queue cannot safely start intake from repository evidence alone.

This is **not** `READY_FOR_FEATURE_INTAKE`.  
This is **not** merely “no documents exist” — specification sources exist, but **product authorization to select among them is missing**.

---

## 6. Next Allowed Action

**`REQUEST_PRODUCT_AUTHORIZATION`**

Required product/governance output (any one is sufficient to unblock later triage):

1. **Authorize a specific deferred presentation item** for UI governance intake (e.g., OA-10-05 / E-03 Audit Explorer UI, or another named OA), **or**
2. **Publish an authoritative UI feature backlog register** (path + ownership + ranked open items), **or**
3. **Authorize backend prerequisite work** first (e.g., mark-all Application contract) and then authorize the corresponding UI successor.

Until that authorization exists:

- Do **not** start feature intake
- Do **not** run repo-inspection as if a candidate were selected
- Do **not** treat deferred OA language or stale reconstructed lists as backlog authority

---

## Stop Boundary

This discovery stops here. It does not select a feature, create intake/repo-inspection/analysis/contract/lock artifacts, or authorize implementation.

---

*Audit note: Technical possibility ≠ documented product decision ≠ historical idea ≠ blocked request. Only an authoritative backlog/spec authorization act may introduce a new UI governance candidate.*
