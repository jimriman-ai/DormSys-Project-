# Dormitory UI — Feature Analysis

## Feature

| Field | Value |
|---|---|
| **Canonical feature slug** | `dormitory-ui` |
| **Feature title** | Dormitory UI |
| **Domain** | Dormitory |
| **Source specification** | `specs/004-accommodation-resource` |
| **Analysis date** | 2026-07-10 |
| **Gap classification** | `NO_VALID_UI_SCOPE` — Dormitory Application surface absent; Spec04 implementation hold; Product MVF still `TBD_BY_PRODUCT` |
| **Product authorization** | `docs/product/product-authorization-next-ui-feature.md` — **`AUTHORIZED`** for intake only |
| **Prior gate** | `repo-inspection` → `REPO_INSPECTION_COMPLETE_NOT_READY` |

## Analysis objective

Determine whether any evidence-bounded Dormitory UI MVF exists that:

1. Consumes **only Dormitory-module** Application capabilities already delivered.
2. Does **not** treat Request or Allocation consumer stubs as Dormitory UI capability.
3. Does **not** convert deferred Spec04 / Phase H items into UI requirements.
4. Stays within Product Authorization (including exclusions and `TBD_BY_PRODUCT`).
5. States clearly whether contract phase may proceed.

---

## Inputs considered

| Input | Role |
|---|---|
| `docs/product/product-authorization-next-ui-feature.md` | Authoritative intake; scope `TBD_BY_PRODUCT`; exclusions |
| `docs/ui/analysis/dormitory/dormitory-ui.repo-inspection.md` | Primary repository truth |
| `.specify/governance/_meta/authority-model.md` | Authorization ≠ inference |
| `.specify/docs/spec-catalog.md` | spec04 Planning Authorized; implementation hold |
| `.specify/docs/catalog-decisions.md` | **CD-014** physical state ownership |
| `specs/004-accommodation-resource/spec.md` | Spec authored; implementation not authorized |
| `specs/004-accommodation-resource/plan.md` | Phase H Livewire deferred; Wave 1 when impl authorized |
| `specs/004-accommodation-resource/tasks.md` | Phase H deferred from MVP task set |
| `docs/ui/review/backlog-authority-discovery.md` | Historical Phase H blocked by implementation hold |
| `docs/ui/analysis/workflow/workflow-ui.feature-analysis.md` | Parallel no-valid-scope pattern |

---

## 1. Feature Analysis Summary

Repo-inspection established that `app/Modules/Dormitory` is a **scaffold only**: no Domain entities, no Application contracts/actions/DTOs, no migrations, no routes, no Livewire/Blade, and no Dormitory-module tests (repo-inspection §§3–7, §13).

Spec04 is **planning-authorized**; **implementation is not authorized** (spec status, plan governance, catalog hold — repo-inspection §11). Planned supplier `DormitoryReadContract` exists as planning documentation only; live consumers use **Null adapters** in Request and Allocation (repo-inspection §§3.4, §5.3).

Product Authorization permits UI governance intake but leaves exact MVF as **`TBD_BY_PRODUCT`** and excludes backend expansion, Allocation UI, Request UI changes, Lottery/Voucher/Workflow UI, and related surfaces.

**Conclusion:** There is **no valid evidence-bounded Dormitory UI scope**. Contract drafting is **not** possible. Next disposition is Product clarification plus Spec04 Application delivery (separate program authorization), not feature-contract.

---

## 2. Confirmed Available Capabilities

Evidence: repo-inspection §§3–5, §10.

