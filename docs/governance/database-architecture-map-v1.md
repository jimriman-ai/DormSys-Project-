# Database Architecture Map v1

**Protocol:** DormSys Database Remediation — Controlled Implementation Protocol v1.3  
**Phase:** −1 Discovery only (documentation — no migrations, schema changes, or HD decisions)  
**Date:** 1405/04/30 | 2026-07-21  
**Authority:** OBSERVED evidence only. **STOP** — Phase 0 (HD) does not begin until Lead approves this Map.  
**Data sources:** `database/migrations/**` (50 files); Eloquent models under `app/Modules/**/Infrastructure/Persistence/Models` + `app/Models/User.php`; companion live-DB snapshot in `docs/governance/schema-migration-audit-2026-07-21.md` (Sail PostgreSQL, all 50 migrations Ran, business row counts = 0).  
**Live DB this session:** compose file not resolved from agent cwd — row estimates below use companion audit where noted.

---

## STOP gate

| Gate | Status |
|------|--------|
| Outputs 1–8 complete | ✅ OBSERVED |
| Map v1 composed | ✅ this document |
| Lead approval of Map v1 | ⏳ required before Phase 0 HD |
| Any migration / schema / HD close | ❌ forbidden until Lead approval |

---

## خروجی 1 — Table Catalog

| table_name | module_owner | purpose | data_source | row_estimate |
|------------|--------------|---------|-------------|--------------|
| users | laravel (legacy web) | Credential/session principal (bigint PK) | migration + live | 0 |
| password_reset_tokens | laravel | Password reset | migration + live | 0 |
| sessions | laravel | Session store; `user_id` bigint index, **no FK** | migration + live | — |
| cache / cache_locks | laravel | Cache | migration + live | — |
| jobs / job_batches / failed_jobs | laravel | Queue | migration + live | — |
| activity_log | spatie (root) | Package activity log | migration + live | 0 |
| telescope_entries / _tags / monitoring | telescope | Dev observability | migration + live | — |
| identity_users | Identity | Authoritative identity aggregate (UUID) | migration + live | 0 |
| permissions / roles / model_has_* / role_has_permissions | Identity / Spatie | RBAC | migration + live | roles=0 |
| employee_departments | Employee | Org units | migration + live | 0 |
| employee_employees | Employee | Employees; soft `identity_id` | migration + live | 0 |
| employee_dependents | Employee | Dependents | migration + live | 0 |
| requests | Request | Accommodation requests | migration + live | 0 |
| request_approvals | Request | Append-only approval history | migration + live | 0 |
| request_dependent_snapshots | Request | Dependent snapshots at submit | migration + live | 0 |
| request_members | Request | Group members | migration + live | 0 |
| request_mission_details | Request | Mission extras (PK = request_id) | migration + live | 0 |
| workflow_request_approval_instances | Workflow | Orchestration instances (soft request_id) | migration + live | 0 |
| workflow_request_approval_step_executions | Workflow | Step executions | migration + live | 0 |
| dormitories | Dormitory | Sites | migration + live | 0 |
| dormitory_buildings / floors / rooms / beds | Dormitory | Physical hierarchy | migration + live | 0 |
| dormitory_manager_assignments | Dormitory | Manager↔dormitory (hard FK Identity) | migration + live | 0 |
| dormitory_unit_manager_assignments | Dormitory | Unit-mgr↔room (hard FK Identity) | migration + live | 0 |
| dormitory_assignments | Dormitory | Assignments with soft revoke | migration + live | 0 |
| allocations / allocation_items | Allocation | Stay allocations + items; exclusion on person+range | migration + live | 0 |
| check_in_records | CheckIn | Check-in/out operational stay | migration + live | 0 |
| lottery_programs / registrations / results / eligible_snapshots | Lottery | **FROZEN** module tables | migration + live | 0 |
| voucher_issuance_triggers / eligibility_outcomes / vouchers / lifecycle_transitions | Voucher | External voucher path (soft UUID graph) | migration + live | 0 |
| notification_logs | Notification | Inbox / delivery log | migration + live | 0 |
| audit_logs | Audit | Append-only domain audit | migration + live | 0 |
| reporting_projection_cursors / correlation_projection_entries / audit_window_aggregates / actor_activity_summaries / projection_ingest_receipts | Reporting | **FROZEN** projections | migration + live | 0 |
| settings | System | Key/value config (incl. lottery scoring) | migration + live | 0 |

