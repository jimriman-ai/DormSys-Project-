# Workflow UI — Feature Analysis

## Feature

| Field | Value |
|---|---|
| **Canonical feature slug** | `workflow-ui` |
| **Feature title** | Workflow UI |
| **Domain** | Workflow |
| **Source specification** | `.specify/docs/spec-catalog.md` (Deferred Components — Workflow Engine); `.specify/docs/catalog-decisions.md` § **CD-010**; related: `specs/005-request-management` |
| **Analysis date** | 2026-07-10 |
| **Gap classification** | `NO_VALID_UI_SCOPE` — Workflow Application surface absent; Product MVF still `TBD_BY_PRODUCT` |
| **Product authorization** | `docs/product/product-authorization-next-ui-feature.md` — **`AUTHORIZED`** for intake only |
| **Prior gate** | `repo-inspection` → `REPO_INSPECTION_COMPLETE_NOT_READY` |

## Analysis objective

Determine whether any evidence-bounded Workflow UI MVF exists that:

1. Consumes **only Workflow-domain** Application capabilities already delivered.
2. Does **not** treat Request approval APIs/history as Workflow UI capability.
3. Does **not** convert missing Workflow backend into UI requirements.
4. Stays within Product Authorization (including exclusions and `TBD_BY_PRODUCT`).
5. States clearly whether contract phase may proceed.

---

## Inputs considered

| Input | Role |
|---|---|
| `docs/product/product-authorization-next-ui-feature.md` | Authoritative intake; scope `TBD_BY_PRODUCT`; exclusions |
| `docs/ui/analysis/workflow/workflow-ui.repo-inspection.md` | Primary repository truth |
| `.specify/governance/_meta/authority-model.md` | Authorization ≠ inference |
| `.specify/docs/spec-catalog.md` | Workflow Engine deferred; activation criteria |
| `.specify/docs/catalog-decisions.md` | **CD-010** ownership split |
| `specs/005-request-management/spec.md` | OA-05-02 inline routing; FR-EX-006 Workflow deferred |
| `app/Modules/Workflow/README.md` | Placeholder / ADR-011 deferred |
| `docs/ui/contracts/requests/request-show.feature-contract.yaml` | Frozen: workflow mutations out of scope |
| `docs/ui/analysis/employee/employee-context-ui.feature-analysis.md` | Analysis artifact shape (contrast: had delivered Application actions) |

---

## 1. Existing Workflow capabilities available for UI consumption

Evidence: repo-inspection §§3–4, §14.

| Capability class | Available for UI consumption? | Evidence |
|---|---|---|
| Workflow Domain entities / engine | **No** | Placeholder dirs + README; no Domain PHP beyond empty tree |
| Workflow Application contracts | **No** | `Application/Contracts/.gitkeep` only |
| Workflow Application actions / services | **No** | `Application/Services/.gitkeep` only |
| Workflow DTOs / ViewModels / capability payloads | **No** | `Application/DTOs/.gitkeep` only |
| Workflow persistence / migrations | **No** | `database/migrations/modules/workflow/.gitkeep` only |
| Workflow mutation capability keys (`workflow.*`) | **No** | Absent from `MutationCapabilityCatalog` (repo-inspection §8) |
| Workflow web routes / Livewire / Blade | **No** | Presentation `.gitkeep` only; no layout nav |

### Explicit evaluation — Workflow module readiness

| Question | Verdict | Evidence |
|---|---|---|
| Module shell registered? | Yes (placeholder) | `WorkflowServiceProvider` in `bootstrap/providers.php`; empty `register()` |
| Ready for UI consumption? | **No** | Repo-inspection §3.3, §14: no Application surfaces |

**Conclusion:** There are **zero** Workflow-module capabilities available for UI consumption without backend expansion.

---

## 2. Existing Application-layer actions / contracts

### 2.1 Workflow Application layer

| Surface | Status | Evidence |
|---|---|---|
| Any Workflow Application action | **Absent** | Repo-inspection §4 |
| Any Workflow read contract | **Absent** | Repo-inspection §4 |

### 2.2 Adjacent Request Application surfaces (not Workflow UI capability)

Documented for boundary clarity only — **must not** be bound as `workflow-ui` MVF under current Product Authorization.

| Surface | Module | Evidence | Usable as Workflow UI? |
|---|---|---|---|
| `ApproveRequestStageAction` | Request | Repo-inspection §6.2, §10 | **No** — Request-owned; Product excludes Request Show workflow mutations |
| `RejectRequestAction` | Request | Same | **No** |
| `RequestReadContract::getApprovalHistory` | Request | `RequestShowPage` + frozen `request-show` | **No** — Request UI pilot; not Workflow domain |
| HTTP `requests.mutations.approve` / `reject` | Request | `Presentation/Routes/requests.php` | **No** |

### Explicit evaluation — UI mutation binding

