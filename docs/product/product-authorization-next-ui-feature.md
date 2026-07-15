# Product Authorization: audit-ui

## 1. Decision Status

| Field | Value |
|---|---|
| **STATUS** | **`AUTHORIZED`** |
| **Decision date** | 2026-07-10 |
| **Authority source** | Product / Governance Review |
| **Supersedes** | Prior blocked posture (`BLOCKED_BY_MISSING_PRODUCT_DECISION`); does **not** reopen closed features; does **not** continue or reuse blocked `workflow-ui` / `dormitory-ui` grants |
| **Decision type** | Product Authorization — UI governance intake |

This artifact is the official Product Authorization for the next DormSys UI governance feature. It applies **only** to `audit-ui`.

---

## 2. Feature Authorization

| Field | Value |
|---|---|
| **Canonical feature slug** | `audit-ui` |
| **Feature title** | Audit UI |
| **Domain** | Audit |
| **Source specification** | `specs/010-audit-trail` |
| **Authorization** | **Approved for UI governance intake** |

### Business purpose

Product authorizes a governed Persian RTL presentation surface for the Audit domain so authorized operators and stakeholders can interact with Audit-owned trail and compliance visibility concerns under UI governance — without treating the UI as a second source of business truth, and without reopening closed or blocked prior UI features.

This decision opens **UI governance intake only**. Exact MVF screen and capability boundaries remain Product-owned where not yet specified (`TBD_BY_PRODUCT`) and must be established through subsequent governance gates starting with `repo-inspection`.

---

## 3. Scope

### Authorized scope

| Item | Status |
|---|---|
| UI governance intake for `audit-ui` | **Authorized** |
| Start gate: **`repo-inspection`** for `audit-ui` only | **Explicitly permitted** |
| Subsequent gates (feature-analysis → …) | Allowed only after each prior gate passes under normal UI governance rules |
| Presentation-layer work within Audit UI (Livewire/Blade) | In scope for later gates after lock-review authorization — **not** authorized for coding by this artifact |
| Exact Audit UI screens, actions, and capability flags | **`TBD_BY_PRODUCT`** — not inferred from audit tables, activity logs, events, code, routes, components, TODOs, deferred roadmap items, or implementation completeness |

### Excluded scope

| Item | Status |
|---|---|
| Implementation / coding | **Not authorized** by this artifact |
| Feature contract drafting / creation | **Not authorized** by this artifact |
| Implementation lock drafting / creation | **Not authorized** by this artifact |
| Unrelated Reporting UI (including KPI dashboards / explorer surfaces not authorized as this feature) | **Excluded** |
| Workflow UI | **Excluded** |
| Notification UI | **Excluded** |
| Employee UI / reopening `employee-context-ui` | **Excluded** |
| Note: Employee UI is now separately authorized under product-authorization-employee-auth-ui.md (F2). Exclusion here remains valid for THIS record's scope. | — |
| Request UI | **Excluded** |
| Dormitory UI / Lottery UI / Voucher UI / Allocation UI | **Excluded** |
| Any unauthorized domain expansion / backend expansion under this UI grant | **Excluded** unless a separate product/backend authorization is issued |
| Any feature other than `audit-ui` | **Excluded** |

---

## 4. Dependency Assessment

| Area | Status |
|---|---|
| **Backend / Application readiness for Audit UI** | Not verified as UI-consumable Application surfaces for this feature by this authorization artifact. Readiness for UI consumption must be established at repo-inspection. |
| **Prerequisites for intake** | Satisfied by this Product Authorization for starting **repo-inspection** only. |
| **Blockers for intake** | None for starting **repo-inspection**. Downstream blockers (if any) must be recorded at repo-inspection / feature-analysis. |
| **Dependency status** | **`UNKNOWN`** |

---

## 5. Governance Transition

| Field | Value |
|---|---|
| **Next allowed action** | Begin UI governance for `audit-ui` |
| **Next allowed governance gate** | **`repo-inspection`** for `audit-ui` only |
| **Explicit permission to start repo-inspection** | **Yes** |

Expected first artifact path (for a later task, **not** created here):

`docs/ui/analysis/audit/audit-ui.repo-inspection.md`

(or repository-equivalent under `docs/ui/analysis/` consistent with existing conventions)

---

## 6. Constraints

- This artifact **does** authorize UI governance intake for **`audit-ui`** and **explicitly permits** starting **`repo-inspection`** for that feature only.
- This artifact does **not** authorize implementation.
- This artifact does **not** create repo-inspection, feature-analysis, feature-contract, contract-review, implementation-lock, or lock-review content.
- This artifact does **not** reopen closed features or continue blocked prior UI grants.
- This artifact does **not** authorize any feature other than `audit-ui`.
- No coding may begin until a later lock-review returns `APPROVED_FOR_IMPLEMENTATION`.
- Agents must not expand scope into excluded domains or infer authorized UI capabilities from non-Product sources.

---

*Official Product Authorization. Stop boundary for this task: authorization artifact only.*
