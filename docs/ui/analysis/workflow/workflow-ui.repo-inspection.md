# Workflow UI — Repository Inspection

## Feature

| Field | Value |
|---|---|
| **Canonical feature slug** | `workflow-ui` |
| **Feature title** | Workflow UI |
| **Domain** | Workflow |
| **Source specification** | `.specify/docs/spec-catalog.md` (Deferred Components — Workflow Engine); `.specify/docs/catalog-decisions.md` § **CD-010**; related boundary: `specs/005-request-management` |
| **Product authorization** | `docs/product/product-authorization-next-ui-feature.md` — **`AUTHORIZED`** for UI governance intake; **`repo-inspection` explicitly permitted**; exact UI scope **`TBD_BY_PRODUCT`** |

## Inspection date

2026-07-10

## Inspection scope

Repository-observable facts only for Workflow UI intake. No product re-selection, no inferred UI requirements from deferred catalog/OA language, no conversion of Request approval surfaces into Workflow UI requirements, no feature-analysis conclusions beyond readiness classification.

---

## 1. Inspection Summary

**The Workflow module exists only as a placeholder boundary.** Provider registration and empty layer directories (`.gitkeep`) are present. There is **no** Workflow Domain logic, Application contract/action, DTO, repository, migration, route, Livewire component, Blade view, or Workflow-specific test suite.

**Approval transition logic exists today inside the Request module** (inline four-stage chain per CD-010 Wave 1 / OA-05-02): `ApproveRequestStageAction`, `RejectRequestAction`, `ApprovalStageResolver`, auto-approval settings, append-only `RequestApproval` persistence, and HTTP mutation routes `requests.mutations.approve` / `requests.mutations.reject`.

**Request owns approval state and history** (CD-010). Request Show Livewire is **read-only** for approval history; frozen UI contracts explicitly exclude workflow mutations. Product authorization for `workflow-ui` **excludes** reopening Request Show workflow mutations and **excludes** treating Workflow Engine backend activation as a substitute for this UI grant.

**Special-check verdict:** UI **cannot** consume Workflow-module backend capabilities without expansion — **no Workflow Application surfaces exist**. Adjacent Request approval capabilities are **not** Workflow-owned and are **not** authorized as this feature’s UI scope by Product.

Product authorization is present and permitted this gate. Repository readiness for a Workflow-domain UI that consumes Workflow Application contracts is **`NOT_READY`** (not `READY`; insufficient Workflow evidence is not classified as ready).

---

## 2. Inputs Reviewed

### Required governance / product / spec inputs

| Path | Role |
|---|---|
| `.specify/governance/_meta/authority-model.md` | Authority vocabulary (authorization ≠ inference) |
| `docs/ui/review/governance-next-candidate-triage.md` | Queue context after `employee-context-ui` closeout |
| `docs/product/product-authorization-next-ui-feature.md` | **AUTHORIZED** intake for `workflow-ui`; scope `TBD_BY_PRODUCT` |
| `.specify/docs/spec-catalog.md` | Deferred Components — Workflow Engine; activation criteria |
| `.specify/docs/catalog-decisions.md` | **CD-010** Request vs Workflow ownership split |
| `specs/005-request-management/spec.md` | FR-007–009, FR-EX-006/010, OA-05-02 inline routing |
| `app/Modules/Workflow/README.md` | Placeholder / ADR-011 deferred statement |
| `app/Modules/Request/README.md` | CD-010 ownership; no Workflow implementation |
| `docs/ui/contracts/requests/request-show.feature-contract.yaml` | Frozen: workflow mutations out of scope |
| Existing `docs/ui/analysis/**/*.repo-inspection.md` | Inspection artifact shape |

### Code / tests inspected

