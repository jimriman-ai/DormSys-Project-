# Dormitory UI — Repository Inspection

## Feature

| Field | Value |
|---|---|
| **Canonical feature slug** | `dormitory-ui` |
| **Feature title** | Dormitory UI |
| **Domain** | Dormitory |
| **Source specification** | `specs/004-accommodation-resource` |
| **Product authorization** | `docs/product/product-authorization-next-ui-feature.md` — **`AUTHORIZED`** for UI governance intake; **`repo-inspection` explicitly permitted**; exact UI scope **`TBD_BY_PRODUCT`** |

## Inspection date

2026-07-10

## Inspection scope

Repository-observable facts only for Dormitory UI intake. No product re-selection, no inferred UI requirements from Phase H / deferred plan language, no conversion of Allocation/Check-In/Request consumer stubs into Dormitory UI requirements, no feature-analysis conclusions beyond readiness classification.

---

## 1. Repository Evidence Summary

**The Dormitory module exists only as an architectural scaffold.** Provider registration and empty layer directories (`.gitkeep`) are present. There is **no** Dormitory Domain logic, Application contract/action, DTO, repository, migration PHP, route, Livewire component, Blade view, mutation capability key, or Dormitory-module test suite.

**Spec04 (`specs/004-accommodation-resource`) is planning-authorized; implementation is not authorized** (spec status, plan governance, catalog hold). Planned supplier surface `DormitoryReadContract` is documented under planning contracts but **not implemented** inside `app/Modules/Dormitory`.

**Consumer stubs exist outside Dormitory:** Request binds `NullDormitoryReadAdapter` for `App\Modules\Request\Application\Contracts\DormitoryReadContract` (`siteExists` UUID-format check only). Allocation binds `NullDormitoryReadAdapter` for `DormitoryReadPort` (`bedExists` / `isBedAssignable` stubs). These are **not** Dormitory Application capabilities and are **not** Product-authorized as Dormitory UI surfaces.

**Request UI** shows dormitory UUID fields as free-text / display references only — Request presentation, not Dormitory catalog UI. Product authorization **excludes** Request UI changes, Allocation UI, Lottery UI, Voucher UI, Check-In/Check-Out expansion, and Workflow UI.

**Special-check verdict:** UI **cannot** consume Dormitory-module backend capabilities without expansion — **no Dormitory Application surfaces exist**. Spec04 implementation hold and Product `TBD_BY_PRODUCT` further prevent evidence-bounded MVF definition at this gate.

Product authorization is present and permitted this gate. Repository readiness for a Dormitory-domain UI that consumes Dormitory Application contracts is **`NOT_READY`**.

---

## 2. Inputs Reviewed

| Path | Role |
|---|---|
| `.specify/governance/_meta/authority-model.md` | Authorization ≠ inference |
| `docs/ui/review/governance-next-candidate-triage.md` | Prior `dormitory-admin-ui` blocked / not authorized (historical) |
| `docs/ui/review/backlog-authority-discovery.md` | Phase H blocked by spec04 implementation hold |
| `docs/product/product-authorization-next-ui-feature.md` | **AUTHORIZED** intake for `dormitory-ui` |
| `.specify/docs/spec-catalog.md` | spec04 Planning Authorized; implementation hold |
| `.specify/docs/catalog-decisions.md` | **CD-014** Dormitory owns physical state |
| `specs/004-accommodation-resource/spec.md` | Spec authored; implementation not authorized |
| `specs/004-accommodation-resource/plan.md` | Phase H Livewire deferred; Wave 1 when impl authorized |
| `specs/004-accommodation-resource/tasks.md` | Phase H deferred; no Livewire in MVP task set |
| `specs/004-accommodation-resource/contracts/dormitory-read-service.md` | Planning contract; implementation not authorized |
| `app/Modules/Dormitory/**` | Module scaffold |
| `app/Modules/Request/.../DormitoryReadContract` + Null adapter | Consumer stub |
| `app/Modules/Allocation/.../DormitoryReadPort` + Null adapter | Consumer stub |
| `resources/views/livewire/request/*` | Dormitory UUID display/input in Request UI only |
| `tests/Architecture/AllocationBoundaryTest.php` | Allocation must not import Dormitory Infrastructure |
| `tests/Feature/Modules/Allocation/DormitoryIntegrationTest.php` | Allocation physical-state signals with mocked ports |