**Table count (approx):** 56 live tables per companion audit (framework + domain).

---

## خروجی 2 — Column Catalog (summary by table family)

> Full Blueprint-level detail was extracted from all 50 migrations. Below: authoritative column shapes for remediation-critical tables. Framework tables follow Laravel defaults (`users.id` bigint AI; `sessions.user_id` unsignedBigInteger nullable indexed **without FK**).

### identity_users
| column | type | nullable | default | notes |
|--------|------|----------|---------|-------|
| id | uuid | no | — | PK |
| status | string(32) | no | — | indexed |
| display_name | string | no | — | |
| email | string | yes | — | unique |
| created_by / updated_by / deleted_by | uuid | yes | — | soft actor refs |
| timestamps + softDeletes | — | yes | — | |

### users (legacy)
| column | type | nullable | default | notes |
|--------|------|----------|---------|-------|
| id | bigint AI | no | AI | PK — **not UUID** |
| name / email / password | string | no | — | email unique |
| email_verified_at | timestamp | yes | — | |
| remember_token | string(100) | yes | — | |
| timestamps | — | yes | — | no softDeletes |

### sessions
| column | type | nullable | default | notes |
|--------|------|----------|---------|-------|
| id | string | no | — | PK |
| user_id | unsignedBigInteger | yes | — | indexed; **HD-F candidate** (no FK) |
| ip_address / user_agent / payload / last_activity | … | … | — | |

### requests (+ Stage-1 column)
| column | type | nullable | default | notes |
|--------|------|----------|---------|-------|
| id | uuid | no | — | PK |
| code | string | no | — | unique |
| employee_id | uuid | no | — | soft → employee_employees |
| assigned_stage1_approver_identity_id | uuid | yes | — | soft → identity_users; **FK dropped** 2026_07_20 |
| dormitory_id | uuid | no | — | soft → dormitories |
| type / status | string | no | — | |
| check_in_date / check_out_date | date | no | — | |
| submitted_at / cancelled_at / rejection_reason | … | yes | — | |
| audit uuids + timestamps + softDeletes | — | — | — | |

### allocations
| column | type | nullable | default | notes |
|--------|------|----------|---------|-------|
| id | uuid | no | — | PK |
| person_id | uuid | no | — | soft; exclusion key |
| bed_id | uuid | no | — | soft → dormitory_beds; **HD-E** |
| method / status | string(32) | no | — | |
| source_request_id / source_lottery_result_id | uuid | yes | — | soft |
| date_range | daterange | no | — | raw SQL |
| released_at / release_reason | timestamptz / text | yes | — | |
| audit + softDeletes | — | — | — | |

### check_in_records
| column | type | nullable | default | notes |
|--------|------|----------|---------|-------|
| id | uuid | no | — | PK |
| allocation_id | uuid | no | — | soft; **no index**; **HD-A** |
| checked_in_at | timestamptz | no | — | |
| checked_out_at | timestamptz | yes | — | open stay = NULL |
| operator_id | uuid | no | — | soft → identity |
| audit + softDeletes | — | — | — | |