| Question | Verdict | Evidence |
|---|---|---|
| Can any Workflow UI mutation bind to existing **Workflow** Application actions? | **No** | No Workflow Application actions exist |
| Can mutations bind to Request approve/reject under this feature? | **Forbidden** | Product auth excluded scope; frozen `request-show`; analysis rule forbids treating Request APIs as Workflow UI |

---

## 3. Existing domain ownership boundaries

| Concern | Owner (CD-010 / catalog) | Owner (code today) | Evidence |
|---|---|---|---|
| `RequestApproval` state / history | Request | Request | CD-010; `RequestApproval` entity; Request README |
| Inline four-stage transition rules (Wave 1) | Request while Workflow deferred | Request (`ApprovalStageResolver`, approve/reject actions) | OA-05-02; repo-inspection §5, §10 |
| Reusable transition rules / chain / routing engine | Workflow when activated | **Not implemented** | Spec catalog Deferred Components; Workflow README |
| Event subscription for orchestration | Workflow when activated | **No Workflow subscribers** | Repo-inspection §10 |

### Explicit evaluation — Request ownership vs Workflow UI assumptions

| Assumption | Blocked? | Why |
|---|---|---|
| “Workflow UI can drive approval stage transitions today” | **Yes** | Transitions are Request-owned inline; Workflow engine absent |
| “Approval history UI is Workflow UI” | **Yes** | History is Request Show (frozen Request pilot) |
| “Product auth for workflow-ui implies Request mutation UI” | **Yes** | Product explicitly excludes Request Show workflow mutations |

**Conclusion:** Request ownership **blocks** any analysis that relocates current approval UX into Workflow UI without a new Product decision and Workflow Application delivery.

---

## 4. Possible MVF UI scope if supported by existing capabilities

### 4.1 Evidence-bounded MVF search

Employee Context UI analysis could recommend an MVF because Application mutations were delivered. Workflow has **no** analogous delivered Application surface.

| Candidate MVF idea | Supported by Workflow Application evidence? | Blocked by |
|---|---|---|
| Workflow Livewire hub / nav | No | No routes/pages; no Application to call |
| Workflow chain / stage configuration UI | No | No Workflow Domain/Application |
| Workflow execution / routing console | No | Engine deferred; Product excludes activation-as-UI-substitute |
| Read-only Workflow status / definition screens | No | No Workflow read contracts/DTOs |
| Approver mutation UI via Request actions | Adjacent only | Product exclusion + ownership + frozen Request Show |
| Empty shell page with no backend bind | Not a valid governed MVF | Would invent presentation without capability contract; violates capability-first / Anti-Leak posture |

### Explicit evaluation — read-only UI

| Question | Verdict | Evidence |
|---|---|---|
| Is read-only Workflow UI possible with existing Workflow contracts? | **No** | No Workflow read contracts/DTOs (repo-inspection §4, §11) |
| Does Request approval history count as Workflow read-only MVF? | **No** | Request-owned; already in frozen Request Show; Product does not authorize re-scoping it as `workflow-ui` |

### 4.2 Valid UI scope statement

**No valid UI scope exists** for `workflow-ui` under current repository evidence and current Product Authorization boundaries.

| Statement | Status |
|---|---|
| Evidence-supported Workflow mutation MVF | **None** |
| Evidence-supported Workflow read-only MVF | **None** |
| Product-defined exact screens/actions | **`TBD_BY_PRODUCT`** — not filled by repo |
| Invented MVF from deferred catalog / Request APIs | **Forbidden** by analysis rules |

---

## 5. Capabilities requiring backend prerequisites

Do **not** convert these into UI contract requirements. They are **prerequisites** before any Workflow UI MVF can be evidence-bounded.

| Desired UI idea (illustrative only) | Missing evidence | Prerequisite |
|---|---|---|
| Any Workflow presentation bound to Workflow Application | No Workflow Application contracts/actions | Separate backend/program authorization to implement Workflow Application (and typically engine activation per catalog CD-010) |
| Workflow definition / chain / routing screens | No Domain models or persistence | Workflow Domain + Application delivery |
| Workflow capability flags (`can_*`) for UI | No Workflow capability payload | Backend capability-first contracts in Workflow Application |
| Approver Livewire as Workflow UI | Request owns transitions; Product excludes Request Show mutations | Product re-scope **and** ownership/architecture decision — not inventable here |
| Exact MVF screen list | `TBD_BY_PRODUCT` | Product clarification of authorized screens/actions |

**Rule applied:** missing Workflow backend is a **blocker**, not a UI requirement list (task rule + Product exclusion of engine activation as UI-auth substitute).

---

## 6. Forbidden or unsupported UI scope

