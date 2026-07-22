---
ratified: true
ratified_by: Lead
ratified_wave: REGISTRY-RATIFY-02
ratified_at: 2026-07-22T11:31:02Z
snapshot_sha256: 3346145788be2f9d49fd260479a8481b2900ca333c0c71dbe68cc945b7506c9f
scope_note: Ratification asserts registry accuracy only. It does NOT resolve blockers recorded inside (HDAC-01/02/03/05, Spec04 BLOCKED, Business Owner UNRESOLVED, DR-REG-03/04/05), and does NOT extend to .specify/** or docs/governance/**.
---
# .dormSys Database Map (DRAFT)

> **Authority status:** DRAFT — not authoritative until Lead issues `RATIFY REGISTRIES`.  
> **Bootstrap wave:** `REGISTRY-INIT-01`  
> **Generated:** 2026-07-22 (1405/04/31)  
> **Schema SoT (D-002):** `database/migrations` only. Static file parse. No `artisan migrate`. No live DB.  
> **Coverage:** 50 / 50 migration PHP files under `database/migrations/` read.

---

## Method

| Step | Evidence |
|------|----------|
| Enumerate migrations | Recursive `*.php` under `database/migrations/` (count = 50) |
| Extract CREATE/ALTER | `Schema::create`, `$schema->create`, `Schema::table`, raw `ALTER TABLE` / `DB::statement` in `up()` |
| Columns / FK / indexes | Declared in migration closures (abbreviated: method + first string arg) |
| Evolved tables | Tables touched by CREATE + later ALTER/RAW across files → tag **[EVOLVED]** |

---

## Model ↔ table (scope: `app/Models` only)

| Model path | Explicit `$table`? | Mapping recorded |
|------------|--------------------|------------------|
| `app/Models/User.php` | **No** `$table` property observed | **`users`** — Laravel convention (DR-REG-02 CLOSED). Migration: `database/migrations/0001_01_01_000000_create_users_table.php`. |

No other PHP files under `app/Models/` observed.

---

## Spatie permission tables (DR-REG-01 CLOSED)

Physical names from `config/permission.php` → `table_names` (REGISTRY-FIX-01). Migration creates via `$tableNames[…]`: `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php`.

| Config key | Physical table name | Source |
|------------|---------------------|--------|
| `roles` | `roles` | `config/permission.php` (`table_names.roles`) |
| `permissions` | `permissions` | `config/permission.php` (`table_names.permissions`) |
| `model_has_permissions` | `model_has_permissions` | `config/permission.php` (`table_names.model_has_permissions`) |
| `model_has_roles` | `model_has_roles` | `config/permission.php` (`table_names.model_has_roles`) |
| `role_has_permissions` | `role_has_permissions` | `config/permission.php` (`table_names.role_has_permissions`) |

Data-only (no DDL): `database/migrations/modules/identity/2026_07_15_000001_rename_student_records_permissions_to_employee_records.php` updates `DB::table('permissions')`.

---

## Table inventory (literal CREATE names)

### Framework / package

#### `users`
- **Source:** `database/migrations/0001_01_01_000000_create_users_table.php`
- **Model:** `App\Models\User` (mapped by Laravel convention — no `$table` property; per DR-REG-02 CLOSED)
- **Columns:** `id`, `string('name')`, `string('email')` unique, `timestamp('email_verified_at')` nullable, `string('password')`, `rememberToken()`, `timestamps()`
- **FK / indexes:** unique email

#### `password_reset_tokens`
- **Source:** same file
- **Columns:** `string('email')` primary, `string('token')`, `timestamp('created_at')` nullable

#### `sessions`
- **Source:** same file
- **Columns:** `string('id')` primary, `foreignId('user_id')` nullable + index, `string('ip_address')` nullable, `text('user_agent')` nullable, `longText('payload')`, `integer('last_activity')` + index
- **FK:** `foreignId('user_id')` — no `constrained()` / `on()` in migration

#### `cache` / `cache_locks`
- **Source:** `database/migrations/0001_01_01_000001_create_cache_table.php`
- **cache:** `string('key')` primary, `mediumText('value')`, `bigInteger('expiration')` + index
- **cache_locks:** `string('key')` primary, `string('owner')`, `bigInteger('expiration')` + index

#### `jobs` / `job_batches` / `failed_jobs`
- **Source:** `database/migrations/0001_01_01_000002_create_jobs_table.php`
- **jobs:** `id`, `string('queue')` + index, `longText('payload')`, `unsignedTinyInteger('attempts')`, `unsignedInteger('reserved_at'|'available_at'|'created_at')`
- **job_batches:** `string('id')` primary, `string('name')`, job counters, `longText('failed_job_ids')`, `mediumText('options')` nullable, timestamps as integers
- **failed_jobs:** `id`, `string('uuid')` unique, `string('connection'|'queue')`, `longText('payload'|'exception')`, `timestamp('failed_at')`; index `['connection','queue','failed_at']`

#### `activity_log`
- **Source:** `database/migrations/2026_06_22_174847_create_activity_log_table.php`
- **Columns:** `$table->id()`, `$table->string('log_name')->nullable()->index()`, `$table->text('description')`, `$table->nullableUuidMorphs('subject', 'subject')`, `$table->string('event')->nullable()`, `$table->nullableUuidMorphs('causer', 'causer')`, `$table->json('attribute_changes')->nullable()`, `$table->json('properties')->nullable()`, `$table->timestamps()`

---

### Identity / Employee / Request

#### `identity_users`
- **Source:** `database/migrations/modules/identity/2026_06_26_000001_create_identity_users_table.php`
- **Columns:** `uuid('id')` primary, `string('status'|'display_name'|'email')`, audit uuids, `timestamps()`, `softDeletes()`
- **Indexes:** unique email; index status

#### `employee_departments` **[EVOLVED]** (same-file + cross-file FK chain)
- **Chain:**
  1. CREATE — `…/employee/2026_06_26_000001_create_employee_departments_table.php`
  2. ALTER (same file `up`) — `foreign('parent_id')` → `employee_departments.id`
  3. ALTER — `…/employee/2026_06_26_000002_create_employee_employees_table.php` adds `foreign('manager_id')` → `employee_employees.id`
- **Columns (create):** `uuid('id')` primary, `string('name'|'code')` code unique, `uuid('manager_id'|'parent_id')` nullable, `integer('lottery_priority')`, `string('status')`, audit, `timestamps()`, `softDeletes()`

#### `employee_employees`
- **Source:** `…/employee/2026_06_26_000002_create_employee_employees_table.php`
- **Columns:** `uuid('id')` primary, `uuid('identity_id')` unique, `string('employee_code'|'national_code')` unique, name fields, `uuid('department_id')`, `date('hire_date')`, `integer('base_lottery_score')`, `string('status')`, audit, `timestamps()`, `softDeletes()`
- **FK:** `department_id` → `employee_departments.id`
- **Indexes:** status

#### `employee_dependents`
- **Source:** `…/employee/2026_07_11_000003_create_employee_dependents_table.php`
- **FK:** `employee_id` → `employee_employees.id`
- **Indexes:** employee_id, relationship

#### `requests` **[EVOLVED]**
- **Chain:**
  1. CREATE — `…/request/2026_06_26_000001_create_requests_table.php`
  2. ALTER — `…/request/2026_07_18_000001_add_assigned_stage1_approver_identity_id_to_requests_table.php` adds `uuid('assigned_stage1_approver_identity_id')` nullable + **FK** → `identity_users.id` + index
  3. ALTER — `…/request/2026_07_20_000001_drop_assigned_stage1_approver_identity_fk_from_requests_table.php` **drops FK only** (column + index retained)
- **Create columns:** `uuid('id')` primary, `string('code')` unique, `uuid('employee_id'|'dormitory_id')`, `string('type'|'status')`, dates, timestamps nullable, `text('rejection_reason')` nullable, audit, `timestamps()`, `softDeletes()`
- **Indexes:** employee_id, status, `[employee_id, status]`

#### `request_approvals`
- **Source:** `…/request/2026_06_26_000002_create_request_approvals_table.php`
- **FK:** `request_id` → `requests.id`
- **Indexes:** request_id; `[request_id, decided_at]`

#### `request_dependent_snapshots`
- **Source:** `…/request/2026_06_26_000003_create_request_dependent_snapshots_table.php`
- **FK:** `request_id` → `requests.id`

#### `request_members`
- **Source:** `…/request/2026_06_26_000004_create_request_members_table.php`
- **FK:** `request_id` → `requests.id`

#### `request_mission_details`
- **Source:** `…/request/2026_06_26_000005_create_request_mission_details_table.php`
- **PK:** `uuid('request_id')`; **FK:** → `requests.id`

---

### Dormitory

#### `dormitories`
- **Source:** `…/dormitory/2026_07_10_000001_create_dormitories_table.php`
- **Indexes:** unique code; status; name
- **RAW CHECK:** status ∈ available|unavailable|maintenance|inactive

#### `dormitory_buildings`
- **Source:** `…/000002_…`
- **FK:** `dormitory_id` → `dormitories.id`
- **Indexes:** unique `[dormitory_id, code]`; status CHECK (RAW)

#### `dormitory_floors`
- **Source:** `…/000003_…`
- **FK:** `building_id` → `dormitory_buildings.id`
- **Indexes:** unique `[building_id, label]`; status CHECK

#### `dormitory_rooms`
- **Source:** `…/000004_…`
- **FK:** `floor_id` → `dormitory_floors.id`
- **Indexes:** unique `[floor_id, code]`; capacity CHECK; status CHECK

#### `dormitory_beds` **[EVOLVED]**
- **Chain:**
  1. CREATE — `…/dormitory/2026_07_10_000005_create_dormitory_beds_table.php` (occupancy CHECK vacant|occupied)
  2. ALTER/RAW — `…/dormitory/2026_07_12_000001_add_reserved_occupancy_and_signal_ref_to_dormitory_beds.php` expands CHECK to vacant|reserved|occupied; adds `uuid('last_signal_reference_id')` nullable
- **FK:** `room_id` → `dormitory_rooms.id`
- **Indexes:** unique `[room_id, label]`; status; physical_occupancy_state; named `dormitory_beds_availability_idx`

#### `dormitory_manager_assignments`
- **Source:** `…/dormitory/2026_07_16_000001_…`
- **FK:** `user_id` → `identity_users`; `dormitory_id` → `dormitories`
- **Unique:** `[user_id, dormitory_id]`

#### `dormitory_unit_manager_assignments`
- **Source:** `…/dormitory/2026_07_16_000002_…`
- **FK:** `user_id` → `identity_users`; `room_id` → `dormitory_rooms`
- **Unique:** `[user_id, room_id]`

#### `dormitory_assignments`
- **Source:** `…/dormitory/2026_07_20_000001_…`
- **FK:** `user_id` → `identity_users`; `dormitory_id` → `dormitories`
- **RAW:** partial unique index on `(user_id, dormitory_id) WHERE revoked_at IS NULL`

---

### Lottery / Allocation / Check-in / Voucher

#### `lottery_programs`
- **Source:** `…/lottery/2026_06_30_000001_…`
- **Indexes:** dormitory_id; status; `[status, registration_ends_at]`
- **FK:** none declared (dormitory_id UUID only)

#### `lottery_registrations`
- **Source:** `…/lottery/2026_06_30_000002_…`
- **FK:** `program_id` → `lottery_programs.id`
- **Unique:** `[program_id, request_id]`

#### `lottery_results`
- **Source:** `…/lottery/2026_06_30_000003_…`
- **FK:** `program_id` → `lottery_programs.id`; `registration_id` → `lottery_registrations.id`
- **Unique:** `[program_id, registration_id]`

#### `lottery_eligible_snapshots`
- **Source:** `…/lottery/2026_06_30_000004_…`
- **FK:** `program_id` → `lottery_programs.id`
- **Unique:** `program_id`

#### `allocations` **[EVOLVED]**
- **Chain:**
  1. CREATE + RAW daterange — `…/allocation/2026_07_01_000001_create_allocations_table.php` (`btree_gist`; `date_range daterange NOT NULL`)
  2. RAW EXCLUDE — `…/allocation/2026_07_01_000003_add_allocation_overlap_exclusion.php` constraint `allocations_person_date_range_exclusion`

#### `allocation_items`
- **Source:** `…/allocation/2026_07_01_000002_…`
- **FK:** `allocation_id` → `allocations.id`

#### `check_in_records`
- **Source:** `…/check_in/2026_07_01_000001_…`
- **Columns:** uuid PK; `allocation_id`, `operator_id` UUIDs; `timestampTz` check-in/out; audit; softDeletes — **no FK declared**

#### `voucher_issuance_triggers`
- **Source:** `…/voucher/2026_07_01_000001_…` + RAW `stay_period daterange`

#### `voucher_eligibility_outcomes`
- **Source:** `…/voucher/2026_07_01_000002_…`

#### `vouchers`
- **Source:** `…/voucher/2026_07_01_000003_…` + RAW `stay_period daterange`

#### `voucher_lifecycle_transitions`
- **Source:** `…/voucher/2026_07_01_000004_…`

---

### Audit / Notification / Reporting / System / Workflow

#### `audit_logs`
- **Source:** `…/audit/2026_07_02_000001_…` (+ RAW partial index)

#### `notification_logs`
- **Source:** `…/notification/2026_07_02_000001_…` (+ RAW partial unread index)

#### `reporting_projection_cursors`
- **Source:** `…/reporting/2026_07_03_000001_…`

#### `reporting_correlation_projection_entries`
- **Source:** `…/reporting/2026_07_03_000002_…`

#### `reporting_audit_window_aggregates` **[EVOLVED]**
- **Chain:** CREATE `…/000003_…` → ALTER `…/2026_07_05_000001_add_distinct_ref_tracking_to_reporting_aggregates.php` adds `jsonb('distinct_entity_refs'|'distinct_actor_refs')`

#### `reporting_actor_activity_summaries` **[EVOLVED]**
- **Chain:** CREATE `…/000004_…` → ALTER same `2026_07_05_000001_…` adds `jsonb('distinct_entity_refs')`

#### `reporting_projection_ingest_receipts`
- **Source:** `…/reporting/2026_07_03_000005_…`

#### `settings`
- **Source:** `…/system/2026_07_20_000001_create_settings_table.php`
- **Columns:** `uuid('id')` primary, `string('key')` unique, `json('value')`, `timestamps()`

#### `workflow_request_approval_instances`
- **Source:** `…/workflow/2026_07_21_000001_…` (+ RAW partial unique one-running-per-request)

#### `workflow_request_approval_step_executions`
- **Source:** `…/workflow/2026_07_21_000002_…`
- **FK:** `workflow_instance_id` → `workflow_request_approval_instances.id`
- **RAW:** partial unique one-pending-per-instance

---

## [EVOLVED] summary

| Table | Migration chain |
|-------|-----------------|
| `requests` | `2026_06_26_000001` → `2026_07_18_000001` → `2026_07_20_000001` |
| `dormitory_beds` | `2026_07_10_000005` → `2026_07_12_000001` |
| `allocations` | `2026_07_01_000001` → `2026_07_01_000003` |
| `employee_departments` | `2026_06_26_000001` (+ FK) → `2026_06_26_000002` (manager FK) |
| `reporting_audit_window_aggregates` | `2026_07_03_000003` → `2026_07_05_000001` |
| `reporting_actor_activity_summaries` | `2026_07_03_000004` → `2026_07_05_000001` |

---

## Tooling (non-product)

Per **DR-REG-06 CLOSED** (REGISTRY-FIX-01): Telescope tables are tooling-only, not product schema. Content relocated from Framework / package (no deletion).

#### `telescope_entries` / `telescope_entries_tags` / `telescope_monitoring`
- **Source:** `database/migrations/2026_06_22_184914_create_telescope_entries_table.php` (`$schema->create`)
- **Classification:** tooling-only (DR-REG-06 CLOSED)
- **telescope_entries:** `bigIncrements('sequence')`, `uuid('uuid'|'batch_id')`, `string('family_hash'|'type')` nullable as declared, `boolean('should_display_on_index')`, `longText('content')`, `dateTime('created_at')` nullable; unique uuid; indexes batch_id, family_hash, created_at, `[type, should_display_on_index]`
- **telescope_entries_tags:** `uuid('entry_uuid')`, `string('tag')`; primary `[entry_uuid, tag]`; index tag; **FK** `entry_uuid` → `telescope_entries.uuid`
- **telescope_monitoring:** `string('tag')` primary

---

## Parse limitations (facts)

- Column lists are abbreviated from Blueprint calls; default lengths/nullability may require re-open of the cited file for exact DDL.
- Spatie physical names recorded from `config/permission.php` (**DR-REG-01 CLOSED**); column DDL remains in migration `2026_06_26_000002_create_permission_tables.php` (not expanded this wave).
- Module Infrastructure models not mapped (**DR-REG-05** OPEN).
- No live `\d` / `migrate:status` consulted (wave constraint).