### Other domain tables (shape pattern)
| Family | PK | SoftDeletes | Notable columns |
|--------|-----|-------------|-----------------|
| employee_* | uuid | Y | `identity_id` unique soft; dept self-FK + manager FK |
| request children | uuid (mission: request_id PK) | N | FK → requests CASCADE; soft employee/approver/dependent |
| dormitory hierarchy | uuid | Y | hard FKs restrict; status CHECKs; beds occupancy CHECK (+ reserved) |
| dormitory_*_assignments | uuid | N (assignments use revoked_at) | **hard FK** → identity_users |
| lottery_* | uuid | Y | soft dormitory/request/employee; hard FK within lottery |
| voucher_* | uuid | Y | soft UUID graph; daterange stay_period; **no FKs** |
| workflow_* | uuid | N | soft request/identity; hard FK steps→instances |
| audit_logs | uuid | N | append-only; actor_id string(128) |
| notification_logs | uuid | Y | soft employee; dedup unique |
| reporting_* | uuid | N | soft audit_log refs; jsonb aggregates |
| settings | uuid | N | key unique, value json |

---

## خروجی 3 — Constraint Inventory (material)

| table | constraint_name / inferred | type | definition | on_delete |
|-------|----------------------------|------|------------|-----------|
| *most domain* | PRIMARY | PK | `id` uuid (or mission `request_id`) | — |
| users | users_email_unique | UNIQUE | email | — |
| identity_users | identity_users_email_unique | UNIQUE | email | — |
| employee_departments | FK parent_id | FK | → employee_departments.id | SET NULL |
| employee_departments | FK manager_id | FK | → employee_employees.id | SET NULL |
| employee_employees | FK department_id | FK | → employee_departments.id | SET NULL |
| employee_employees | unique identity_id / employee_code / national_code | UNIQUE | — | — |
| employee_dependents | FK employee_id | FK | → employee_employees.id | CASCADE |
| request_* children | FK request_id | FK | → requests.id | CASCADE |
| requests | *(was)* assigned_stage1 FK | FK | → identity_users — **DROPPED** | was RESTRICT |
| dormitory hierarchy | FKs chain | FK | building→dormitory→…→bed | RESTRICT |
| dormitory_* | *_status_check | CHECK | status enum sets | — |
| dormitory_beds | occupancy CHECK | CHECK | vacant\|reserved\|occupied | — |
| dormitory_manager_assignments | FKs + unique(user,dormitory) | FK/UNIQUE | → identity_users, dormitories | RESTRICT |
| dormitory_unit_manager_assignments | FKs + unique(user,room) | FK/UNIQUE | → identity_users, rooms | RESTRICT |
| dormitory_assignments | FKs | FK | → identity_users, dormitories | RESTRICT |
| lottery_registrations / results / snapshots | FKs → programs (+ registration) | FK | intra-lottery | default |
| allocations | allocations_person_date_range_exclusion | EXCLUSION | gist person_id =, date_range && WHERE status=active | — |
| allocation_items | FK allocation_id | FK | → allocations | CASCADE |
| voucher_* | UNIQUE correlation / trigger / outcome / code | UNIQUE | no FKs | — |
| notification_logs | notification_logs_dedup_uniq | UNIQUE | (correlation_id, recipient, type) | — |
| audit_logs | audit_logs_correlation_uniq | UNIQUE | correlation_id | — |
| settings | settings_key_unique | UNIQUE | key | — |
| workflow steps | FK workflow_instance_id | FK | → instances | CASCADE |
| Spatie pivots | FKs to roles/permissions | FK | CASCADE | CASCADE |
| telescope_entries_tags | FK entry_uuid | FK | → telescope_entries.uuid | CASCADE |

---

## خروجی 4 — Index Inventory (material / non-PK)