| Forbidden / unsupported | Authority / evidence |
|---|---|
| Binding UI to `ApproveRequestStageAction` / `RejectRequestAction` as Workflow UI | Product auth excluded Request Show workflow mutations; CD-010 Request ownership; analysis rule |
| Reopening frozen `request-show` / request list pilots under `workflow-ui` | Product auth; frozen contracts |
| Treating Workflow Engine activation as delivered by this UI authorization | Product auth excluded scope |
| Inventing Workflow Application APIs inside the UI feature | Product auth; Anti-Leak; task rule |
| Converting catalog deferral / FR-EX-010 into mandatory UI screens | Analysis rule; deferral ≠ authorization |
| Reopening `employee-context-ui` | Product auth; closeout |
| Identity / Employee / Notification / Audit / Reporting / Lottery / etc. UI | Product auth exclusions |
| Empty decorative Workflow page with no backend authority surface | Unsupported as governed MVF (no capability contract) |

---

## 7. Dependency risks

| Risk | Severity | Evidence | Implication |
|---|---|---|---|
| Zero Workflow Application surface | **Critical** | Repo-inspection §§3–4, §12 | Contract phase cannot define consumable binds |
| Product scope still `TBD_BY_PRODUCT` | **Critical** | Product auth §3 | Even after backend exists, Product must name MVF screens/actions |
| Confusion with Request approval HTTP/UI | **High** | Adjacent Request surfaces exist and are tested | Agents may mis-scope; must remain excluded |
| Catalog activation criteria unmet | **High** (program) | Spec catalog Deferred Components | Backend activation is separate; Product forbids using it as UI substitute |
| Proceeding to contract without scope | **Critical** (governance) | Authority model: scope must be explicit | Would produce non-authoritative / empty contract |

**Intake for analysis:** complete.  
**Progression to contract:** **blocked**.

---

## 8. Whether Workflow UI can proceed to contract phase

| Criterion | Met? | Evidence |
|---|---|---|
| Product authorization for feature intake | Yes | Product auth `AUTHORIZED` |
| Exact Product MVF scope defined | **No** | `TBD_BY_PRODUCT` |
| At least one Workflow Application capability for UI bind | **No** | Repo-inspection `NOT_READY` |
| Valid evidence-bounded UI scope | **No** | §4 of this analysis |
| Request surfaces usable as substitute | **No** | Product exclusions + ownership |

### Explicit evaluation — Product scope clarification

| Question | Verdict |
|---|---|
| Does Product scope need further clarification? | **Yes — required** |
| Can analysis invent the missing Product MVF from repo? | **No** |
| Can analysis invent Workflow backend from UI need? | **No** |

**Verdict:** Workflow UI **cannot** proceed to feature-contract (or review-decision aimed at contracting) until Product clarifies authorized UI scope **and** Workflow Application capabilities exist for that scope (via separate backend/program authorization as applicable).

---

## Feature analysis status

**`FEATURE_ANALYSIS_COMPLETE_NO_VALID_UI_SCOPE`**

Analysis is complete. No evidence-bounded Workflow UI MVF can be proposed. Contract phase is **not** unlocked.

---

## Proposed contract scope boundary

**None — no valid contract scope.**

One-line boundary:

> No Workflow UI contract scope is proposable: the Workflow module has no Application contracts/actions for UI consumption; Product MVF remains `TBD_BY_PRODUCT`; Request approval APIs and Request Show history are out of authorized `workflow-ui` scope.

---

## Blocking decisions

| ID | Blocking decision required | Owner |
|---|---|---|
| BD-WF-001 | Define exact authorized Workflow UI screens, actors, and actions (replace `TBD_BY_PRODUCT`) | **Product** |
| BD-WF-002 | Authorize and deliver Workflow Application contracts/actions (and any required Domain/engine work) that those screens will consume — **or** explicitly decline/suspend `workflow-ui` until activation criteria and program auth allow | **Product + Architecture / program authorization** |
| BD-WF-003 | Confirm that Request approve/reject and Request Show history remain **out of scope** for `workflow-ui` (already excluded; must not be reopened by inference) | **Product** (reaffirm) |

Until BD-WF-001 and BD-WF-002 are resolved with repository-visible Application surfaces matching Product scope, **no** feature-contract drafting is authorized by evidence.

---

## Next governance gate

**`REQUEST_PRODUCT_CLARIFICATION`**

Not `feature-contract`. Not `review-decision` for contracting.

Expected disposition (not created here): Product / Governance Review must either:

1. Clarify `workflow-ui` MVF **and** authorize prerequisite Workflow Application delivery, then re-enter at `repo-inspection` or a scoped re-analysis after backend evidence exists, **or**
2. Suspend / withdraw `workflow-ui` UI governance until Workflow Engine / Application readiness is program-authorized.

---

## Explicit non-actions

This analysis did **not**:

- Create a feature contract or implementation lock
- Write or modify Application/backend/UI code
- Expand scope beyond Product Authorization
- Treat Request approval APIs as Workflow UI capability
- Convert missing Workflow backend capabilities into UI requirements
- Invent MVF screens from deferred catalog language

---

*Feature analysis only. Next gate: REQUEST_PRODUCT_CLARIFICATION.*
