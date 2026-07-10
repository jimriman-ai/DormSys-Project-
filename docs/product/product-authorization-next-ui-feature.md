# Product Authorization — Next UI Feature

## 1. Decision Status

| Field | Value |
|---|---|
| **Status** | **`AUTHORIZED`** |
| **Decision date** | 2026-07-10 |
| **Authority source** | Product / Governance Review |
| **Supersedes** | Prior blocked record in this path (`BLOCKED_BY_MISSING_PRODUCT_DECISION`) |
| **Decision type** | Product Authorization — UI governance intake |

This artifact is the official product authorization for the next DormSys UI governance candidate.

---

## 2. Feature Authorization

| Field | Value |
|---|---|
| **Canonical feature slug** | `employee-context-ui` |
| **Feature title** | Employee Context UI |
| **Domain** | Employee |
| **Source specification** | `specs/003-employee-context` |
| **Related references** | spec03 Phase F / R-15 (deferred Livewire HR admin); catalog `spec03` Employee Context |
| **Authorization** | **Approved for UI governance intake** |

### Business purpose

Provide a governed Persian RTL Livewire presentation surface for HR administrators to work with the Employee bounded context — employee profiles with Identity attachment, department assignment, and related Employee-owned records — without embedding business authority in the UI.

This authorization opens the deferred presentation follow-on described in spec03 planning (Phase F / R-15) for **UI governance intake only**. Exact MVF boundaries are to be established in subsequent governance gates starting with repo-inspection.

---

## 3. Scope

### Authorized scope

| Item | Status |
|---|---|
| UI governance intake for `employee-context-ui` | **Authorized** |
| Start gate: **`repo-inspection`** | **Explicitly permitted** |
| Subsequent gates (feature-analysis → …) | Allowed only after each prior gate passes under normal UI governance rules |
| Presentation-layer work within Employee Context UI (Livewire/Blade) | In scope for later gates after lock-review authorization — **not** authorized for coding by this artifact |
| Consumption of existing Employee Application contracts / read-write actions already delivered by spec03 | In scope for inspection and later contract/lock definition |

### Excluded scope

| Item | Status |
|---|---|
| Implementation / coding | **Not authorized** by this artifact |
| Feature contract drafting | Not started by this artifact |
| Implementation lock drafting | Not started by this artifact |
| Identity login/session UX (OA-02-01) | **Excluded** |
| Identity Livewire admin (spec02 T035–T037) | **Excluded** (separate feature if ever authorized) |
| Request, Allocation, Lottery, Voucher, Dormitory, Audit explorer, Notification residual features | **Excluded** |
| Request Show workflow mutations | **Excluded** |
| Expanding Employee backend beyond what repo-inspection and later gates authorize | **Excluded** unless a separate product/backend authorization is issued |
| Reopening closed notification/request UI features (P2–P9, P3/P4, etc.) | **Excluded** |

---

## 4. Dependency Assessment

| Area | Status |
|---|---|
| **Backend readiness** | Spec03 Wave 1A MVP and US2 (departments) are delivered per `specs/003-employee-context`. US3+ backend authorization remains limited per spec status — repo-inspection must map UI candidates to **already delivered** Application surfaces and flag any backend gaps. |
| **Identity dependency** | `IdentityUserReadContract` available (spec02 frozen Wave 1A). Auth UX remains deferred (OA-02-01); UI must not invent login flows. |
| **Prerequisites for intake** | Satisfied by this Product Authorization. |
| **Blockers for intake** | None for starting **repo-inspection**. Downstream blockers (if any) must be recorded at repo-inspection / feature-analysis. |
| **Dependency status** | **Ready for UI governance intake** at `repo-inspection` |

---

## 5. Governance Transition

| Field | Value |
|---|---|
| **Next allowed action** | Begin UI governance for `employee-context-ui` |
| **Next allowed gate** | **`repo-inspection`** |
| **Explicit permission** | **Yes — repo-inspection is permitted to start** |

Expected first artifact path (for the next task, not created here):

`docs/ui/analysis/employee/employee-context-ui.repo-inspection.md`

(or repository-equivalent under `docs/ui/analysis/` consistent with existing conventions)

---

## 6. Constraints

- This artifact **does** authorize UI governance intake and **explicitly permits** starting **`repo-inspection`**.
- This artifact does **not** authorize implementation.
- This artifact does **not** create a feature contract or implementation lock.
- This artifact does **not** create repo-inspection, feature-analysis, contract, or lock content.
- No coding may begin until a later lock-review returns `APPROVED_FOR_IMPLEMENTATION`.
- Agents must not expand scope into excluded domains or reopen closed UI features under this authorization.

---

*Official Product Authorization. Stop boundary for this task: authorization artifact only.*