| table | index_name | columns | type | unique | condition |
|-------|------------|---------|------|--------|-----------|
| sessions | sessions_user_id_index | user_id | btree | no | — |
| dormitory_assignments | dormitory_assignments_user_dormitory_active_uidx | user_id, dormitory_id | btree | **yes** | `revoked_at IS NULL` |
| workflow_request_approval_instances | workflow_req_approval_instances_one_running_per_request | request_id | btree | **yes** | `status = 'running'` |
| workflow_request_approval_step_executions | workflow_req_approval_steps_one_pending_per_instance | workflow_instance_id | btree | **yes** | `status = 'pending'` |
| allocations | allocations_person_date_range_exclusion | person_id, date_range | **gist** | exclusion | `status = 'active'` |
| audit_logs | audit_logs_active_idx | archived_at, occurred_at DESC | btree | no | `archived_at IS NULL` |
| notification_logs | notification_logs_unread_idx | recipient_employee_id, read_at | btree | no | `read_at IS NULL` |
| requests | assigned_stage1_approver_identity_id | column | btree | no | survives FK drop |
| check_in_records | — | — | — | — | **no allocation_id index; no open-stay unique (HD-A)** |
| reporting_* | named family/dim uniques | composites | btree | yes | — |
| *(plus)* | standard status/FK indexes on most domain tables | — | btree | mixed | — |

---

## خروجی 5 — Relationship Map

### Hard FK (enforced)

| source | → target | type | enforced |
|--------|----------|------|----------|
| employee_departments.parent_id | employee_departments.id | FK | yes SET NULL |
| employee_departments.manager_id | employee_employees.id | FK | yes SET NULL |
| employee_employees.department_id | employee_departments.id | FK | yes SET NULL |
| employee_dependents.employee_id | employee_employees.id | FK | yes CASCADE |
| request_approvals / snapshots / members / mission.request_id | requests.id | FK | yes CASCADE |
| dormitory_buildings.dormitory_id | dormitories.id | FK | yes RESTRICT |
| dormitory_floors.building_id | dormitory_buildings.id | FK | yes RESTRICT |
| dormitory_rooms.floor_id | dormitory_floors.id | FK | yes RESTRICT |
| dormitory_beds.room_id | dormitory_rooms.id | FK | yes RESTRICT |
| dormitory_manager_assignments.user_id | identity_users.id | FK | yes RESTRICT |
| dormitory_manager_assignments.dormitory_id | dormitories.id | FK | yes RESTRICT |
| dormitory_unit_manager_assignments.user_id | identity_users.id | FK | yes RESTRICT |
| dormitory_unit_manager_assignments.room_id | dormitory_rooms.id | FK | yes RESTRICT |
| dormitory_assignments.user_id | identity_users.id | FK | yes RESTRICT |
| dormitory_assignments.dormitory_id | dormitories.id | FK | yes RESTRICT |
| lottery_registrations.program_id | lottery_programs.id | FK | yes |
| lottery_results.program_id / registration_id | lottery_* | FK | yes |
| lottery_eligible_snapshots.program_id | lottery_programs.id | FK | yes |
| allocation_items.allocation_id | allocations.id | FK | yes CASCADE |
| workflow_…_step_executions.workflow_instance_id | workflow_…_instances.id | FK | yes CASCADE |
| Spatie model_has_* / role_has_* | permissions/roles | FK | yes CASCADE |
| telescope_entries_tags.entry_uuid | telescope_entries.uuid | FK | yes CASCADE |

### Soft UUID / soft-ref (not enforced) — selected

| source | → presumed target | type | enforced |
|--------|-------------------|------|----------|
| employee_employees.identity_id | identity_users.id | soft-uuid | no |
| requests.employee_id | employee_employees.id | soft-uuid | no |
| requests.dormitory_id | dormitories.id | soft-uuid | no |
| requests.assigned_stage1_approver_identity_id | identity_users.id | soft-uuid | no (FK removed) |
| request_approvals.approver_id | identity_users.id | soft-uuid | no |
| allocations.person_id / bed_id / source_* | employee / beds / request / lottery_result | soft-uuid | no |
| check_in_records.allocation_id / operator_id | allocations / identity_users | soft-uuid | no |
| voucher_* graph | triggers/outcomes/vouchers/employee/dormitory/request | soft-uuid | no |
| workflow_…_instances.request_id | requests.id | soft-uuid | no |
| workflow_* identity columns | identity_users.id | soft-uuid | no |
| lottery_programs.dormitory_id; registrations.request_id/employee_id | dormitories / requests / employees | soft-uuid | no |
| reporting_* source_audit_log_id | audit_logs.id | soft-uuid | no |
| *._created_by / updated_by / deleted_by | identity_users.id | soft-ref | no |
| sessions.user_id | **users.id (bigint)** | soft-ref (typed) | no — **HD-F** |
| activity_log subject/causer morph | polymorphic UUID | soft-ref | no |