| Capability class | Available for UI consumption? | Evidence |
|---|---|---|
| Dormitory Domain entities (site/building/room/bed) | **No** | Domain dirs `.gitkeep` only; README scaffold statement |
| Dormitory Application contracts | **No** | `Application/Contracts/.gitkeep` |
| Dormitory Application actions / services | **No** | `Application/Services/.gitkeep` |
| Dormitory DTOs / read models / capability payloads | **No** | `Application/DTOs/.gitkeep` |
| Dormitory persistence / migrations | **No** | migration path `.gitkeep` only |
| Dormitory mutation capability keys (`dormitory.*`) | **No** | Absent from `MutationCapabilityCatalog` |
| Dormitory web routes / Livewire / Blade / layout nav | **No** | Presentation `.gitkeep`; no layout entry |

### Adjacent non-capabilities (explicitly not Dormitory UI)

| Surface | Module | Why not Dormitory UI | Evidence |
|---|---|---|---|
| `Request\...\DormitoryReadContract` + Null adapter | Request | Consumer stub; UUID-format `siteExists` only | Repo-inspection §5.3 |
| `Allocation\...\DormitoryReadPort` + Null adapter | Allocation | Consumer stub until live supplier | Repo-inspection §5.3 |
| Request create/list/show dormitory UUID fields | Request UI | Product excludes Request UI changes | Product auth §3; repo-inspection §4.2 |

**Confirmed Dormitory capabilities available for UI consumption:** **none**.

---

## 3. MVF Scope Assessment

### 3.1 Evidence-bounded MVF search

| Candidate MVF idea | Supported by Dormitory Application evidence? | Blocked by |
|---|---|---|
| Catalog admin (sites/buildings/rooms/beds) | No | No Application actions; Spec04 impl hold |
| Physical status management UI | No | No Domain/Application delivery |
| External dormitory registration UI | No | Same |
| Read-only dormitory catalog / picker | No | No Dormitory read contracts/DTOs |
| Layout nav to Dormitory admin | No | No route/page; nothing to bind |
| Request `dormitoryId` field as Dormitory UI | Adjacent only | Product exclusion + ownership |
| Allocation bed UI as Dormitory UI | Adjacent only | Product excludes Allocation UI |
| Phase H Livewire from plan/tasks | Deferred text only | Must not convert deferral into requirements |

### 3.2 Valid UI scope statement

**No valid UI scope exists** for `dormitory-ui` under current repository evidence and current Product Authorization boundaries.

| Statement | Status |
|---|---|
| Evidence-supported Dormitory mutation MVF | **None** |
| Evidence-supported Dormitory read-only MVF | **None** |
| Product-defined exact screens/actions | **`TBD_BY_PRODUCT`** |
| Invented MVF from Phase H / Null stubs / Request fields | **Forbidden** |

---

## 4. Application Contract Readiness

| Question | Verdict | Evidence |
|---|---|---|
| Can UI mutations bind to existing Dormitory Application actions? | **No** | Repo-inspection §5.1 — zero actions |
| Can read-only UI bind to existing Dormitory read contracts? | **No** | Repo-inspection §5.1 — zero contracts/DTOs |
| Is planning `dormitory-read-service.md` a delivered bind target? | **No** | Status: planning; implementation not authorized (repo-inspection §5.2) |
| May Request/Allocation Null ports be used as Dormitory UI contracts? | **No** | Analysis rule + Product exclusions; repo-inspection §10 |

**Application contract readiness for UI binding:** **not ready**.

---

## 5. Dependency and Blocking Analysis

| Dependency | Status | Evidence | Implication |
|---|---|---|---|
| Product intake authorization | Satisfied | Product auth `AUTHORIZED` | Analysis gate allowed |
| Exact Product MVF scope | **`TBD_BY_PRODUCT`** | Product auth §3 | Blocks contract boundary definition |
| Spec04 implementation authorization | **Not granted** (hold) | Spec/plan/catalog; repo-inspection §9, §11 | Blocks Application delivery prerequisite |
| Dormitory Application surface | **Missing** | Repo-inspection §§3–5, §13 | Blocks any UI bind without backend expansion |
| Backend expansion under this UI grant | **Excluded** | Product auth | Cannot invent Application APIs in UI feature |
| Allocation / occupancy / Check-In UI | **Excluded** | Product auth; CD-014/015 docs only | Must not expand scope |
| Request UI dormitory UUID | **Excluded** | Product auth | Must not re-scope as Dormitory UI |
| Workflow UI | **Excluded** | Product auth | Separate blocked chain |

