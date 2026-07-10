# Employee Context UI — Review Decision

## Feature

| Field | Value |
|---|---|
| **Feature code** | `employee-context-ui` |
| **Feature title** | Employee Context UI |
| **Domain area** | employee |
| **Classification** | `UI_PRESENTATION_GAP` |
| **Decision date** | 2026-07-10 |

## Review objective

Decide whether `employee-context-ui` is authorized to proceed through governance, confirm the **narrow MVF** bounded to delivered Application surfaces, resolve feature-analysis open questions **RD-EC-001 through RD-EC-005**, and authorize the **exact next artifact**. This review does **not** authorize implementation, draft a contract or lock, or modify application/backend code.

---

## 1. Decision summary

| Field | Decision |
|---|---|
| **Verdict** | **Approved** |
| **Disposition** | `APPROVED_READY_FOR_CONTRACT` |
| **Status (equivalent)** | `APPROVED_FOR_CONTRACT` |
| **Implementation authorized?** | **No** — feature-contract drafting is the next authorized step only |
| **Blockers** | **None** |

`employee-context-ui` is approved for **feature-contract** drafting as a **narrow presentation MVF** over already-delivered Employee Application mutations only. Contract creation is the **required and authorized** next governance artifact. **Implementation remains unauthorized.** Contract, lock, and code must **not** be created as part of this review-decision task.

---

## 2. Inputs reviewed

| Artifact | Role |
|---|---|
| `docs/product/product-authorization-next-ui-feature.md` | Product authorization (`AUTHORIZED`; slug `employee-context-ui`) |
| `docs/ui/analysis/employee/employee-context-ui.repo-inspection.md` | Repository truth (Application surfaces; no Employee web UI) |
| `docs/ui/analysis/employee/employee-context-ui.feature-analysis.md` | Primary decision basis; open RD-EC-001–005 |
| `docs/architecture/ui/UI-ANTI-LEAK-CONTRACT.md` | UI governance (thin Livewire; no business authority in UI) |
| Existing UI review-decision conventions (e.g. notification P7) | Disposition / gate naming |

---

## 3. Decision question resolutions (RD-EC-001 through RD-EC-005)

| ID | Question | Decision |
|---|---|---|
| **RD-EC-001** | Full four-mutation MVF vs narrower first slice | **Approve the four-mutation MVF** as listed in §4 — not a further-narrowed subset. Scope stays strictly on delivered Application actions; no list/search/profile expansion. |
| **RD-EC-002** | Single hub page vs separate routes per mutation | **Single Employee Hub Page** hosts Create Employee, Create Department, Assign Department, and Deactivate Department. Separate per-mutation public routes are **not** required for this MVF (contract may define one hub route + optional internal sections). |
| **RD-EC-003** | Layout label/copy (Persian) and nav placement | **Nav label:** **کارکنان**. **Placement:** third shared-layout nav item, immediately after **اعلان‌ها** (order: درخواست‌ها → اعلان‌ها → کارکنان). Plain `href` to the hub route; active state via `request()->routeIs('…')` matching existing layout nav pattern. Exact route name is deferred to feature-contract. |
| **RD-EC-004** | Post-create detail panel vs success flash + id | **Success flash + returned identifier display is enough.** A dedicated employee/department profile or edit panel is **out of MVF**. Optional hub confirmation via existing point-read (`findById` / equivalent) after mutation is allowed only as non-authoritative display of backend-returned data — not profile editing. |
| **RD-EC-005** | Additional capability flags for action visibility | **No additional backend capability flags required for this MVF.** Forms may be shown to authenticated users who reach the hub; mutation authorization remains backend-authoritative via existing Employee mutation policy / gate. Failures surface as backend outcomes. **Do not** introduce new Application APIs or capability fields as part of this feature. |

---

## 4. Approved MVF (for feature-contract drafting only)

The following is **approved for feature-contract drafting** (not implementation):

| # | Surface / capability | Boundary |
|---|---|---|
| 1 | **Employee Hub Page** | Single authenticated Persian RTL Livewire (or equivalent presentation) hub that hosts the four mutation forms below; UUID/id text inputs where selectors are not delivered |
| 2 | **HR / Employee navigation entry** | Shared layout nav link (**کارکنان**) to the hub; discoverability only |
| 3 | **Create Employee form** | Thin UI → `CreateEmployeeAction` (or Application-equivalent already delivered) |
| 4 | **Create Department form** | Thin UI → `CreateDepartmentAction` |
| 5 | **Assign Department form** | Thin UI → `AssignDepartmentToEmployeeAction`; explicit employee/department id inputs (no selector/dropdown) |
| 6 | **Deactivate Department action** | Thin UI → `DeactivateDepartmentAction` |