---

## خروجی 6 — Module Boundary Map

| Module | Tables owned | Cross-module refs | Boundary type |
|--------|--------------|-------------------|---------------|
| laravel / spatie / telescope | users, sessions, cache*, jobs*, activity_log, telescope_* | sessions→users (typed, no FK) | none / soft |
| Identity | identity_users; Spatie permission tables | morph model_id → identity UUID | hard FK within Spatie; soft actor uuids |
| Employee | employee_departments, employees, dependents | identity_id soft → Identity | soft UUID (+ intra hard FK) |
| Request | requests + 4 children | employee/dormitory/stage1 soft; children hard→requests | soft UUID cross; hard intra |
| Workflow | instances, step_executions | request_id + identity soft | soft UUID (**shell — treat carefully**) |
| Dormitory | dormitories…beds; 3 assignment tables | **hard FK** → identity_users on assignments | **hard FK exception** (OQ-DORM-04 / WP-DORM-04 HOLD) |
| Allocation | allocations, allocation_items | person/bed/request/lottery soft | soft UUID + exclusion |
| CheckIn | check_in_records | allocation/operator soft | soft UUID |
| Lottery | 4 lottery_* | dormitory/request/employee soft | soft UUID — **FROZEN** |
| Voucher | 4 voucher_* | soft graph only | soft UUID (GAP-PREUI-07) |
| Notification | notification_logs | employee soft | soft UUID |
| Audit | audit_logs | polymorphic soft | none |
| Reporting | 5 reporting_* | audit soft | soft UUID — **FROZEN** |
| System | settings | none | none |

**FROZEN (protocol):** Lottery, Reporting, workflow shell — no migration / schema / contract / model changes without Lead override of freeze.

---

## خروجی 7 — Migration Inventory

