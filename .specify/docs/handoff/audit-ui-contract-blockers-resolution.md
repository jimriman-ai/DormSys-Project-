# Audit UI — Contract Blockers Resolution

**Artifact type:** Clarification / decision record (non-authorizing)  
**Review date:** 2026-07-11  
**Checkpoint:** `audit-ui-contract-blockers-resolution`

This artifact reviews evidence for the four Contract-blocking questions from `.specify/docs/handoff/audit-ui.feature-analysis.md`. It does **not** create a Feature Contract, authorize implementation, or change code.

Upstream:

- Feature Analysis: `AUDIT_UI_FEATURE_ANALYSIS_CREATED` / `AUDIT_UI_CONTRACT_NOT_READY`
- Evidence Validation: `AUDIT_UI_FEATURE_ANALYSIS_ALLOWED`

---

## 1. Status

`AUDIT_UI_CONTRACT_BLOCKERS_REVIEWED`

---

## 2. Current State

`AUDIT_UI_CONTRACT_NOT_READY`

---

## 3. Question-by-Question Findings

### 3.1 Role Audience

**Finding category:** staff/internal role-scoped (evidence-confirmed)

| Item | Detail |
| ---- | ------ |
| Confirmed evidence | Spatie permission `audit.read` is required for Audit history reads (UD-10-06). Seeded roles with `audit.read`: `Administrator`, `DormMgr`, `HRMgr` (`IdentityRoleSeeder::AUDIT_READ_ROLES`; Spec10 research R-06 / contracts). Employees and Operators denied in v1. `SystemAdministrator` seed does **not** receive `audit.read`. |
| Confirmed evidence (negative) | Not **admin-only** (multiple manager roles share `audit.read`). Not **broader authenticated audience** (permission gate required). |
| Unconfirmed evidence | No separate Product MVF artifact that further narrows or widens UI audience below/above the existing `audit.read` role set (`TBD_BY_PRODUCT` for screens/actions, not a contrary role matrix). |
| Owner decision required? | **No** for audience category. Evidence supports Contract audience as principals holding `audit.read` (staff/internal role-scoped to the seeded grant set). |

**Resolution:** Role audience blocker **resolved by evidence** as **staff/internal role-scoped** (`audit.read` holders).

---

### 3.2 Backend Read Source

**Finding category:** confirmed source identified

| Item | Detail |
| ---- | ------ |
| Confirmed evidence | `AuditHistoryReadContract` is the Spec10 Application read port for presentation/reporting (`specs/010-audit-trail/contracts/audit-history-read-contract.md` Direction: Presentation / Reporting → Audit; `plan.md` lists it as presentation read port). Implemented and policy-enforced via `AuditHistoryReadService` → `AuditReadPolicyEnforcementPoint`. |
| Confirmed evidence (adjacent, not selected) | Reporting JSON endpoints (`/entity-timeline`, `/audit-window-summary`, etc.) also consume Audit history through adapters — these are **Reporting** presentation/API surfaces. |
| Unconfirmed / excluded | Product Authorization for `audit-ui` **excludes** unrelated Reporting UI (KPI/explorer). Feature Analysis non-goals forbid expanding into Spec11 E-03 / Reporting explorer by implication. |
| Owner decision required? | **No** for choosing among the two plausible families: governance + Product exclusion select **Audit Application history read** (`AuditHistoryReadContract`), not Reporting JSON UI/API as the Audit UI consumption path. |

**Resolution:** Backend read source blocker **resolved by evidence** as **`AuditHistoryReadContract`** (existing Audit Application history read).

`OWNER_DECISION_REQUIRED: BACKEND_READ_SOURCE` — **not** raised.

---

### 3.3 Navigation Scope

**Finding category:** unconfirmed

| Item | Detail |
| ---- | ------ |
| Confirmed evidence | No Audit UI routes, Livewire pages, or Blade views exist. App layout nav (`resources/views/components/layouts/app.blade.php`) has no Audit entrypoint. Spec10 OA-10-05 defers presentation without fixing navigation placement. Product exact screens/actions remain `TBD_BY_PRODUCT`. |
| Unconfirmed evidence | Whether entry should be **admin navigation only**, **role-scoped navigation** for all `audit.read` holders, **contextual/detail-page entry only**, or another placement — **no repository or Product decision confirms this**. |
| Owner decision required? | **Yes** |

`OWNER_DECISION_REQUIRED: NAVIGATION_SCOPE`

---

### 3.4 UI Route Authorization Coverage

**Finding category:** likely but unconfirmed

| Item | Detail |
| ---- | ------ |
| Confirmed evidence | Application-layer coverage for history **queries**: `AuditHistoryReadService::query` calls `AuditReadPolicyEnforcementPoint::enforce()` → `AuditAuthorizationPort::authorizeRead()` (`audit.read`). Principal middleware `audit.principal` exists for request attributes. Reporting API tests demonstrate deny-without-`audit.read` behavior for Reporting routes — not Audit UI routes. |
| Unconfirmed evidence | **No** Audit UI web route or Livewire page exists. Therefore route middleware / Livewire mount / presentation-layer authorization **coverage for a future Audit UI page is not confirmed**. Safe governance requires Contract (or owner) to state that UI access must not bypass Application enforcement and must deny guests and non-`audit.read` principals — that binding is not yet recorded as an owner/Product decision for the UI surface. |
| Owner decision required? | **Yes** |

`OWNER_DECISION_REQUIRED: UI_ROUTE_AUTHORIZATION`

---

## 4. Resolution Summary

| Blocker | Outcome |
| ------- | ------- |
| Role audience | **Resolved by evidence** — staff/internal role-scoped (`audit.read` → `Administrator`, `DormMgr`, `HRMgr`) |
| Backend read source | **Resolved by evidence** — `AuditHistoryReadContract` |
| Navigation scope | **Unresolved** — `OWNER_DECISION_REQUIRED: NAVIGATION_SCOPE` |
| UI-route authorization coverage | **Unresolved** — `OWNER_DECISION_REQUIRED: UI_ROUTE_AUTHORIZATION` |

Owner decisions still required before Contract:

1. `OWNER_DECISION_REQUIRED: NAVIGATION_SCOPE` — choose admin-only nav vs role-scoped nav vs contextual/detail-only (or explicit other), consistent with `audit.read` audience.
2. `OWNER_DECISION_REQUIRED: UI_ROUTE_AUTHORIZATION` — confirm how the future UI route/page will be governed (must remain consistent with Application `audit.read` enforcement; record that Application gate remains authoritative and UI must not bypass it).

---

## 5. Contract Readiness Reassessment

`AUDIT_UI_CONTRACT_NOT_READY`

Two blockers remain unresolved (navigation scope; UI-route authorization coverage). Contract creation remains blocked until owner decisions close those items.

---

## 6. Non-Authorization Statement

`This artifact resolves contract blockers only and does not authorize Contract creation, implementation, or code changes.`

---

## 7. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

Only this clarification artifact was created:

- `.specify/docs/handoff/audit-ui-contract-blockers-resolution.md`