| Path | Role |
|---|---|
| `app/Modules/Workflow/**` | Module tree (placeholder) |
| `app/Modules/Workflow/Infrastructure/Providers/WorkflowServiceProvider.php` | Empty register; loads empty migration path |
| `bootstrap/providers.php` | `WorkflowServiceProvider` registered |
| `database/migrations/modules/workflow/` | `.gitkeep` only |
| `app/Modules/Request/Application/Services/ApproveRequestStageAction.php` | Request-owned approve |
| `app/Modules/Request/Application/Services/RejectRequestAction.php` | Request-owned reject |
| `app/Modules/Request/Domain/Services/ApprovalStageResolver.php` | Inline stage routing |
| `app/Modules/Request/Domain/Entities/RequestApproval.php` | Approval history entity |
| `app/Modules/Request/Application/Contracts/RequestApprovalRepositoryContract.php` | Approval persistence port |
| `app/Modules/Request/Application/Services/RequestMutationAuthorizationGate.php` | Mutation auth gate |
| `app/Modules/Request/Presentation/Http/Controllers/RequestMutationController.php` | HTTP approve/reject |
| `app/Modules/Request/Presentation/Routes/requests.php` | Mutation route names |
| `app/Modules/Request/Presentation/Routes/web.php` | Livewire list/create/show |
| `app/Modules/Request/Presentation/Livewire/RequestShowPage.php` | Read-only detail + history |
| `resources/views/livewire/request/request-show-page.blade.php` | Approval history display |
| `app/Application/Mutation/Registry/MutationCapabilityCatalog.php` | `request.approve` / `request.reject` |
| `tests/Feature/Modules/Request/RequestApprovalTest.php` | Approval chain tests |
| `tests/Feature/Modules/Request/RequestHttpMutationTest.php` | HTTP approve/reject |
| `tests/Feature/Modules/Request/RequestShowUiFlowTest.php` | No workflow mutation controls |
| `tests/Architecture/architecture.php` | Workflow in module inventory |
| `routes/web.php` / layout nav | No Workflow routes/nav |

---

## 3. Existing Workflow Backend / Module Capabilities

### 3.1 Module presence

| Item | Evidence |
|---|---|
| Module directory | `app/Modules/Workflow/` present |
| Service provider | `WorkflowServiceProvider` registered in `bootstrap/providers.php` |
| Provider `register()` | Comment only: “Module bindings will be added in later phases.” — **no bindings** |
| Provider `boot()` | `loadMigrationsFrom(database_path('migrations/modules/workflow'))` |
| README | “Placeholder boundary only (ADR-011 deferred). No workflow engine, states, services, domain logic, or business implementation in Spec01.” |
| Architecture inventory | `Workflow` listed in `architectureModuleNames()` |

### 3.2 Layer contents (Workflow module)

| Layer path | Contents |
|---|---|
| `Application/Contracts/` | `.gitkeep` only |
| `Application/DTOs/` | `.gitkeep` only |
| `Application/Services/` | `.gitkeep` only |
| `Domain/Models/` | `.gitkeep` only |
| `Domain/ValueObjects/` | `.gitkeep` only |
| `Domain/Events/` | `.gitkeep` only |
| `Domain/Exceptions/` | `.gitkeep` only |
| `Infrastructure/Persistence/` | `.gitkeep` only |
| `Infrastructure/Repositories/` | `.gitkeep` only |
| `Infrastructure/Migrations/` | `.gitkeep` only |
| `Presentation/Livewire/` | `.gitkeep` only |
| `Presentation/Views/` | `.gitkeep` only |
| `Presentation/Controllers/` | `.gitkeep` only |
| `Presentation/Routes/` | `.gitkeep` only |
| `database/migrations/modules/workflow/` | `.gitkeep` only — **no migration PHP files** |

### 3.3 Classification

| Question | Verdict |
|---|---|
| Does Workflow module exist? | **Yes — placeholder only** |
| Is Workflow Engine implemented? | **No** |
| Workflow Application contracts/actions available? | **No** |
| Workflow persistence / domain entities? | **No** |

---

## 4. Existing Workflow Application Actions / Use Cases

| Surface | Evidence |
|---|---|
| Workflow Application Services | **Absent** |
| Workflow Application Contracts / ports | **Absent** |
| Workflow DTOs / ViewModels | **Absent** |
| Mutation capability keys under `workflow.*` | **Absent** in `MutationCapabilityCatalog` |
| Artisan / console Workflow commands | **Absent** |

**Status:** `UNKNOWN` is not used here for “maybe present” — absence is **verified**. There are **zero** Workflow Application use cases to bind a UI to.

---

## 5. Existing Workflow Domain Boundaries (documented + code)

### 5.1 Catalog / CD-010 (normative boundary)