| migration_file | timestamp | table_affected | module | status | depends_on |
|----------------|-----------|----------------|--------|--------|------------|
| `0001_01_01_000000_create_users_table.php` | 0001_01_01_000000 | users, password_reset_tokens, sessions | laravel | create | — |
| `0001_01_01_000001_create_cache_table.php` | 0001_01_01_000001 | cache, cache_locks | laravel | create | — |
| `0001_01_01_000002_create_jobs_table.php` | 0001_01_01_000002 | jobs, job_batches, failed_jobs | laravel | create | — |
| `2026_06_22_174847_create_activity_log_table.php` | 2026_06_22_174847 | activity_log | spatie | create | **no down()** — **HD-C** |
| `2026_06_22_184914_create_telescope_entries_table.php` | 2026_06_22_184914 | telescope_* | telescope | create | — |
| `modules/identity/2026_06_26_000001_…` | 2026_06_26_000001 | identity_users | identity | create | — |
| `modules/identity/2026_06_26_000002_…` | 2026_06_26_000002 | permissions, roles, pivots | identity | create | config permission |
| `modules/employee/2026_06_26_000001_…` | 2026_06_26_000001 | employee_departments | employee | create | self-FK |
| `modules/employee/2026_06_26_000002_…` | 2026_06_26_000002 | employee_employees; alter departments | employee | create+alter | depts |
| `modules/request/2026_06_26_000001…000005` | 2026_06_26_* | requests + children | request | create | requests first |
| `modules/lottery/2026_06_30_000001…000004` | 2026_06_30_* | lottery_* | lottery | create | programs→children |
| `modules/allocation/2026_07_01_000001…000003` | 2026_07_01_* | allocations, items, exclusion | allocation | create+alter | btree_gist |
| `modules/voucher/2026_07_01_000001…000004` | 2026_07_01_* | voucher_* | voucher | create | — |
| `modules/check_in/2026_07_01_000001_…` | 2026_07_01_000001 | check_in_records | check_in | create | — |
| `modules/audit/2026_07_02_000001_…` | 2026_07_02_000001 | audit_logs | audit | create | — |
| `modules/notification/2026_07_02_000001_…` | 2026_07_02_000001 | notification_logs | notification | create | — |
| `modules/reporting/2026_07_03_*` + `2026_07_05_*` | 2026_07_03/05 | reporting_* | reporting | create+alter | — |
| `modules/dormitory/2026_07_10_000001…000005` | 2026_07_10_* | dorm hierarchy | dormitory | create | chain |
| `modules/employee/2026_07_11_000003_…` | 2026_07_11_000003 | employee_dependents | employee | create | employees |
| `modules/dormitory/2026_07_12_000001_…` | 2026_07_12_000001 | dormitory_beds alter | dormitory | alter | beds create |
| `modules/identity/2026_07_15_000001_…` | 2026_07_15_000001 | permissions (data rename) | identity | data | permissions |
| `modules/dormitory/2026_07_16_000001…000002` | 2026_07_16_* | manager / unit-manager assigns | dormitory | create | identity + dorm |
| `modules/request/2026_07_18_000001_…` | 2026_07_18_000001 | requests + FK stage1 | request | alter | identity_users |
| `modules/request/2026_07_20_000001_…` | 2026_07_20_000001 | **drop stage1 FK only** | request | alter | prior add FK |
| `modules/dormitory/2026_07_20_000001_…` | 2026_07_20_000001 | dormitory_assignments | dormitory | create | identity + dorm |
| `modules/system/2026_07_20_000001_…` | 2026_07_20_000001 | settings | system | create | — |
| `modules/workflow/2026_07_21_000001…000002` | 2026_07_21_* | workflow_* | workflow | create | instances→steps |

### FK create → drop → assume chain (critical)

1. **2026_07_18** — creates `assigned_stage1_approver_identity_id` + **FK** → `identity_users`  
2. **2026_07_20** — **drops FK**; column + index retained (soft UUID)  
3. Downstream code/tests must **not** assume FK exists (OQ-REQ-02 CLOSED Option A)

### ModuleMigrationPathsTest gap (**HD-G**)

`tests/Unit/Support/ModuleMigrationPathsTest.php` registers: identity, employee, request, workflow, dormitory, allocation, lottery, voucher, notification, audit, reporting.  
**Missing from provider data set:** `system`, `check_in` — though both providers call `loadMigrationsFrom(...)` in code.

### Timestamp collisions

Same basename timestamps across modules (e.g. `2026_07_01_000001` allocation/voucher/check_in; `2026_07_20_000001` request/dormitory/system). Laravel orders by full path basename; logical `depends_on` above is safer than filesystem order alone.

---

## خروجی 8 — Model Alignment Map

