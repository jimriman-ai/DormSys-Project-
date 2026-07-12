# Audit UI — Open Questions Resolution

**Artifact type:** Governance clarification / decision record (non-authorizing)  
**Decision date:** 2026-07-11  
**Checkpoint:** `audit-ui-open-questions-resolution`

This artifact records owner/governance decisions that close Feature Contract open questions OQ-AU-01, OQ-AU-02, and OQ-AU-03. It does **not** authorize implementation, create an Implementation Lock, or modify code.

Upstream:

- Feature Contract: `.specify/docs/ui/contracts/audit/audit-ui.feature-contract.yaml` — `AUDIT_UI_FEATURE_CONTRACT_CREATED`
- Implementation Authorization Decision: `.specify/docs/handoff/audit-ui-implementation-authorization-decision.md` — `AUDIT_UI_IMPLEMENTATION_NOT_AUTHORIZED`
- Prior owner decisions: `.specify/docs/handoff/audit-ui-owner-decisions.md`

---

## 1. Status

`AUDIT_UI_OPEN_QUESTIONS_RESOLVED`

---

## 2. Current State

`AUDIT_UI_IMPLEMENTATION_NOT_AUTHORIZED`

(Incoming coding posture is unchanged by this artifact; Implementation Authorization remains a **subsequent** decision.)

---

## 3. Resolution Summary

### OQ-AU-01 — Query Profile / Filtering Boundary

**Decision:** `AUDIT_UI_V1_QUERY_PROFILE_CONFIRMED`

Meaning:

- Audit UI v1 remains read-only
- Spec10 filter/search capability is **not** automatically included unless explicitly approved
- No unapproved filtering or search scope may enter implementation
- If future filtering is required, it requires separate governance approval

Closes contract marker: OQ-AU-01 (`blocks: implementation_authorization`)

---

### OQ-AU-02 — Navigation Label and Placement

**Decision:** `ROLE_SCOPED_NAVIGATION_CONFIRMED`

Meaning:

- Navigation follows the previously approved role-scoped model (`ROLE_SCOPED_NAVIGATION` / `audit.read`)
- Exact visual placement remains an implementation concern
- No new navigation architecture is introduced

Closes contract marker: OQ-AU-02 (`blocks: implementation_lock_or_authorization`)

---

### OQ-AU-03 — V1 Display Field Set

**Decision:** `AUDIT_UI_V1_DISPLAY_BOUNDARY_CONFIRMED`

Meaning:

- V1 exposes only existing approved audit history data
- Display fields must come from the approved read contract (`AuditHistoryReadContract` / existing history item projection)
- No new derived analytics, reporting fields, or audit mutations are introduced

Closes contract marker: OQ-AU-03 (`blocks: implementation_authorization`)

---

## 4. Contract Impact

`AUDIT_UI_CONTRACT_REMAINS_VALID`

Reason:

The decisions clarify existing boundaries and do not expand approved capability.

No Feature Contract revision is required. Forbidden behaviors (mutations, export, analytics, dashboards, unapproved filter/search, permission model changes, Reporting explorer expansion) remain in force.

---

## 5. Authorization Reassessment

All three Implementation Authorization blockers named in the Feature Contract and the prior denial decision are resolved without scope expansion.

`AUDIT_UI_IMPLEMENTATION_AUTHORIZATION_READY`

Meaning:

- Reconsideration of Implementation Authorization is now eligible as a **subsequent** governance step
- This artifact does **not** itself grant Implementation Authorization
- Any later grant must remain within the Feature Contract and these clarified boundaries
- Unapproved filter/search, new fields, mutations, and navigation architecture expansion remain excluded

---

## 6. Non-Authorization Statement

`This artifact resolves governance questions only and does not authorize implementation.`

---

## 7. No-Change Confirmation

`No application, test, UI implementation, contract, quickstart, or implementation files were modified.`

Only this resolution artifact was created:

- `.specify/docs/handoff/audit-ui-open-questions-resolution.md`