| Source | Boundary |
|---|---|
| CD-010 | **Request** owns `RequestApproval` entity, approval **state**, history |
| CD-010 | **Workflow** (deferred) owns approval **transition rules**, chain definition, routing, orchestration **when activated** |
| Spec catalog Deferred Components | Workflow Engine not an active standalone spec; activation criteria: ≥2 multi-stage workflows, shared transition behavior, justified reusable engine |
| Integration pattern (docs) | Request emits events; Workflow would subscribe when activated; final outcomes returned via Domain Event |

### 5.2 Repository reality vs deferred ownership

| Concern | Owner in repository today |
|---|---|
| Approval state machine (pending stages → Approved/Rejected) | **Request** Domain states |
| Inline stage routing | **Request** `ApprovalStageResolver` |
| Append-only approval rows | **Request** `RequestApproval` + repository |
| Auto-approval settings | **Request** `AutoApprovalSettingsReader` |
| Workflow transition-rule engine | **Not implemented** (deferred) |

Deferred catalog language is **not** converted into UI requirements by this inspection.

---

## 6. Existing Routes Related to Workflow or Approval Flows

### 6.1 Workflow routes

| Surface | Evidence |
|---|---|
| `app/Modules/Workflow/Presentation/Routes/` | `.gitkeep` only |
| Named routes `workflow.*` | **No matches** under `routes/` or Workflow presentation |
| Layout nav Workflow entry | **Absent** |

### 6.2 Request approval-related routes (adjacent — Request-owned)

| Route name | Method / path pattern | Handler |
|---|---|---|
| `requests.mutations.approve` | `POST /{requestId}/approve` (module API routes) | `RequestMutationController::approve` → `ApproveRequestStageAction` |
| `requests.mutations.reject` | `POST /{requestId}/reject` | `RequestMutationController::reject` → `RejectRequestAction` |
| `requests.show` | Livewire GET detail | `RequestShowPage` — **no** approve/reject Livewire actions |
| `requests.flow.show` | JSON/API show | `RequestFlowController::show` |

These routes are **Request presentation**, not Workflow module surfaces.

---

## 7. Existing Livewire / Blade / UI Surfaces

### 7.1 Workflow UI

| Surface | Evidence |
|---|---|
| Workflow Livewire | **Absent** (`.gitkeep`) |
| Workflow Blade views | **Absent** (`.gitkeep`) |
| Workflow Controllers | **Absent** (`.gitkeep`) |

### 7.2 Request UI touching approval (adjacent)

| Surface | Behavior evidenced |
|---|---|
| `RequestShowPage` + `request-show-page.blade.php` | Displays `approvalHistory` (stage, decision, approver_reference, reason, decided_at) |
| Request Show Livewire actions | **No** approve/reject/submit mutation methods on the component |
| `RequestShowUiFlowTest` | Asserts detail page **exposes no workflow mutation controls** |
| Frozen contract `request-show` | Pilot = read-only detail + approval history; **workflow mutations explicitly out of scope** |
| Product auth excluded scope | Request Show workflow mutations **excluded** from `workflow-ui` authorization |

**Verdict:** No existing UI surface exposes **Workflow-module** behavior. Request Show exposes **Request-owned** approval history as read-only display only.

---

## 8. Existing Authorization Boundaries

| Boundary | Evidence | Module |
|---|---|---|
| `MutationCapabilityCatalog::REQUEST_APPROVE` (`request.approve`) | Enforced in `ApproveRequestStageAction` | Request |
| `MutationCapabilityCatalog::REQUEST_REJECT` (`request.reject`) | Enforced in `RejectRequestAction` | Request |
| `RequestMutationAuthorizationGate::assertApprove` / `assertReject` | Approver must match actor; current stage checks | Request |
| HTTP FormRequests prohibit spoofing `approverId` | `ProhibitsMutationIdentitySpoofingFields` | Request |
| Workflow-specific Gate / Policy / capability flags | **Absent** | Workflow |
| Backend-provided Workflow UI capability payload (`can_*` for Workflow) | **Absent** | Workflow |

---

## 9. Existing Tests

### 9.1 Workflow module tests

