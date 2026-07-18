# F-W07-04 — Candidate Post-Login UI Surface Catalog

**ID:** F-W07-04 (canonical; do not renumber)  
**Authority:** SB-D4=A (Sprint B Phase 3) — **catalog only**  
**Source:** `docs/features/employee-auth-ui/w07-security-review-report.md:19`; `docs/governance/open-decisions.md` § F-W07-04; HD-05A  

---

## Explicit non-authorization

- **Slug NOT named** for the next product surface.
- **Feature Contract NOT started.**
- **Implementation Authorization NOT granted.**
- Program **F2 remains PARTIAL** solely because F-W07-04 stays open/carried (`work-breakdown.md` phase status; open-decisions § F-W07-04).
- Closing F-W07-04 requires a **future human decision** (name slug and/or park).

---

## Candidate surfaces (informational)

| Candidate (working label) | Target identity role(s) | Rough purpose | Dependencies | Needs new data? |
|---------------------------|-------------------------|---------------|--------------|-----------------|
| Employee request self-service (expansion) | `employee` | Broader own-request UX beyond current IMPL-PERMIT surfaces | Spec05 CLOSED; feature-contract exists | Possibly UI-only; snapshot column already present |
| Stage-1 approver console (ops hardening) | `dormitory-manager` (DGAP-13) | Polish/list/filter for `/approvals/stage1/*` | IMPL-PERMIT-03 CLOSED; shared role with UI-M1 (SB-D5=A accepted) | No schema required for catalog |
| Dormitory manager dashboard (UI-M1 follow-ons) | `dormitory-manager` | Non-placeholder Stage-3 / richer cards | UI-M1 L8 complete; GAP-GOV-02 merge tip | Maybe read-model only |
| Unit-manager dashboard (UI-M2 follow-ons) | `dormitory-unit-manager` | Post-L3 L6+ productization | `docs/features/ui-m2/l3-spec.md` (SB-D3); Band 4 / L3 review | No for L3; L6 may add none |
| Assignment admin (UI-A2 proposal) | TBD | Create/edit manager/unit assignments | DGAP-09 RE-FROZEN — needs new unfreeze | **Yes** — product UI over existing assignment tables |
| Audit / reporting UI entry | TBD | Cross-cutting read surfaces | Spec10/11 governance posture | Likely projections only |

---

## Document control

| Field | Value |
|-------|--------|
| Status | **CATALOG ONLY (SB-D4=A)** |
| Next human gate | Name next slug **or** park F-W07-04 |
| Path | `docs/features/f-w07-04-candidate-catalog.md` |
