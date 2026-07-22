# Domain Gaps Report — WAVE DOM-DISC-01

> **Wave:** DOM-DISC-01 (Discovery-only)
> **Generated:** 2026-07-22 (1405/04/31)
> **Baseline:** `.dormSys/database-map.md` (100% table inventory)
> **Rule:** Evidence-cited only. No fixes applied. No schema/model changes.

## Method

| Step | Evidence |
|------|----------|
| Domain tables | `#### \`table\`` headers in `.dormSys/database-map.md` excluding Framework / Spatie permission / Telescope / `settings` |
| Models | `app/Modules/**/Infrastructure/Persistence/Models/**` with `protected $table` |
| Migration FKs | Static parse of `database/migrations/**` (effective FKs after dropForeign) |
| SoftDeletes | `App\Support\Models\BaseModel` already uses `SoftDeletes` — models extending it are not flagged |
| hasMany FK keys | Foreign key on **child** table — not treated as missing column on parent |

## Coverage (every domain table once)

| Table | Model path | Status |
|-------|------------|--------|
| `identity_users` | `app/Modules/Identity/Infrastructure/Persistence/Models/UserModel.php` | GAPS: GAP-DOM-21, GAP-DOM-22, GAP-DOM-23 |
| `employee_departments` | `app/Modules/Employee/Infrastructure/Persistence/Models/DepartmentModel.php` | CLEAN |
| `employee_employees` | `app/Modules/Employee/Infrastructure/Persistence/Models/EmployeeModel.php` | GAPS: GAP-DOM-16 |
| `employee_dependents` | `app/Modules/Employee/Infrastructure/Persistence/Models/DependentModel.php` | CLEAN |
| `requests` | `app/Modules/Request/Infrastructure/Persistence/Models/RequestModel.php` | GAPS: GAP-DOM-17, GAP-DOM-18, GAP-DOM-19, GAP-DOM-20 |
| `request_approvals` | `app/Modules/Request/Infrastructure/Persistence/Models/RequestApprovalModel.php` | GAPS: GAP-DOM-01 |
| `request_dependent_snapshots` | `app/Modules/Request/Infrastructure/Persistence/Models/RequestDependentSnapshotModel.php` | GAPS: GAP-DOM-02 |
| `request_members` | `app/Modules/Request/Infrastructure/Persistence/Models/RequestMemberModel.php` | GAPS: GAP-DOM-03 |
| `request_mission_details` | `app/Modules/Request/Infrastructure/Persistence/Models/RequestMissionDetailsModel.php` | GAPS: GAP-DOM-04 |
| `dormitories` | `app/Modules/Dormitory/Infrastructure/Persistence/Models/DormitoryModel.php` | CLEAN |
| `dormitory_buildings` | `app/Modules/Dormitory/Infrastructure/Persistence/Models/BuildingModel.php` | CLEAN |
| `dormitory_floors` | `app/Modules/Dormitory/Infrastructure/Persistence/Models/FloorModel.php` | CLEAN |
| `dormitory_rooms` | `app/Modules/Dormitory/Infrastructure/Persistence/Models/RoomModel.php` | CLEAN |
| `dormitory_beds` | `app/Modules/Dormitory/Infrastructure/Persistence/Models/BedModel.php` | CLEAN |
| `dormitory_manager_assignments` | `—` | GAPS: GAP-DOM-05, GAP-DOM-06, GAP-DOM-07 |
| `dormitory_unit_manager_assignments` | `—` | GAPS: GAP-DOM-08, GAP-DOM-09, GAP-DOM-10 |
| `dormitory_assignments` | `app/Modules/Dormitory/Infrastructure/Persistence/Models/DormitoryAssignment.php` | GAPS: GAP-DOM-11 |
| `lottery_programs` | `app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryProgramModel.php` | GAPS: GAP-DOM-24, GAP-DOM-25, GAP-DOM-27 |
| `lottery_registrations` | `app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryRegistrationModel.php` | GAPS: GAP-DOM-12, GAP-DOM-26 |
| `lottery_results` | `app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryResultModel.php` | GAPS: GAP-DOM-13, GAP-DOM-14 |
| `lottery_eligible_snapshots` | `app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryEligibleSnapshotModel.php` | GAPS: GAP-DOM-15 |
| `allocations` | `app/Modules/Allocation/Infrastructure/Persistence/Models/AllocationModel.php` | CLEAN |
| `allocation_items` | `app/Modules/Allocation/Infrastructure/Persistence/Models/AllocationItemModel.php` | CLEAN |
| `check_in_records` | `app/Modules/CheckIn/Infrastructure/Persistence/Models/CheckInRecordModel.php` | CLEAN |
| `voucher_issuance_triggers` | `app/Modules/Voucher/Infrastructure/Persistence/Models/VoucherIssuanceTriggerModel.php` | CLEAN |
| `voucher_eligibility_outcomes` | `app/Modules/Voucher/Infrastructure/Persistence/Models/VoucherEligibilityOutcomeModel.php` | CLEAN |
| `vouchers` | `app/Modules/Voucher/Infrastructure/Persistence/Models/VoucherModel.php` | CLEAN |
| `voucher_lifecycle_transitions` | `app/Modules/Voucher/Infrastructure/Persistence/Models/VoucherLifecycleTransitionModel.php` | CLEAN |
| `audit_logs` | `app/Modules/Audit/Infrastructure/Persistence/Models/AuditLogModel.php` | CLEAN |
| `notification_logs` | `app/Modules/Notification/Infrastructure/Persistence/Models/NotificationLogModel.php` | CLEAN |
| `reporting_projection_cursors` | `app/Modules/Reporting/Infrastructure/Persistence/Models/ProjectionCursorModel.php` | CLEAN |
| `reporting_correlation_projection_entries` | `app/Modules/Reporting/Infrastructure/Persistence/Models/CorrelationProjectionEntryModel.php` | CLEAN |
| `reporting_audit_window_aggregates` | `app/Modules/Reporting/Infrastructure/Persistence/Models/AuditWindowAggregateModel.php` | CLEAN |
| `reporting_actor_activity_summaries` | `app/Modules/Reporting/Infrastructure/Persistence/Models/ActorActivitySummaryModel.php` | CLEAN |
| `reporting_projection_ingest_receipts` | `app/Modules/Reporting/Infrastructure/Persistence/Models/ProjectionIngestReceiptModel.php` | CLEAN |
| `workflow_request_approval_instances` | `app/Modules/Workflow/Infrastructure/Persistence/Models/RequestApprovalWorkflowInstanceModel.php` | CLEAN |
| `workflow_request_approval_step_executions` | `app/Modules/Workflow/Infrastructure/Persistence/Models/RequestApprovalWorkflowStepExecutionModel.php` | CLEAN |