| Suite | Evidence |
|---|---|
| `tests/Unit/Modules/Workflow/**` | **Absent** |
| `tests/Feature/Modules/Workflow/**` | **Absent** |
| Provider migration path smoke | `ModuleMigrationPathsTest` references `WorkflowServiceProvider` + `workflow` path |

### 9.2 Request approval / “workflow” wording in tests (adjacent)

| Test | What it proves |
|---|---|
| `RequestApprovalTest` | Four-stage approve to `Approved`; append-only rows |
| `RequestHttpMutationTest` | Authenticated approve/reject HTTP; wires to Application actions |
| `RequestMutationAuthorizationTest` | Auth gate; registers request workflow actions/capability keys |
| `RequestShowUiFlowTest` | UI has **no** workflow mutation controls |
| `AutoApprovalSettingsReaderTest` | Auto-approval settings reader |

No test asserts a Workflow-module Application or UI surface.

---

## 10. Request ↔ Workflow Ownership Boundaries (verified)

| Concern | Owner (docs) | Owner (code today) |
|---|---|---|
| `RequestApproval` state/history | Request (CD-010) | **Request** |
| Inline four-stage transition rules (Wave 1) | Request while Workflow deferred (OA-05-02) | **Request** |
| Reusable Workflow Engine / chain definition | Workflow when activated | **Not present** |
| Domain events for future Workflow subscription | Request emits (spec05 FR-009) | Request events present (`RequestApprovalRecorded`, `RequestApproved`) — **no Workflow subscribers found** |
| Livewire approver UI | Deferred in spec05 (FR-EX-010) | Request Show = history only; approve/reject = HTTP API only |

**Do not treat Request approval HTTP/API or history display as Workflow UI evidence.**

---

## 11. Missing Capabilities

| Gap | Evidence status |
|---|---|
| Workflow Domain entities / engine | **Missing** |
| Workflow Application contracts / actions / DTOs | **Missing** |
| Workflow migrations / persistence | **Missing** |
| Workflow web routes / Livewire / Blade | **Missing** |
| Workflow mutation capability keys | **Missing** |
| Workflow UI capability flags from backend | **Missing** |
| Workflow-specific tests | **Missing** |
| Product-defined exact Workflow UI MVF screens/actions | **`TBD_BY_PRODUCT`** (authorization artifact) — not inventable from repo |
| Workflow Engine activation (catalog criteria) | **Not met / deferred** — not converted to UI requirement |

---

## 12. Dependency Blockers

| Dependency | Status | Notes |
|---|---|---|
| Product authorization for intake | **Satisfied** | Permits repo-inspection |
| Exact Workflow UI scope | **`TBD_BY_PRODUCT`** | Blocks defining consumable MVF from Product alone |
| Workflow Application surface for UI consumption | **Missing** | Primary technical blocker for Workflow-domain UI without backend expansion |
| Workflow Engine activation | **Deferred** | Product auth **excludes** using activation as substitute for this UI grant |
| Request approval Application + HTTP | **Present** | **Adjacent only**; Product excludes Request Show workflow mutations from this feature |
| Frozen `request-show` / request list UI | **Closed/frozen** | Must not reopen under `workflow-ui` without separate Product decision |
| `employee-context-ui` | **FEATURE_CLOSED** | Must not reopen |

**Can UI consume existing Workflow backend capabilities without expansion?**  
**No.** Workflow Application capabilities do not exist.

**Overall dependency status for Workflow UI implementation readiness:** **`NOT_READY`** (verified absence — not classified as `READY`; residual Product scope remains `TBD_BY_PRODUCT` / partially `UNKNOWN` for future intended surfaces).

---

## 13. Candidate UI Surfaces Supported by Repository Evidence

Surfaces that could **later** be considered **only if** Product later defines them **and** ownership/authorization allow — listed as evidence mapping, **not** requirements:

| Candidate surface | Supporting evidence | Classification |
|---|---|---|
| Any Workflow-module Livewire/admin screen | **None** — placeholder module | **Not supported** by current repository |
| Workflow engine configuration / chain editor UI | **None** in Workflow Application | **Not supported** |
| Approver approve/reject Livewire on Request Show | Request HTTP `ApproveRequestStageAction` / `RejectRequestAction` exist | **Request-owned adjacent capability**; **excluded** by Product auth + frozen `request-show` — **not** a `workflow-ui` candidate under current authorization |
| Approval history read-only display | Already delivered on Request Show | **Request UI** (closed/frozen pilot) — **not** Workflow UI |
| Layout nav to “Workflow” | No route/page | **Not supported** |

