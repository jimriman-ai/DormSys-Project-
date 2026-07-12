# Feature Analysis — Audit UI

**Artifact type:** Feature analysis (non-authorizing)  
**Analysis date:** 2026-07-11  
**Checkpoint:** `audit-ui.feature-analysis`

This artifact analyzes the confirmed presentation gap for `Audit UI` under Spec Kit governance. It does **not** grant Design Approval, Feature Contract approval, Implementation Authorization, Quickstart, Batch Execution Permission, or coding authority.

Upstream path:

`NEXT_APPROVED_WORK_ITEM_SELECTED: Audit UI`  
→ `AUDIT_UI_EVIDENCE_VALIDATED`  
→ `AUDIT_UI_FEATURE_ANALYSIS_ALLOWED`

---

## 1. Status

`AUDIT_UI_FEATURE_ANALYSIS_CREATED`

---

## 2. Work Item

`Audit UI`

| Field | Value |
| ----- | ----- |
| Canonical slug | `audit-ui` |
| Domain | Audit |
| Selection artifact | `.specify/docs/handoff/next-approved-work-item-selection-after-create-entrypoint-deferral.md` |
| Evidence validation | `.specify/docs/handoff/audit-ui-evidence-validation.md` |
| Product intake | `docs/product/product-authorization-next-ui-feature.md` — `AUTHORIZED` (intake only; exact MVF `TBD_BY_PRODUCT`) |
| Source specification context | `specs/010-audit-trail` (OA-10-05 presentation deferred from Spec10 closure) |

---

## 3. Evidence Basis

Summarized from `.specify/docs/handoff/audit-ui-evidence-validation.md` only (confirmed facts):

| Finding | Status |
| ------- | ------ |
| Backend / Application history read capability | **Present** — `AuditHistoryReadContract` / `AuditHistoryReadService` / `QueryAuditHistoryAction` (+ related DTOs); Reporting JSON APIs also consume Audit history |
| Audit Livewire / Blade / UI routes | **Absent** — Presentation Livewire/Views/Routes/Controllers empty (`.gitkeep` only, plus principal middleware); no audit views under `resources/views` |
| Presentation gap | **Confirmed** |
| Dependencies | **`DEPENDENCIES_READY`** (per evidence validation) |
| Prior Spec Kit closure / deferral for this slug | **None** |

Governance note carried forward: exact Product MVF remains `TBD_BY_PRODUCT`; whether UI should bind Audit Application contracts directly versus Reporting APIs was **not** settled by evidence validation.

---

## 4. User/Business Problem

Authorized operators who already have Audit history read permission on the backend cannot use a governed Persian RTL web surface in DormSys to **view** that history.

Today, audit/application history is stored and queryable through Application (and Reporting JSON) channels, but there is no confirmed user-visible Audit UI page or navigation entrypoint for that read capability. Stakeholders therefore lack an in-product presentation path for compliance/trail visibility that Product has authorized for UI governance intake.

This problem is presentation and access discoverability of an **existing** read capability — not absence of audit recording.

---

## 5. Confirmed Gap

`Existing audit/application history read capability lacks a confirmed authorized UI presentation surface.`

---

## 6. Non-Goals

This Feature Analysis explicitly excludes:

- no new audit event generation
- no audit editing or mutation
- no database/schema changes
- no export/reporting
- no analytics dashboard
- no authorization bypass
- no implementation authorization
- no Feature Contract creation by this artifact
- no expansion into Reporting KPI / Operator Explorer (E-03) scope by implication
- no notification, workflow, request, or dormitory UI changes
- no inventing backend capability not evidenced as present

---

## 7. Scope Candidate

Smallest plausible feature scope that could satisfy the confirmed gap (analysis only — not a contract):

**Read-only Audit history view surface** for principals who already pass existing Audit read authorization, presenting fields already available from the existing history read path, with a discoverable navigation affordance scoped to those authorized principals.

Boundary constraints for any future contract:

- Consume **existing** audit/history read data only
- Read-only presentation only
- Stay within existing authorization boundaries (no new permission model invented here)
- Do not add write, export, filter/analytics, or schema work as part of this gap closure

This candidate does **not** name implementation files, routes, components, or tasks.

---

## 8. Dependency and Readiness Assessment

Dependencies are ready **only to the extent validated** by `.specify/docs/handoff/audit-ui-evidence-validation.md`:

- Audit trail persistence and Application history read surfaces exist
- Read policy enforcement / principal context infrastructure exists
- Presentation layer for Audit UI is absent (the gap)

No additional dependencies are invented by this analysis.

Unresolved product/contract-binding choices (backend source binding, MVF screen shape, navigation placement) are **not** reclassified as missing foundational dependencies; they remain open questions for Contract readiness (§9–§10).

---

## 9. Risks and Open Questions

Unresolved items that must be resolved before Feature Contract or Implementation Authorization:

| # | Open question | Why it blocks Contract |
| - | ------------- | ---------------------- |
| Q1 | Exact user role(s) / principals permitted to view audit/application history **in the UI** | Backend baseline documents Spatie `audit.read` for `Administrator`, `DormMgr`, and `HRMgr`, but Product MVF and UI audience (which of those roles, and whether UI mirrors that set exactly) are not contract-locked |
| Q2 | Exact existing backend read source to expose | Evidence confirms both `AuditHistoryReadContract` and Reporting JSON timeline/window endpoints; Contract must name **one** authoritative UI consumption path without inventing a hybrid or new API |
| Q3 | Whether the navigation entrypoint should be admin-only or role-scoped to all `audit.read` holders | Affects discoverability scope and layout placement; Product MVF still `TBD_BY_PRODUCT` |
| Q4 | Whether existing authorization policies already cover a future UI route | Application `AuditHistoryReadService` enforces read policy on query; **no** Audit UI web route exists yet, so route-level / Livewire presentation authorization coverage is **unconfirmed** and must be specified at Contract without bypassing Application enforcement |
| Q5 | Relationship to deferred OA-10-05 / blocked triage slug `audit-explorer-ui` | Product authorizes `audit-ui`; Contract must not silently reopen Spec11-excluded Explorer/Reporting UI scope |

---

## 10. Contract Readiness Decision

`AUDIT_UI_CONTRACT_NOT_READY`

Basis:

- Confirmed gap and dependency readiness support Feature Analysis completion
- Contract creation remains blocked by unresolved **authorization / role audience**, **backend-source binding**, **navigation scoping**, and **UI-route authorization coverage** ambiguities listed in §9
- Product exact MVF remains `TBD_BY_PRODUCT`

Owner / Product clarification on Q1–Q4 (at minimum) is required before a Feature Contract may be drafted.

---

## 11. Non-Authorization Statement

`This Feature Analysis does not authorize Contract, implementation, or code changes.`

---

## 12. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

Only this Feature Analysis artifact was created:

- `.specify/docs/handoff/audit-ui.feature-analysis.md`