**Progression to contract:** **blocked**.

---

## 6. Forbidden Scope

| Forbidden | Authority / evidence |
|---|---|
| Allocation UI | Product auth excluded scope |
| Request UI changes (including treating dormitory UUID fields as Dormitory UI) | Product auth; repo-inspection §4.2 |
| Lottery UI | Product auth |
| Voucher UI | Product auth |
| Check-In / Check-Out UI | Product auth (no expansion); CD-015 separate context |
| Workflow UI | Product auth |
| Backend / Domain / Infrastructure expansion under this UI grant | Product auth |
| Converting Spec04 Phase H / deferred Wave 1 text into UI requirements | Analysis rule; plan/tasks deferral |
| Treating Request/Allocation Null consumer stubs as Dormitory UI capabilities | Analysis rule; repo-inspection §10 |
| Reopening `employee-context-ui` | Product auth; `FEATURE_CLOSED` |
| Empty decorative Dormitory page with no Application bind | Unsupported as governed MVF |

---

## 7. Proposed Contract Boundary

**None — no valid contract scope.**

Do not propose a feature-contract.

One-line boundary:

> No Dormitory UI contract scope is proposable: the Dormitory module has no Application contracts/actions for UI consumption; Spec04 implementation remains unauthorized; Product MVF remains `TBD_BY_PRODUCT`; Request/Allocation dormitory stubs and Request UI fields are out of authorized `dormitory-ui` scope.

---

## 8. Feature Analysis Status

**`FEATURE_ANALYSIS_COMPLETE_NO_VALID_UI_SCOPE`**

Analysis is complete. No evidence-bounded Dormitory UI MVF can be proposed. Contract phase is **not** unlocked.

---

## 9. Blocking Decisions

| ID | Blocking decision required | Owner |
|---|---|---|
| BD-DR-001 | Define exact authorized Dormitory UI screens, actors, and actions (replace `TBD_BY_PRODUCT`) | **Product** |
| BD-DR-002 | Authorize Spec04 implementation (or equivalent) and deliver Dormitory Application contracts/actions matching that UI scope — **or** suspend/withdraw `dormitory-ui` until program readiness allows | **Product + Architecture / program authorization** |
| BD-DR-003 | Reaffirm that Request/Allocation consumer stubs and Request dormitory UUID UI remain **out of scope** for `dormitory-ui` | **Product** (reaffirm) |

Until BD-DR-001 and BD-DR-002 are resolved with repository-visible Dormitory Application surfaces matching Product scope, **no** feature-contract drafting is authorized by evidence.

---

## 10. Next Governance Gate

**`REQUEST_PRODUCT_CLARIFICATION`**

Not `feature-contract`. Not `review-decision` for contracting.

Expected disposition (not created here): Product / Governance Review must either:

1. Clarify `dormitory-ui` MVF **and** authorize Spec04 (or scoped) Application delivery, then re-enter at `repo-inspection` or a scoped re-analysis after backend evidence exists, **or**
2. Suspend / withdraw `dormitory-ui` UI governance until Spec04 implementation readiness is program-authorized.

---

## Explicit non-actions

This analysis did **not**:

- Create a feature contract or implementation lock
- Write or modify Application/backend/UI code
- Expand scope beyond Product Authorization
- Convert deferred Spec04 / Phase H items into requirements
- Treat Request or Allocation consumer stubs as Dormitory UI capabilities
- Invent UI surfaces without repository evidence
- Reopen closed features

---

*Feature analysis only. Next gate: REQUEST_PRODUCT_CLARIFICATION.*