---

## 3. Existing Dormitory Capabilities

### 3.1 Module presence

| Item | Evidence |
|---|---|
| Module directory | `app/Modules/Dormitory/` present |
| Service provider | `DormitoryServiceProvider` registered in `bootstrap/providers.php` |
| Provider `register()` | Comment only — **no bindings** |
| Provider `boot()` | `loadMigrationsFrom(.../modules/dormitory)` |
| README | “Architectural boundary scaffold only for Spec01. No business models, migrations, controllers, Livewire components, or domain services are implemented in this phase.” |
| Architecture inventory | `Dormitory` listed in `architectureModuleNames()` |

### 3.2 Domain / persistence (Dormitory module)

| Layer | Contents |
|---|---|
| `Domain/Models/` | `.gitkeep` only |
| `Domain/ValueObjects/` | `.gitkeep` only |
| `Domain/Events/` | `.gitkeep` only |
| `Domain/Exceptions/` | `.gitkeep` only |
| `Infrastructure/Persistence/` | `.gitkeep` only |
| `Infrastructure/Repositories/` | `.gitkeep` only |
| `Infrastructure/Migrations/` | `.gitkeep` only |
| `database/migrations/modules/dormitory/` | `.gitkeep` only — **no migration PHP files** |

**Domain entities / services in Dormitory module:** **None** (verified absence).

### 3.3 Classification

| Question | Verdict |
|---|---|
| Does Dormitory module exist? | **Yes — scaffold only** |
| Spec04 catalog/CRUD implemented? | **No** |
| Dormitory Application contracts/actions available? | **No** |
| Physical status / occupancy state in Dormitory? | **No** (CD-014 ownership documented; code not delivered) |

### 3.4 Adjacent non-Dormitory “dormitory” references (not Dormitory capabilities)

| Item | Location | Nature |
|---|---|---|
| `DormitoryReadContract::siteExists` | Request Application contract | Consumer port; Null adapter accepts any valid UUID |
| `DormitoryReadPort::bedExists` / `isBedAssignable` | Allocation Application port | Consumer stub until live supplier |
| `DormitorySiteId` VO | Request Domain | Request-owned UUID reference |
| Request create form `dormitoryId` field | Request Livewire/Blade | Free-text UUID entry — **Request UI** |
| Audit enum `dormitory.room_status_changed` | Audit | Event type name only; no Dormitory producer evidenced in module |

---

## 4. Existing UI Surface Inventory

### 4.1 Dormitory UI

| Surface | Evidence |
|---|---|
| Dormitory Livewire | **Absent** (`Presentation/Livewire/.gitkeep`) |
| Dormitory Blade views | **Absent** (`Presentation/Views/.gitkeep`) |
| Dormitory Controllers | **Absent** (`Presentation/Controllers/.gitkeep`) |
| Dormitory presentation routes | **Absent** (`Presentation/Routes/.gitkeep`) |
| Web/API routes named `dormitory.*` / `dormitories.*` | **No matches** under `routes/` |
| Layout nav Dormitory entry | **Absent** in `resources/views/components/layouts/` |

### 4.2 Adjacent Request UI (excluded from Product scope)

| Surface | Behavior | Classification |
|---|---|---|
| `request-create-page` | Text field “شناسه خوابگاه” / `dormitoryId` | Request UI — Product excludes Request UI changes |
| `request-list-page` / `request-show-page` | Displays dormitory id / reference | Request UI — not Dormitory catalog |

**Verdict:** No existing UI surface exposes **Dormitory-module** catalog or physical-resource behavior.

---

## 5. Application Contract / Action Inventory

### 5.1 Dormitory Application layer

| Surface | Status | Evidence |
|---|---|---|
| Application Contracts | **Absent** | `Application/Contracts/.gitkeep` |
| Application Services / Actions | **Absent** | `Application/Services/.gitkeep` |
| Application DTOs | **Absent** | `Application/DTOs/.gitkeep` |
| Mutation capability keys `dormitory.*` | **Absent** | No matches in `MutationCapabilityCatalog` |

### 5.2 Planning-only contract docs (not implemented code)

| Document | Status per file |
|---|---|
| `specs/004-accommodation-resource/contracts/dormitory-read-service.md` | Planning — implementation not authorized |
| Plan Wave 1 intent: catalog CRUD actions + `DormitoryReadContract` supplier | Deferred until implementation authorization — **not repository-delivered** |

