# Program Roadmap — Canonical Phase Sequence

**Purpose:** This document canonicalizes the program-level phase sequence (A→G). It is NOT an execution/feature tracker.

**Created:** 1405/04/24 | **Owner:** Lead | **Source:** DG-02

---

## Scope & Provenance

**Scope:** program-level phase sequence only. Feature-specific execution and remediation status (e.g., dormitory-admin-ui) is tracked in the respective feature artifacts, not here.

**Provenance:** some phase labels are reconstructed from governance evidence (decision records, ledgers, test baselines) rather than a single original planning document.

---

## Phase Sequence

| Phase | Title/Scope | Status |
|-------|-------------|--------|
| A | Domain Foundation (entity inventory, relationship matrix, actor/role taxonomy) | COMPLETE |
| B | Ownership & Authority (ownership matrix, authority gap register, human decision pack) | COMPLETE |
| C | Spec Gap Closure (dependency tables, canonical glossary, completed boundary specs) | COMPLETE |
| D | PEP Rebuild (PEP inventory, decomposition, readiness screening, canonical policy-level records) | COMPLETE |
| E | Exit Criteria / Domain Stabilization Exit | COMPLETE |
| F1 | Auth: employee-records | COMPLETE |
| F2 | Auth: Employee UI/Auth broad scope | **COMPLETE** for boundary `employee-auth-ui` (W-01…W-08 CLOSED). F-W07-04 carried to UI product gate. **No** next UI slug / Spec04 Auth authorized from F2 closeout. |
| G | UI: dormitory-admin-ui | COMPLETE (remediation closed per L9-R Round 2.1 APPROVED; residuals tracked in feature artifacts) |

---

## Cross-References

| Ref | Path |
|-----|------|
| DG-01, DG-02, DG-03 | `docs/governance/open-decisions.md` |
| BL-B1-01 | `docs/governance/risk-register.md` |
| Terminology | `docs/governance/glossary.md` |

---

## Changelog

| تاریخ | تغییر | توسط |
|-------|-------|------|
| 1405/04/24 | F2 `employee-auth-ui` W-01…W-08 CLOSED (Lead W-07/W-08 acceptance); UI entry not authorized | Agent (Lead closeout) |
| 1405/04/24 | DOC hygiene: W-02 CLOSED (DGAP-07 A); W-07 IN EXECUTION (scope YES+AMEND) | Agent (Lead-authorized batch) |
| 1405/04/24 | Option B reconciliation: F2 ACTIVE notes W-01/W-06 impl accepted; W-07/W-08 remain open | Lead |
| 1405/04/24 | L3 spec authored for W-01/W-03; W-03 closed without action (DG-03/Option A) | Lead |
| 1405/04/24 | Initial roadmap created; Phase F split into F1/F2 per DG-02; program-level scope declared | Lead |
