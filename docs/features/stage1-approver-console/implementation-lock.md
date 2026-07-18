# Implementation Lock — F-W07-04 stage1-approver-console

- **date:** 2026-07-18
- **status:** COMPLETED
- **completion_reference:** F-W07-04-D3
- **verification:** Wave 1 Review Gate PASS
- **scope:** list/filter/polish for `/approvals/stage1/*`
- **forbidden_changes:**
  - migrations or schema changes
  - role split or ROLE_DEPT_MGR revival
  - stage-string rename
  - scope bleed into NOT SELECTED candidates
- **auth_gate:** dormitory-manager (identity guard) — must not change
- **approver:** Lead (DormSys Architect)
- **basis:** Feature Contract ACCEPTED (F-W07-04-D2); FC `docs/features/stage1-approver-console/feature-contract.md`
