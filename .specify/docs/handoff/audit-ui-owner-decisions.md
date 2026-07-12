# Audit UI — Owner Decisions (Contract Blockers)

**Artifact type:** Owner / governance decision record (non-authorizing)  
**Decision date:** 2026-07-11  
**Checkpoint:** `audit-ui-owner-decisions`

This artifact records owner decisions that close the remaining Contract blockers for `Audit UI` and reassesses Contract readiness. It does **not** create a Feature Contract, authorize implementation, or change code.

Upstream:

- Feature Analysis: `.specify/docs/handoff/audit-ui.feature-analysis.md`
- Evidence Validation: `.specify/docs/handoff/audit-ui-evidence-validation.md`
- Blockers review: `.specify/docs/handoff/audit-ui-contract-blockers-resolution.md` — `AUDIT_UI_CONTRACT_NOT_READY` with `OWNER_DECISION_REQUIRED: NAVIGATION_SCOPE` and `OWNER_DECISION_REQUIRED: UI_ROUTE_AUTHORIZATION`

---

## 1. Status

`AUDIT_UI_OWNER_DECISIONS_RECORDED`

---

## 2. Current State

`AUDIT_UI_CONTRACT_NOT_READY`

---

## 3. Recorded Owner Decisions

| Decision | Value |
| -------- | ----- |
| Navigation scope | `NAVIGATION_SCOPE = ROLE_SCOPED_NAVIGATION` |
| UI route authorization | `UI_ROUTE_AUTHORIZATION = EXISTING_PERMISSION_BASED_AUTHORIZATION` |

### Decision meanings

**`NAVIGATION_SCOPE = ROLE_SCOPED_NAVIGATION`**

- Audit UI discoverability/navigation is available only to users who hold `audit.read`
- Not admin-only
- Not general authenticated navigation
- Role/permission-scoped staff/internal navigation

**`UI_ROUTE_AUTHORIZATION = EXISTING_PERMISSION_BASED_AUTHORIZATION`**

- The future UI route/page must be protected using the existing permission `audit.read`
- The same permission governs visibility/discoverability
- No new dedicated policy is introduced unless future evidence requires it

### Carried-forward evidence resolutions (unchanged)

| Item | Value |
| ---- | ----- |
| Role audience | staff/internal |
| Required permission | `audit.read` |
| Backend read source | `AuditHistoryReadContract` |

---

## 4. Decision Rationale

- Aligns with staff/internal audience evidence (`audit.read` holders: `Administrator`, `DormMgr`, `HRMgr`)
- Reuses existing permission-based authorization already enforced on Application history reads
- Avoids unnecessary policy proliferation for v1
- Keeps Audit UI scope minimal and governance-safe (read-only presentation of existing history; no Reporting explorer expansion)

---

## 5. Contract Readiness Reassessment

Both remaining owner decisions are recorded. Prior evidence resolutions for role audience and backend read source remain in force.

`AUDIT_UI_CONTRACT_READY`

Contract creation is now eligible as a **subsequent** governance step. This artifact does **not** itself create the Feature Contract.

Closed owner-decision markers:

- `OWNER_DECISION_REQUIRED: NAVIGATION_SCOPE` — closed by `ROLE_SCOPED_NAVIGATION`
- `OWNER_DECISION_REQUIRED: UI_ROUTE_AUTHORIZATION` — closed by `EXISTING_PERMISSION_BASED_AUTHORIZATION`

---

## 6. Non-Authorization Statement

`This artifact records owner decisions and reassesses contract readiness only. It does not authorize implementation or code changes.`

---

## 7. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

Only this decision artifact was created:

- `.specify/docs/handoff/audit-ui-owner-decisions.md`