### Approved technical boundary (contract must respect)

| Dimension | Approved boundary |
|---|---|
| **Feature type** | UI presentation over delivered Application mutations |
| **Backend / Application expansion** | **None** — consume only already-delivered Employee Application surfaces |
| **Inputs** | Explicit UUID/id (and other action-required fields) text inputs — **no** employee/department list UX |
| **Confirmation** | Flash + id; optional point-read display only |
| **Auth** | Existing mutation policy / gate; no Identity admin UX |
| **Anti-leak** | Thin Livewire → Application only; no Domain/Infrastructure imports in Presentation; no business-authoritative UI logic |

---

## 5. Explicit exclusions (rejected for this feature)

The following are **rejected** for `employee-context-ui` and must **not** appear in feature-contract, implementation-lock, or implementation unless a **future** product-authorized feature explicitly includes them:

| Excluded item | Disposition |
|---|---|
| Employee listing | **Excluded** |
| Employee search | **Excluded** |
| Employee profile editing | **Excluded** |
| Department tree UI | **Excluded** |
| Employee selector / dropdown | **Excluded** |
| Dependents UI | **Excluded** |
| Full HR admin panel | **Excluded** |
| Identity / Auth UX (login, SSO, Identity admin) | **Excluded** |
| Backend expansion (new Application APIs, list/read contracts, capability flags, migrations, etc.) | **Excluded** — not introduced by this decision |
| Reopening closed notification UI features (P2–P9) | **Excluded** |
| Reopening closed request UI features | **Excluded** |
| Employee deactivate Application action (not delivered) | **Excluded** — no new backend requirement |
| Eligibility admin UI | **Excluded** |
| Console command changes as product scope | **Excluded** |

---

## 6. Ownership decision

| Layer | Change authorized by this review? |
|---|---|
| Feature-contract artifact (next gate) | **Yes** — drafting authorized |
| Implementation-lock | **No** — not this gate |
| Application code / Livewire / Blade / routes | **No** — implementation not authorized |
| Employee Application / Domain / Infrastructure | **No** — no backend expansion |
| Identity / Auth presentation | **No** |
| Notification / Request closed UI surfaces | **No** |

**Rationale:** Product authorization and repo-inspection confirm a presentation gap over four delivered mutations. The approved MVF is the maximum UI slice that does not invent list/search/profile/dependent/backend work.

---

## 7. Governance gate authorization

| Gate | Authorized now? |
|---|---|
| **feature-contract** | **Yes** — **next allowed gate** |
| contract-review | No — after contract exists |
| implementation-lock | No |
| lock-review | No |
| implementation | **No** |
| verification / closeout | No |

### Next allowed artifact

**Feature contract** at:

`docs/ui/contracts/employee/employee-context-ui.feature-contract.yaml`

(or repository-equivalent under `docs/ui/contracts/employee/`)

### Explicit non-authorization

This review decision:

- **Does not** authorize implementation
- **Does not** authorize creating the feature-contract **in this task** (contract is the *next* gate for a subsequent task)
- **Does not** authorize creating an implementation-lock
- **Does not** authorize writing or modifying application/backend code
- **Does not** introduce new backend requirements

---

## 8. Contract drafting constraints (for the next gate)

When the feature-contract is drafted in a **later** task, it must:

1. Encode **only** the approved MVF in §4
2. Encode **all** exclusions in §5
3. Reflect RD-EC-001–005 resolutions in §3
4. Require thin Livewire → existing Application actions only
5. Forbid list/search/profile/tree/selector/dependents/Identity UX/backend expansion
6. Forbid reopening closed notification or request UI features
7. State that implementation remains unauthorized until lock + lock-review (per project UI governance)

---

## 9. Residual risks (accepted for contract phase)

| Risk | Acceptance |
|---|---|
| UUID/id text entry is operationally awkward | **Accepted** for MVF — selectors require undelivered list/read UX |
| Hub shows forms without pre-flight capability flags | **Accepted** — backend mutation auth remains authoritative (RD-EC-005) |
| Spec US3 dependents remain unmet | **Accepted** — out of MVF; separate future authorization |

---

## 10. Status

**`APPROVED_READY_FOR_CONTRACT`** (`APPROVED_FOR_CONTRACT`)

Next governance gate: **`feature-contract`**

Implementation: **not authorized**

This task must **not** create: feature-contract, implementation-lock, or code.

---

*Review decision only. Next gate: feature-contract (separate task).*