Deferred Workflow Engine catalog items and Request FR-EX-010 Livewire deferral are **historical/deferral evidence only** — not converted into Workflow UI requirements.

---

## 14. Special Checks (explicit)

| Check | Result |
|---|---|
| Workflow module exists or placeholder? | **Placeholder only** |
| Workflow Application contracts/actions available? | **No** |
| Approval transition logic exists? | **Yes — in Request module** (inline Wave 1) |
| Request owns approval state/history? | **Yes** (CD-010 + code) |
| Existing UI exposes Workflow behavior? | **No** |
| UI can consume existing Workflow backend without expansion? | **No** |

---

## 15. Spec / Catalog Status vs Repository Reality

| Claim | Repository reality |
|---|---|
| Workflow Engine deferred (catalog / CD-010) | Confirmed — placeholder module only |
| Request owns `RequestApproval` (CD-010) | Confirmed |
| Inline routing while Workflow deferred (OA-05-02) | Confirmed in Request Application/Domain |
| No Workflow implementation in spec05 | Confirmed — no Workflow code beyond shell |
| Livewire approver UI deferred (FR-EX-010) | Request Show has no mutation controls; approve/reject via HTTP API only |
| Product `workflow-ui` scope | `TBD_BY_PRODUCT` — no repo evidence fills this |

---

## 16. Explicit Non-Actions

This inspection did **not**:

- Implement code or modify routes/UI/Application/Domain/Infrastructure
- Create feature-analysis, feature-contract, or implementation-lock
- Infer missing Workflow UI scope from code, routes, TODOs, deferred OA, or roadmap order
- Convert deferred Workflow backend activation into UI requirements
- Reopen `employee-context-ui` or frozen Request Show mutation scope
- Classify insufficient/absent Workflow Application evidence as `READY`

---

## 17. Repository readiness status

**`REPO_INSPECTION_COMPLETE_NOT_READY`**

Rationale:

1. Product authorization permitted this inspection gate.
2. Workflow module is a verified placeholder with **no** Application contracts for UI consumption.
3. Approval transitions that exist are **Request-owned** and are **not** authorized as `workflow-ui` surfaces by Product (and Request Show mutations remain frozen/excluded).
4. Exact Product UI scope remains `TBD_BY_PRODUCT`.
5. Per inspection rules: insufficient Workflow UI-consumable evidence is **not** classified as `READY`.

---

## 18. Confirmed next governance gate

**`feature-analysis`**

Expected next artifact (not created in this task):

`docs/ui/analysis/workflow/workflow-ui.feature-analysis.md`

Constraint for that gate: must not invent Workflow UI requirements from Request approval APIs, deferred catalog activation language, or placeholder directories; must confront `TBD_BY_PRODUCT` and the missing Workflow Application surface as primary findings.

---

## 19. Blocking findings

### Blocking for Workflow UI consumption / readiness

| Finding | Classification |
|---|---|
| Workflow Application contracts/actions absent | **Blocking** — no Workflow backend for UI to consume without expansion |
| Workflow module placeholder only (no domain/engine/persistence) | **Blocking** for Workflow-domain UI |
| Product exact scope still `TBD_BY_PRODUCT` | **Blocking** for MVF definition from Product alone |
| Request approval capabilities are Request-owned; Product excludes Request Show workflow mutations and Workflow Engine activation-as-substitute | **Blocking** against treating adjacent Request surfaces as authorized `workflow-ui` evidence |

### Blocking for this gate (repo-inspection completion)

**None** — inspection completed; evidence recorded.

### Non-blocking / adjacent notes

| Finding | Classification |
|---|---|
| Request HTTP approve/reject + tests exist | Adjacent Request capability — out of authorized `workflow-ui` scope unless Product later re-scopes |
| Request Show approval history UI exists | Frozen Request pilot — not Workflow UI |
| Catalog activation criteria unmet | Backend program concern; Product excluded activation as UI-auth substitute |

---

*Repository evidence inspection only. Next gate: feature-analysis.*
