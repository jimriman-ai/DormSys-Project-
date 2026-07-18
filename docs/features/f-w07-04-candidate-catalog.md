# F-W07-04 — Candidate Post-Login UI Surface Catalog

**ID:** F-W07-04 (canonical; do not renumber)  
**Authority:** SB-D4=A (Sprint B Phase 3) — **catalog only**  
**Source:** `docs/features/employee-auth-ui/w07-security-review-report.md:19`; `docs/governance/open-decisions.md` § F-W07-04; HD-05A  

---

## Explicit non-authorization

- **Feature Contract ACCEPTED** — `docs/features/stage1-approver-console/feature-contract.md`; decision `docs/governance/governance-log.md:16` (F-W07-04-D2).
- **Implementation Authorization NOT granted.**
- Program **F2 = PASS** for F-W07-04 (`docs/governance/governance-log.md:16`, F-W07-04-D2).
- Slug **DECIDED** under **F-W07-04-D1** (2026-07-18): `stage1-approver-console` (mint; park rejected).

---

## Candidate surfaces (informational)

| Candidate (working label) | Disposition | Target identity role(s) | Rough purpose | Dependencies | Needs new data? |
|---------------------------|-------------|-------------------------|---------------|--------------|-----------------|
| Employee request self-service (expansion) | **NOT SELECTED** | `employee` | Broader own-request UX beyond current IMPL-PERMIT surfaces | Spec05 CLOSED; feature-contract exists | Possibly UI-only; snapshot column already present |
| Stage-1 approver console (ops hardening) → slug `stage1-approver-console` | **DECIDED (F-W07-04-D1)** | `dormitory-manager` (DGAP-13) | Polish/list/filter for `/approvals/stage1/*` | IMPL-PERMIT-03 CLOSED; shared role with UI-M1 (SB-D5=A accepted) | No schema required for catalog |
| Dormitory manager dashboard (UI-M1 follow-ons) | **NOT SELECTED** | `dormitory-manager` | Non-placeholder Stage-3 / richer cards | UI-M1 L8 complete; GAP-GOV-02 merge tip | Maybe read-model only |
| Unit-manager dashboard (UI-M2 follow-ons) | **NOT SELECTED** | `dormitory-unit-manager` | Post-L3 L6+ productization | `docs/features/ui-m2/l3-spec.md` (SB-D3); Band 4 / L3 review | No for L3; L6 may add none |
| Assignment admin (UI-A2 proposal) | **NOT SELECTED** | TBD | Create/edit manager/unit assignments | DGAP-09 RE-FROZEN — needs new unfreeze | **Yes** — product UI over existing assignment tables |
| Audit / reporting UI entry | **NOT SELECTED** | TBD | Cross-cutting read surfaces | Spec10/11 governance posture | Likely projections only |

Decision recorded in governance-log.md (F-W07-04-D1). Next step: Feature Contract (separate prompt).
Feature Contract draft: `docs/features/stage1-approver-console/feature-contract.md`.

---

## Document control

| Field | Value |
|-------|--------|
| Status | **SLUG DECIDED (F-W07-04-D1)** — Feature Contract not started |
| Selected slug | `stage1-approver-console` (mint; `department-request-approver-console` reserved) |
| Next human gate | Feature Contract (separate prompt) |
| Path | `docs/features/f-w07-04-candidate-catalog.md` |
