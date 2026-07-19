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

## Wave 2 status (SB-D9 / WP-RQ-W2-01) — status record only

- **authorized:** SB-D9 ISSUED (2026-07-19)
- **wave_status:** COMPLETED (WT) — UX tests + list/filter polish; SHA UNVERIFIED until Lead commit
- **auth_gate:** unchanged — `dormitory-manager` (identity)
- **application_signature:** `ListPendingStage1RequestsAction::execute()` — **unchanged** (presentation-side filter/pagination)
- **verify_companion (out-of-L1):** `ExemptMutationActionRegistry` — register `ListPendingStage1RequestsAction` as read-only query exempt (MPEP boundary; peer to Query* / Stage-1 actions) — **SB-D10**
- **wave_2_change_inventory (companion):** `app/Application/Mutation/Registry/ExemptMutationActionRegistry.php` (`:21`, `:34`)
- **deferred_again:** requester-name filter (domain has `employee_id` only; no cross-module Employee name read under this Lock)