### 5.3 Consumer stubs (not Dormitory Application inventory)

| Contract / adapter | Module | Behavior |
|---|---|---|
| `Request\...\DormitoryReadContract` + `NullDormitoryReadAdapter` | Request | `siteExists` → UUID validity |
| `Allocation\...\DormitoryReadPort` + `NullDormitoryReadAdapter` | Allocation | Stub bed existence/assignability |
| `Allocation\...\DormitoryReadAdapter` | Allocation | Wraps port (consumer-side) |

**Can UI bind mutations/queries to Dormitory Application actions/contracts?** **No** — none exist in the Dormitory module.

---

## 6. Authorization Boundary Evidence

| Boundary | Evidence | Module |
|---|---|---|
| Dormitory Gate / Policy / Spatie permissions for catalog admin | **Absent** | Dormitory |
| Dormitory mutation capability catalog keys | **Absent** | Shared Mutation catalog |
| Request mutation auth for dormitory UUID field | Request mutation gates only; dormitory id is request payload | Request |
| Allocation boundary: no Dormitory Infrastructure imports | `AllocationBoundaryTest` | Architecture |
| CD-014 ownership (docs) | Dormitory = physical state; Allocation = assignment | Catalog decisions |

**Backend-provided Dormitory UI capability flags (`can_*`):** **Absent** / **UNKNOWN** as undelivered.

---

## 7. Existing Tests

| Suite | Evidence |
|---|---|
| `tests/Unit/Modules/Dormitory/**` | **Absent** |
| `tests/Feature/Modules/Dormitory/**` | **Absent** |
| Dormitory UI flow tests | **Absent** |
| `ModuleMigrationPathsTest` | References `DormitoryServiceProvider` + `dormitory` path (scaffold smoke) |
| `AllocationBoundaryTest` | Forbids Allocation → Dormitory Infrastructure imports |
| `DormitoryIntegrationTest` (Allocation) | Allocation assign/release with **mocked** physical-state / dormitory ports — **not** Dormitory module tests |
| Request/Lottery/Voucher tests using dormitory UUIDs | Fixture UUID references — **not** Dormitory catalog coverage |

---

## 8. Missing Capabilities

| Gap | Evidence status |
|---|---|
| Dormitory Domain entities (site/building/room/bed) | **Missing** |
| Dormitory Application CRUD / status actions | **Missing** |
| Dormitory Application read contracts / list queries / DTOs | **Missing** |
| Live `DormitoryReadContract` supplier in Dormitory module | **Missing** (planning doc only; consumers use Null adapters) |
| Migrations / persistence | **Missing** |
| Web routes / Livewire / Blade / layout nav | **Missing** |
| Dormitory mutation capability keys / policies | **Missing** |
| Spec04 implementation authorization | **Not granted** (catalog / plan / spec status) |
| Product-defined exact Dormitory UI MVF | **`TBD_BY_PRODUCT`** |
| Phase H Livewire admin | Deferred in plan/tasks — **not** converted to requirements |

---

## 9. Dependency Blockers

| Dependency | Status for Dormitory UI | Notes |
|---|---|---|
| Product authorization for intake | **Satisfied** | Permits repo-inspection |
| Exact Dormitory UI scope | **`TBD_BY_PRODUCT`** | Blocks MVF from Product alone |
| Spec04 implementation authorization | **Not authorized** (hold) | Primary program blocker for Application surfaces |
| Dormitory Application surface for UI consumption | **Missing** | Primary technical blocker |
| Allocation dependency | **Out of Product UI scope** | CD-014 consumer/driver relationship exists in docs; Allocation UI excluded; Null ports are not Dormitory UI evidence |
| Occupancy / physical state in Dormitory | **Not delivered** | Owned by Dormitory per CD-014 when implemented |
| Check-In / Check-Out | **Excluded** from Product auth expansion | Separate context (CD-015); must not expand into Check-In UI |
| Employee / Request | Request uses dormitory UUID stubs only | Product excludes Request UI changes |
| Workflow UI | **Excluded** | Blocked separately |
| Closed `employee-context-ui` | Must not reopen | Product auth |

**Can UI consume existing Dormitory backend capabilities without expansion?**  
**No.**

