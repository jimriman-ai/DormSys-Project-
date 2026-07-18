# Governance Log

| Date | ID | Status | Note |
|------|-----|--------|------|
| 1405/04/27 \| 2026-07-18 | DGAP-14 | DECIDED | Canonical role = dormitory-manager in governance/contracts. Runtime and domain class residuals explicitly out of scope. |
| 1405/04/27 \| 2026-07-18 | GOV-ARTIFACT | RATIFIED | governance-log.md created as part of R2 (did not pre-exist); creation ratified by Lead. |
| 1405/04/27 \| 2026-07-18 | DGAP-14 | F1-F3 APPLIED | Decision block Effect/inventory/Decision Option A applied (NO-COMMIT). L9 checklist #2 PASS. Sprint A CLOSED → Sprint B ACTIVE. |
| 1405/04/27 \| 2026-07-18 | SPRINT | CONFIRMED | Sprint A = CLOSED; Sprint B = ACTIVE. Backlog: RESIDUAL-01, RESIDUAL-02, UI-M2, F-W07-04 (Lead CONFIRM). |
| 2026-07-18 | SB-D1 | DECIDED (A) | Keep ROLE_DEPT_MGR as deprecated alias; rewrite broken negative test (createDeptMgrOnlyIdentity…) to use a non-approver role; no migration | Lead |
| 2026-07-18 | SB-D2 | DECIDED (A) | Keep pending_department_manager / ApprovalStage::DepartmentManager; document stage ≠ Spatie role (dual model); no rename, no migration | Lead |
| 2026-07-18 | SB-D3 | DECIDED (A) | AUTH-011 Band 4 / L3-only granted: author docs/features/ui-m2/l3-spec.md now; independent of SB-D1/D2 | Lead |
| 2026-07-18 | SB-D4 | DECIDED (A) | F-W07-04: catalog-only — list candidate next-slug surfaces; slug naming + Feature Contract deferred to a later human decision; F2 stays PARTIAL | Lead |
| 2026-07-18 | SB-D5 | ACCEPTED-AS-IS (A) | Shared Spatie role dormitory-manager across Stage-1 console and UI-M1 accepted by design; separation enforced via routes/V1/assignments; no code change — governance record only, no EXECUTE payload | Lead |
| SB-CLOSE | 2026-07-18 | Sprint B Closure | CLOSED | Phase 3 execution complete. All tasks T1–T5 done. 1895 tests passed. L3 citation fix verified (PASS). Remaining for Lead: (1) L3 review gate sign-off for `docs/features/ui-m2/l3-spec.md`; (2) slug naming decision for F-W07-04; (3) manual commit of Phase 3 working tree. |
| 2026-07-18 | F-W07-04-D1 | DECIDED | F-W07-04 feature slug = `stage1-approver-console`. Basis: SB-D4 candidate catalog + ANALYSIS report (candidate inventory, convention rules, collision check: CLEAR). Mint (not adopt); `department-request-approver-console` reserved/historical; park rejected; F2 remains PARTIAL pending Feature Contract. | Lead |
| 2026-07-18 | F-W07-04-D2 | DECIDED | (a) Feature Contract for `stage1-approver-console` ACCEPTED following L3 review gate (PASS-WITH-FIXES, 2026-07-18); (b) gate F2 for F-W07-04 flipped PARTIAL → PASS; (c) directory convention ratified: feature docs live under `docs/features/{slug}/`, here `docs/features/stage1-approver-console/`. Basis: F-W07-04-D1; L3 gate report. | Lead |
| 2026-07-18 | F-W07-04-D3 | COMPLETED | Stage1 approver console Wave 1 implementation closure. Wave 1 completed under accepted implementation lock; scope verified list/filter/polish only; Review Gate PASS; existing Stage-1 approval semantics preserved; no role split, stage rename, migration, or authorization changes; remaining UX test expansion deferred to future Wave. Basis: F-W07-04-D2; Wave 1 Review Gate PASS report. | Lead |