**Domain tables scanned:** 37

## Gaps

| ID | Table | Type | Expected (evidence) | Actual (evidence) | Severity |
|----|-------|------|---------------------|-------------------|----------|
| **GAP-DOM-01** | `request_approvals` | MISSING-RELATION | Migration FK `request_id` -> requests.id onDelete=cascade [database/migrations/modules/request/2026_06_26_000002_create_request_approvals_table.php:23] | No belongsTo() in app/Modules/Request/Infrastructure/Persistence/Models/RequestApprovalModel.php with foreign key `request_id` (relations: none) | M |
| **GAP-DOM-02** | `request_dependent_snapshots` | MISSING-RELATION | Migration FK `request_id` -> requests.id onDelete=cascade [database/migrations/modules/request/2026_06_26_000003_create_request_dependent_snapshots_table.php:24] | No belongsTo() in app/Modules/Request/Infrastructure/Persistence/Models/RequestDependentSnapshotModel.php with foreign key `request_id` (relations: none) | M |
| **GAP-DOM-03** | `request_members` | MISSING-RELATION | Migration FK `request_id` -> requests.id onDelete=cascade [database/migrations/modules/request/2026_06_26_000004_create_request_members_table.php:20] | No belongsTo() in app/Modules/Request/Infrastructure/Persistence/Models/RequestMemberModel.php with foreign key `request_id` (relations: none) | M |
| **GAP-DOM-04** | `request_mission_details` | MISSING-RELATION | Migration FK `request_id` -> requests.id onDelete=cascade [database/migrations/modules/request/2026_06_26_000005_create_request_mission_details_table.php:19] | No belongsTo() in app/Modules/Request/Infrastructure/Persistence/Models/RequestMissionDetailsModel.php with foreign key `request_id` (relations: none) | M |
| **GAP-DOM-05** | `dormitory_manager_assignments` | MISSING-MODEL **NEEDS-DECISION** | Persistence model for domain table `dormitory_manager_assignments` (migration: database/migrations/modules/dormitory/2026_07_16_000001_create_dormitory_manager_assignments_table.php:17 CREATE) | No class under app/Modules/**/Persistence/Models with protected $table='dormitory_manager_assignments' | H |
| **GAP-DOM-06** | `dormitory_manager_assignments` | MISSING-RELATION | Migration FK `user_id` -> constrained('identity_users') onDelete=restrict [database/migrations/modules/dormitory/2026_07_16_000001_create_dormitory_manager_assignments_table.php:19] | No model present to host belongsTo/has* for this FK | M |
| **GAP-DOM-07** | `dormitory_manager_assignments` | MISSING-RELATION | Migration FK `dormitory_id` -> constrained('dormitories') onDelete=restrict [database/migrations/modules/dormitory/2026_07_16_000001_create_dormitory_manager_assignments_table.php:22] | No model present to host belongsTo/has* for this FK | M |
| **GAP-DOM-08** | `dormitory_unit_manager_assignments` | MISSING-MODEL **NEEDS-DECISION** | Persistence model for domain table `dormitory_unit_manager_assignments` (migration: database/migrations/modules/dormitory/2026_07_16_000002_create_dormitory_unit_manager_assignments_table.php:17 CREATE) | No class under app/Modules/**/Persistence/Models with protected $table='dormitory_unit_manager_assignments' | H |
| **GAP-DOM-09** | `dormitory_unit_manager_assignments` | MISSING-RELATION | Migration FK `user_id` -> constrained('identity_users') onDelete=restrict [database/migrations/modules/dormitory/2026_07_16_000002_create_dormitory_unit_manager_assignments_table.php:19] | No model present to host belongsTo/has* for this FK | M |
| **GAP-DOM-10** | `dormitory_unit_manager_assignments` | MISSING-RELATION | Migration FK `room_id` -> constrained('dormitory_rooms') onDelete=restrict [database/migrations/modules/dormitory/2026_07_16_000002_create_dormitory_unit_manager_assignments_table.php:22] | No model present to host belongsTo/has* for this FK | M |
| **GAP-DOM-11** | `dormitory_assignments` | MISSING-RELATION | Migration FK `user_id` -> constrained('identity_users') onDelete=restrict [database/migrations/modules/dormitory/2026_07_20_000001_create_dormitory_assignments_table.php:21] | No belongsTo() in app/Modules/Dormitory/Infrastructure/Persistence/Models/DormitoryAssignment.php with foreign key `user_id` (relations: scopeActive:belongsTo, dormitory:belongsTo) | M |
| **GAP-DOM-12** | `lottery_registrations` | MISSING-RELATION | Migration FK `program_id` -> lottery_programs.id onDelete=None [database/migrations/modules/lottery/2026_06_30_000002_create_lottery_registrations_table.php:26] | No belongsTo() in app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryRegistrationModel.php with foreign key `program_id` (relations: none) | M |
| **GAP-DOM-13** | `lottery_results` | MISSING-RELATION | Migration FK `program_id` -> lottery_programs.id onDelete=None [database/migrations/modules/lottery/2026_06_30_000003_create_lottery_results_table.php:25] | No belongsTo() in app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryResultModel.php with foreign key `program_id` (relations: none) | M |
| **GAP-DOM-14** | `lottery_results` | MISSING-RELATION | Migration FK `registration_id` -> lottery_registrations.id onDelete=None [database/migrations/modules/lottery/2026_06_30_000003_create_lottery_results_table.php:29] | No belongsTo() in app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryResultModel.php with foreign key `registration_id` (relations: none) | M |
| **GAP-DOM-15** | `lottery_eligible_snapshots` | MISSING-RELATION | Migration FK `program_id` -> lottery_programs.id onDelete=None [database/migrations/modules/lottery/2026_06_30_000004_create_lottery_eligible_snapshots_table.php:26] | No belongsTo() in app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryEligibleSnapshotModel.php with foreign key `program_id` (relations: none) | M |
| **GAP-DOM-16** | `employee_employees` | MISSING-RELATION **NEEDS-DECISION** | Child table `employee_departments` has FK `manager_id` -> employee_employees.id [database/migrations/modules/employee/2026_06_26_000002_create_employee_employees_table.php:39]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Employee/Infrastructure/Persistence/Models/EmployeeModel.php referencing `manager_id` or `DepartmentModel` (relations: department:belongsTo, dependents:hasMany) | L |
| **GAP-DOM-17** | `requests` | MISSING-RELATION **NEEDS-DECISION** | Child table `request_approvals` has FK `request_id` -> requests.id [database/migrations/modules/request/2026_06_26_000002_create_request_approvals_table.php:23]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Request/Infrastructure/Persistence/Models/RequestModel.php referencing `request_id` or `RequestApprovalModel` (relations: none) | L |
| **GAP-DOM-18** | `requests` | MISSING-RELATION **NEEDS-DECISION** | Child table `request_dependent_snapshots` has FK `request_id` -> requests.id [database/migrations/modules/request/2026_06_26_000003_create_request_dependent_snapshots_table.php:24]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Request/Infrastructure/Persistence/Models/RequestModel.php referencing `request_id` or `RequestDependentSnapshotModel` (relations: none) | L |
| **GAP-DOM-19** | `requests` | MISSING-RELATION **NEEDS-DECISION** | Child table `request_members` has FK `request_id` -> requests.id [database/migrations/modules/request/2026_06_26_000004_create_request_members_table.php:20]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Request/Infrastructure/Persistence/Models/RequestModel.php referencing `request_id` or `RequestMemberModel` (relations: none) | L |
| **GAP-DOM-20** | `requests` | MISSING-RELATION **NEEDS-DECISION** | Child table `request_mission_details` has FK `request_id` -> requests.id [database/migrations/modules/request/2026_06_26_000005_create_request_mission_details_table.php:19]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Request/Infrastructure/Persistence/Models/RequestModel.php referencing `request_id` or `RequestMissionDetailsModel` (relations: none) | L |
| **GAP-DOM-21** | `identity_users` | MISSING-RELATION **NEEDS-DECISION** | Child table `dormitory_manager_assignments` has FK `user_id` -> identity_users.id [database/migrations/modules/dormitory/2026_07_16_000001_create_dormitory_manager_assignments_table.php:19]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Identity/Infrastructure/Persistence/Models/UserModel.php referencing `user_id` or `?` (relations: none) | L |
| **GAP-DOM-22** | `identity_users` | MISSING-RELATION **NEEDS-DECISION** | Child table `dormitory_unit_manager_assignments` has FK `user_id` -> identity_users.id [database/migrations/modules/dormitory/2026_07_16_000002_create_dormitory_unit_manager_assignments_table.php:19]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Identity/Infrastructure/Persistence/Models/UserModel.php referencing `user_id` or `?` (relations: none) | L |
| **GAP-DOM-23** | `identity_users` | MISSING-RELATION **NEEDS-DECISION** | Child table `dormitory_assignments` has FK `user_id` -> identity_users.id [database/migrations/modules/dormitory/2026_07_20_000001_create_dormitory_assignments_table.php:21]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Identity/Infrastructure/Persistence/Models/UserModel.php referencing `user_id` or `DormitoryAssignment` (relations: none) | L |
| **GAP-DOM-24** | `lottery_programs` | MISSING-RELATION **NEEDS-DECISION** | Child table `lottery_registrations` has FK `program_id` -> lottery_programs.id [database/migrations/modules/lottery/2026_06_30_000002_create_lottery_registrations_table.php:26]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryProgramModel.php referencing `program_id` or `LotteryRegistrationModel` (relations: none) | L |
| **GAP-DOM-25** | `lottery_programs` | MISSING-RELATION **NEEDS-DECISION** | Child table `lottery_results` has FK `program_id` -> lottery_programs.id [database/migrations/modules/lottery/2026_06_30_000003_create_lottery_results_table.php:25]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryProgramModel.php referencing `program_id` or `LotteryResultModel` (relations: none) | L |
| **GAP-DOM-26** | `lottery_registrations` | MISSING-RELATION **NEEDS-DECISION** | Child table `lottery_results` has FK `registration_id` -> lottery_registrations.id [database/migrations/modules/lottery/2026_06_30_000003_create_lottery_results_table.php:29]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryRegistrationModel.php referencing `registration_id` or `LotteryResultModel` (relations: none) | L |
| **GAP-DOM-27** | `lottery_programs` | MISSING-RELATION **NEEDS-DECISION** | Child table `lottery_eligible_snapshots` has FK `program_id` -> lottery_programs.id [database/migrations/modules/lottery/2026_06_30_000004_create_lottery_eligible_snapshots_table.php:26]; parent should expose hasMany/hasOne | No hasMany/hasOne in app/Modules/Lottery/Infrastructure/Persistence/Models/LotteryProgramModel.php referencing `program_id` or `LotteryEligibleSnapshotModel` (relations: none) | L |