| Model | Table | PK strategy | Relations | Casts (high level) | SoftDeletes | UUID behavior |
|-------|-------|-------------|-----------|--------------------|-------------|---------------|
| `App\Models\User` | users | bigint AI, int keyType | — | password hashed | N | **no HasUuid** |
| `Identity\UserModel` | identity_users | uuid string non-inc | Spatie HasRoles | status enum | Y (BaseModel) | HasUuid UUIDv7 |
| `EmployeeModel` | employee_employees | uuid | dept, dependents | status, hire_date | Y | HasUuid; identity_id not fillable |
| `DepartmentModel` | employee_departments | uuid | parent/children/manager/employees | status, priority | Y | HasUuid |
| `DependentModel` | employee_dependents | uuid | employee | relationship, age | Y | HasUuid |
| `RequestModel` | requests | uuid | — | type, status, dates | Y | HasUuid |
| `RequestApprovalModel` | request_approvals | uuid | — | stage, decision | N | HasUuid; append-only |
| `RequestDependentSnapshotModel` | request_dependent_snapshots | uuid | — | relationship | N | HasUuid |
| `RequestMemberModel` | request_members | uuid | — | is_leader | N | HasUuid |
| `RequestMissionDetailsModel` | request_mission_details | **request_id** string | — | created_at | N | **no HasUuid**; PK fillable |
| `RequestApprovalWorkflowInstanceModel` | workflow_…_instances | uuid | steps HasMany | status, stage | N | HasUuid |
| `RequestApprovalWorkflowStepExecutionModel` | workflow_…_steps | uuid | instance BelongsTo | stage, status | N | HasUuid |
| Dormitory structure models (5) | dormitories…beds | uuid | hierarchy relations | ResourceStatus | Y | HasUuid via BaseModel |
| `DormitoryAssignment` | dormitory_assignments | uuid | dormitory | assigned/revoked | N (revoked_at) | HasUuid |
| `AllocationModel` / `AllocationItemModel` | allocations / items | uuid | items / allocation | method, status | Y | HasUuid |
| Lottery models (4) | lottery_* | uuid | — | states, scores, json | Y | HasUuid — **FROZEN** |
| Voucher models (4) | voucher_* | uuid | — | enums, jsonb | Y | HasUuid |
| `CheckInRecordModel` | check_in_records | uuid | — | check times | Y | HasUuid |
| `NotificationLogModel` | notification_logs | uuid | — | enums, times | Y | HasUuid |
| `AuditLogModel` | audit_logs | uuid | — | jsonb, enums | N | HasUuid; append-only |
| Reporting models (5) | reporting_* | uuid | — | projection enums | N | HasUuid — **FROZEN** |

### Tables without app Eloquent model
| Table | Access pattern |
|-------|----------------|
| settings | `DB::table` / QueryBuilderSettingsReader |
| dormitory_manager_assignments | no model observed |
| dormitory_unit_manager_assignments | no model observed |
| Spatie / activity_log / framework / telescope | vendor / framework |

### Dangerous mismatches — OBSERVED
| Finding | Risk | HD link |
|---------|------|---------|
| Dual auth: `users` (bigint) vs `identity_users` (uuid) | Session `user_id` cannot point at Identity UUID; guards split web vs api/identity | **HD-H** |
| No uuid-migration + int-$keyType on domain models | None observed for module models | — |
| `bedId ?? $summary->dormitoryId` in CreateAllocationFromRequestAction | Can persist dormitory UUID into `allocations.bed_id` | **HD-B** (Action; Phase 2 unless Lead scopes) |
| `LotteryScoringConfigReader` does not implement `LotteryScoringConfigPort` | Port orphan | **HD-D** — **FROZEN if Lottery** |
| check_in_records: no partial unique open stay | Race → two open check-ins | **HD-A** |
| activity_log: no `down()` | Rollback incomplete | **HD-C** |
| Exclusion on person+range only (not bed_id) | Same bed double-book possible if person differs | **HD-E** |
| ModuleMigrationPathsTest omits system + check_in | Test coverage gap only | **HD-G** |

### HD-H Evidence (from Output 8 + auth config)
| Aspect | Evidence |
|--------|----------|
| `config/auth.php` defaults | `guard` = `web` → provider `users` → `App\Models\User` / table `users` |
| Identity guards | `api`, `identity` → provider `identity` → `UserModel` / `identity_users` |
| Dormitory assignment FKs | `user_id` → **identity_users** (UUID), not `users` |
| sessions.user_id | bigint → aligns with **users.id**, not Identity |
| UserModel.getAuthPassword | throws — Identity is not password store |

---

## Analysis — Database Architecture Map v1 (synthesis)

