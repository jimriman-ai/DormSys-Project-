# IMPL-PERMIT-02 — Stage-1 Approver Snapshot at Create Time

| Field | Value |
|-------|-------|
| **Permit ID** | **IMPL-PERMIT-02** |
| **Parent** | IMPL-PERMIT-01 §2.1 / IMP-Q-02 |
| **Scope** | Persist `assigned_stage1_approver_identity_id` at **request creation** (not submit/approval) |

## Authorizations

1. Resolve Stage-1 approver via org-chart: `employee → department.manager_id → manager.identity_id`.
2. Write snapshot inside `CreatePersonalRequestAction` after payload validation / before persist.
3. Fail-closed: throw `Stage1ApproverUnresolvedException` if unresolved (never silent null).
4. Use `IdentityRoleGuard` at Presentation boundary; Application layer receives `EmployeeReferenceId` only.
5. Do **not** alter migration `2026_07_18_000001_add_assigned_stage1_approver_identity_id_to_requests_table`.

## Ambiguity (recorded)

Permit prose may say “Dormitory Manager”; Spec04 DGAP-05 A / OQ-AUTH-01 bind Stage-1 to **department line manager (`DeptMgr`)**. Implementation follows department org-chart.

## Prefix

`[PERMIT-ID: IMPL-PERMIT-02]`