## Counts by type

- **MISSING-MODEL:** 2
- **MISSING-RELATION:** 25
- **Total gaps:** 27
- **NEEDS-DECISION:** 14
- **CLEAN tables:** 23

## Notes (non-gaps)

- `app/Models/User.php` maps to framework `users` (out of domain list) — not a domain gap.
- UUID value columns without `foreign()` (e.g. `requests.employee_id`, `lottery_programs.dormitory_id`, `check_in_records.allocation_id`) match registry intentional non-FK pattern — not listed as MISSING-RELATION unless an Eloquent `belongsTo` claims them without a migration FK (none found in this pass for those columns).
- Inverse `hasMany` on parents (GAP-DOM-16..27) may be intentionally omitted under DDD Lite / Application-service reads — flagged **NEEDS-DECISION**, not auto-fix.
- No `MISSING-FIELD` / `DRIFT` / `ORPHAN-RELATION` rows remained after false-positive removal (SoftDeletes via BaseModel; hasMany child keys).

## Advisor (not applied)

| | |
|--|--|
| **Current** | Gap list as migration-FK ↔ Eloquent relation matrix |
| **Recommended** | Follow-up Fix waves split: (1) MISSING-MODEL assignment tables, (2) child `belongsTo` on Request/Lottery/Assignment, (3) Lead decision on inverse `hasMany` policy |
| **Reason** | Different risk and ownership; avoids mixing schema-less model stubs with relation sugar |
| **Risks** | Treating inverse relations as mandatory may fight modular boundaries |
| **Trade-offs** | Eloquent convenience vs explicit Application Services |