### A. Architecture posture (observed)
1. **Modular soft-UUID boundary** is the dominant cross-module pattern (Request, Allocation, CheckIn, Voucher, Workflow→Request, Employee→Identity).  
2. **Hard FK exceptions** cluster on Dormitory assignment tables → Identity (ledger: OQ-DORM-04 / WP-DORM-04 HOLD).  
3. **Dual identity store** is intentional transitional debt: legacy `users` + authoritative `identity_users`.  
4. **PostgreSQL-specific integrity** is used where domain demands it: allocation GiST exclusion, partial uniques (workflow, dormitory assignments, audit/notification partial indexes).  
5. **FROZEN modules** (Lottery, Reporting) already have full tables; remediation must not touch them without freeze lift.

### B. Migration health
| Topic | Verdict |
|-------|---------|
| Apply status (companion audit) | 50/50 Ran |
| FK create→drop chain | Documented; final state = soft Stage-1 column |
| Rollback holes | activity_log missing `down()` |
| Path registration test | system + check_in missing from ModuleMigrationPathsTest |
| Timestamp collisions | Present; rely on logical depends_on |

### C. Model ↔ schema alignment
| Topic | Verdict |
|-------|---------|
| Domain UUID PKs vs HasUuid | Aligned (UUIDv7 app-side) |
| SoftDeletes vs softDeletes() | Aligned for BaseModel tables |
| Orphan tables without models | settings + 2 dormitory pivots |
| Dual User models | By design — HD-H must close before Auth remediation |

### D. Phase 0 HD readiness matrix (evidence only — no decisions)

| HD | Evidence pointer | Touch surface if closed |
|----|------------------|-------------------------|
| **HD-A** | check_in_records: no index; no partial unique on open allocation | 1 migration |
| **HD-B** | `CreateAllocationFromRequestAction` L41 fallback dormitoryId→bedId | Action (not schema-first) |
| **HD-C** | activity_log migration has no `down()` | migration patch |
| **HD-D** | Reader ≠ Port; Lottery FROZEN | refactor or delete port — freeze applies |
| **HD-E** | exclusion = person + date_range only | migration alter |
| **HD-F** | sessions.user_id indexed, no FK; type=bigint→users | migration |
| **HD-G** | ModuleMigrationPathsTest omits system, check_in | test-only |
| **HD-H** | Output 8 + auth.php dual stack | Auth/Backend — must close before Auth changes |

### E. Suggested Phase 1 dependency order (PROPOSED — not authorized)
1. Core / no FK: settings already exists; framework tables exist  
2. Hardening indexes/constraints after HD close (HD-A, HD-E, HD-F, HD-C)  
3. Do **not** schedule Lottery/Reporting/workflow-shell schema work while FROZEN  
4. Each item = independent PR with up+down + rollback test + Lead merge gate  

### F. Out of Phase −1 scope (explicit)
- No HD answers  
- No migrations written  
- No UI/Livewire/Auth code changes  
- No Model remediations (Phase 2)

---

## Lead approval block

```
[ ] Map v1 APPROVED — proceed to Phase 0 (close HD-A … HD-H with this evidence)
[ ] Map v1 REJECTED — revision required (note gaps below)
[ ] Partial — list sections to re-discover
```

**Suggested Lead prompt after approval:**
> APPROVE Database Architecture Map v1 — BEGIN Phase 0 HD decisions (HD-A through HD-H)

---

## Evidence index

| Artifact | Path |
|----------|------|
| This Map | `docs/governance/database-architecture-map-v1.md` |
| Companion live-DB audit | `docs/governance/schema-migration-audit-2026-07-21.md` |
| Migrations | `database/migrations/**` (50) |
| Models | `app/Modules/*/Infrastructure/Persistence/Models/*`, `app/Models/User.php` |
| Auth config | `config/auth.php` |
| Migration path test | `tests/Unit/Support/ModuleMigrationPathsTest.php` |
| HD-B code | `app/Modules/Allocation/Application/Services/CreateAllocationFromRequestAction.php` |
| HD-D code | `LotteryScoringConfigReader.php`, `LotteryScoringConfigPort.php` |