**Overall dependency status:** **`NOT_READY`** (verified Application absence + implementation hold; Product scope remains `TBD_BY_PRODUCT`).

---

## 10. Evidence-Based Candidate UI Surfaces

Surfaces that could **later** be considered **only if** Product defines them **and** Application delivery exists — evidence mapping only, **not** requirements:

| Candidate surface | Supporting Dormitory Application evidence | Classification |
|---|---|---|
| Dormitory catalog admin Livewire (sites/buildings/rooms/beds) | **None** | **Not supported** |
| Physical status management UI | **None** | **Not supported** |
| External dormitory registration UI | **None** | **Not supported** |
| Layout nav to Dormitory admin | No route/page | **Not supported** |
| Read-only dormitory picker for other modules | No Dormitory read supplier | **Not supported** |
| Request `dormitoryId` text field | Request UI only | **Excluded** by Product auth — not a `dormitory-ui` candidate |
| Allocation bed assignment UI | Allocation module | **Excluded** by Product auth |

Phase H deferral language in `plan.md` / `tasks.md` is **historical deferral evidence only** — not converted into UI requirements.

---

## 11. Spec / Catalog Status vs Repository Reality

| Claim | Repository reality |
|---|---|
| Spec04 planning authorized | Confirmed (catalog + handoff references in plan) |
| Spec04 implementation not authorized / hold | Confirmed — no Dormitory business code |
| CD-014 Dormitory owns physical state | Documented; **not implemented** in module |
| `DormitoryReadContract` supplier planned | Planning contract only; consumers use Null adapters |
| Phase H Livewire deferred | Confirmed absent |
| Product `dormitory-ui` scope | `TBD_BY_PRODUCT` — not filled by repo |

---

## 12. Explicit Non-Actions

This inspection did **not**:

- Implement code or modify routes/UI/Application/Domain/Infrastructure
- Create feature-analysis, feature-contract, or implementation-lock
- Infer missing Dormitory UI scope from code, routes, TODOs, Phase H, or roadmap order
- Convert deferred specifications into requirements
- Treat existing Request/Allocation stubs or code as Product authorization
- Reopen closed features or expand into Allocation / Lottery / Voucher / Check-In UI

---

## 13. Repository Readiness Status

**`REPO_INSPECTION_COMPLETE_NOT_READY`**

Rationale:

1. Product authorization permitted this inspection gate.
2. Dormitory module is a verified scaffold with **no** Application contracts for UI consumption.
3. Spec04 implementation remains **not authorized** (catalog/plan/spec).
4. Exact Product UI scope remains `TBD_BY_PRODUCT`.
5. Adjacent Request/Allocation dormitory stubs are not Dormitory UI capabilities and are Product-excluded where they touch Request/Allocation UI.
6. Insufficient Dormitory UI-consumable evidence is **not** classified as ready for analysis that assumes a deliverable MVF bind.

---

## 14. Confirmed Next Governance Gate

**`feature-analysis`**

Expected next artifact (not created in this task):

`docs/ui/analysis/dormitory/dormitory-ui.feature-analysis.md`

Constraint for that gate: must not invent Dormitory UI requirements from Phase H, Null consumer adapters, or Request dormitory UUID fields; must confront `TBD_BY_PRODUCT`, implementation hold, and missing Dormitory Application surface as primary findings.

---

## 15. Blocking Findings

### Blocking for Dormitory UI consumption / readiness

| Finding | Classification |
|---|---|
| Dormitory Application contracts/actions absent | **Blocking** |
| Dormitory module scaffold only (no domain/persistence) | **Blocking** |
| Spec04 implementation not authorized (hold) | **Blocking** (program prerequisite for Application delivery) |
| Product exact scope still `TBD_BY_PRODUCT` | **Blocking** for MVF definition from Product alone |
| Consumer Null adapters / Request UUID fields are not authorized Dormitory UI evidence | **Blocking** against mis-scoping |

### Blocking for this gate (repo-inspection completion)

**None** — inspection completed; evidence recorded.

### Non-blocking / adjacent notes

| Finding | Classification |
|---|---|
| Allocation physical-state integration tests with mocks | Adjacent Allocation evidence — out of Product UI scope |
| CD-014 / CD-015 ownership docs | Boundary clarity only until Dormitory Application exists |

---

*Repository evidence inspection only. Next gate: feature-analysis.*
