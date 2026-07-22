---
ratified: true
ratified_by: Lead
ratified_wave: REGISTRY-RATIFY-02
ratified_at: 2026-07-22T11:31:02Z
snapshot_sha256: cdc8fb729db76b819d29a8d4a335c39869897d1fa5a7ad9bddb9c507e4a77dc5
scope_note: Ratification asserts registry accuracy only. It does NOT resolve blockers recorded inside (HDAC-01/02/03/05, Spec04 BLOCKED, Business Owner UNRESOLVED, DR-REG-03/04/05), and does NOT extend to .specify/** or docs/governance/**.
---
# .dormSys Database Map (RATIFIED)

> **Authority status:** RATIFIED (`REGISTRY-RATIFY-02`, Lead, 2026-07-22T11:31:02Z) — registry accuracy only.  
> **Scope limit:** Ratification does **not** resolve blockers recorded inside (HDAC-01/02/03/05, Spec04 BLOCKED, Business Owner UNRESOLVED, DR-REG-03/04/05), and does **not** extend to `.specify/**` or `docs/governance/**`.  
> **Bootstrap wave:** `REGISTRY-INIT-01`  
> **Generated:** 2026-07-22 (1405/04/31)  
> **Schema SoT (D-002):** `database/migrations` only. Static file parse. No `artisan migrate`. No live DB.  
> **Coverage:** 50 / 50 migration PHP files under `database/migrations/` read. WAVE-A: 34 stub tables expanded to column inventory.

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

## Spatie permission tables (DR-REG-01 CLOSED) — WAVE-MAP-B / GAP-DB-04

Physical names from `config/permission.php` → `table_names` (REGISTRY-FIX-01).  
**Authoritative DDL:** `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php` (published project migration; takes precedence over vendor stubs).  
**Runtime config observed:** `permission.teams` = `false`; `column_names.model_morph_key` = `model_id`; pivot keys default `permission_id` / `role_id` (`role_pivot_key` / `permission_pivot_key` are `null` → package defaults).  
Team-branch columns (`team_id` / team composite PKs) are **not** created when `teams` is false — inventories below reflect the `else` branches only.

| Config key | Physical table name | Source |
|------------|---------------------|--------|
| `roles` | `roles` | `config/permission.php` (`table_names.roles`) |
| `permissions` | `permissions` | `config/permission.php` (`table_names.permissions`) |
| `model_has_permissions` | `model_has_permissions` | `config/permission.php` (`table_names.model_has_permissions`) |
| `model_has_roles` | `model_has_roles` | `config/permission.php` (`table_names.model_has_roles`) |
| `role_has_permissions` | `role_has_permissions` | `config/permission.php` (`table_names.role_has_permissions`) |

Data-only (no DDL): `database/migrations/modules/identity/2026_07_15_000001_rename_student_records_permissions_to_employee_records.php` updates `DB::table('permissions')`.

**Cross-link:** activity_log → see Framework section (`#### \`activity_log\``).

#### `permissions`
- **Source:** `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php` (`Schema::create($tableNames['permissions'], …)` → physical `permissions`)

### permissions
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | bigint unsigned | NO | — | PK (`$table->id()`) |
| name | string | NO | — | UNIQUE composite with guard_name |
| guard_name | string | NO | — | UNIQUE composite with name |
| created_at | timestamp | YES | — | timestamps() |
| updated_at | timestamp | YES | — | timestamps() |

**Indexes (composite / named):**
- **UNIQUE:** `['name', 'guard_name']` — `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php:29`

#### `roles`
- **Source:** `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php` (`Schema::create($tableNames['roles'], …)` → physical `roles`; `teams` false → no `team_id`)

### roles
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | bigint unsigned | NO | — | PK (`$table->id()`) |
| name | string | NO | — | UNIQUE composite with guard_name |
| guard_name | string | NO | — | UNIQUE composite with name |
| created_at | timestamp | YES | — | timestamps() |
| updated_at | timestamp | YES | — | timestamps() |

**Indexes (composite / named):**
- **UNIQUE:** `['name', 'guard_name']` — `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php:44`

#### `model_has_permissions`
- **Source:** `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php` (`Schema::create($tableNames['model_has_permissions'], …)` → physical `model_has_permissions`; morph key column name `model_id` is **uuid**)

### model_has_permissions
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| permission_id | bigint unsigned | NO | — | FK → permissions.id; onDelete=cascade; part of composite PK |
| model_type | string | NO | — | part of composite PK; morph type |
| model_id | uuid | NO | — | part of composite PK; morph id (`model_morph_key`); INDEX with model_type |

**Indexes (composite / named):**
- **INDEX:** `['model_id', 'model_type']` named `model_has_permissions_model_id_model_type_index` — `:53`
- **PK:** `['permission_id', 'model_id', 'model_type']` named `model_has_permissions_permission_model_type_primary` — `:66-67`

#### `model_has_roles`
- **Source:** `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php` (`Schema::create($tableNames['model_has_roles'], …)` → physical `model_has_roles`)

### model_has_roles
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| role_id | bigint unsigned | NO | — | FK → roles.id; onDelete=cascade; part of composite PK |
| model_type | string | NO | — | part of composite PK; morph type |
| model_id | uuid | NO | — | part of composite PK; morph id (`model_morph_key`); INDEX with model_type |

**Indexes (composite / named):**
- **INDEX:** `['model_id', 'model_type']` named `model_has_roles_model_id_model_type_index` — `:76`
- **PK:** `['role_id', 'model_id', 'model_type']` named `model_has_roles_role_model_type_primary` — `:89-90`

#### `role_has_permissions`
- **Source:** `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php` (`Schema::create($tableNames['role_has_permissions'], …)` → physical `role_has_permissions`)

### role_has_permissions
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| permission_id | bigint unsigned | NO | — | FK → permissions.id; onDelete=cascade; part of composite PK |
| role_id | bigint unsigned | NO | — | FK → roles.id; onDelete=cascade; part of composite PK |

**Indexes (composite / named):**
- **PK:** `['permission_id', 'role_id']` named `role_has_permissions_permission_id_role_id_primary` — `:108`

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
- **jobs:** `id`, `string('queue')` + index, `longText('payload')`, `unsignedSmallInteger('attempts')`, `unsignedInteger('reserved_at')->nullable()`, `unsignedInteger('available_at'|'created_at')`
- **job_batches:** `string('id')` primary, `string('name')`, job counters, `longText('failed_job_ids')`, `mediumText('options')` nullable, timestamps as integers
- **failed_jobs:** `id`, `string('uuid')` unique, `string('connection'|'queue')`, `longText('payload'|'exception')`, `timestamp('failed_at')`; index `['connection','queue','failed_at']`

#### `activity_log`
- **Source (sole published DDL):** `database/migrations/2026_06_22_174847_create_activity_log_table.php` — `Schema::create('activity_log', …)`
- **Config:** `config/activitylog.php` has **no** `table_name` / connection key observed; physical name taken from migration literal `'activity_log'`.
- **Incremental migrations:** none published under `database/migrations/**` (no `add_event` / `add_batch_uuid` / alter follow-ups found). Final DDL = this file only.

### activity_log
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | bigint unsigned | NO | — | PK (`$table->id()`) — `:14` |
| log_name | string | YES | — | INDEX — `:15` |
| description | text | NO | — | — `:16` |
| subject_type | string | YES | — | morph (`nullableUuidMorphs('subject', 'subject')`) — `:17` |
| subject_id | uuid | YES | — | morph; composite INDEX with subject_type named `subject` — `:17` |
| event | string | YES | — | — `:18` |
| causer_type | string | YES | — | morph (`nullableUuidMorphs('causer', 'causer')`) — `:19` |
| causer_id | uuid | YES | — | morph; composite INDEX with causer_type named `causer` — `:19` |
| attribute_changes | json | YES | — | — `:20` |
| properties | json | YES | — | — `:21` |
| created_at | timestamp | YES | — | timestamps() — `:22` |
| updated_at | timestamp | YES | — | timestamps() — `:22` |

**Indexes (composite / named):**
- **INDEX:** `log_name` — `database/migrations/2026_06_22_174847_create_activity_log_table.php:15`
- **INDEX:** morph `subject` on `['subject_type','subject_id']` — `:17`
- **INDEX:** morph `causer` on `['causer_type','causer_id']` — `:19`

**DRIFT-NOTED vs prior abbreviated map entry:** NONE — prior bullet listed the same Blueprint calls; expansion only materializes morph columns and nullability.

---

### Identity / Employee / Request

#### `identity_users`
- **Source:** `database/migrations/modules/identity/2026_06_26_000001_create_identity_users_table.php`
- **Columns:** `uuid('id')` primary, `string('status'|'display_name'|'email')`, audit uuids, `timestamps()`, `softDeletes()`
- **Indexes:** unique email; index status

#### `employee_departments` **[EVOLVED]** (same-file + cross-file FK chain)
- **Source:** `database/migrations/modules/employee/2026_06_26_000001_create_employee_departments_table.php:13`
- **Status:** [EVOLVED] — chain retained from prior map
- **Chain:**
  1. CREATE — `…/employee/2026_06_26_000001_create_employee_departments_table.php`
  2. ALTER (same file `up`) — `foreign('parent_id')` → `employee_departments.id`
  3. ALTER — `…/employee/2026_06_26_000002_create_employee_employees_table.php` adds `foreign('manager_id')` → `employee_employees.id`

### employee_departments
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| name | string | NO | — | — |
| code | string | NO | — | UNIQUE |
| manager_id | uuid | YES | — | FK → employee_employees.id; onDelete=set null |
| parent_id | uuid | YES | — | FK → employee_departments.id; onDelete=set null |
| lottery_priority | integer | NO | 0 | — |
| status | string(32) | NO | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

#### `employee_employees`
- **Source:** `…/employee/2026_06_26_000002_create_employee_employees_table.php`
- **Columns:** `uuid('id')` primary, `uuid('identity_id')` unique, `string('employee_code'|'national_code')` unique, name fields, `uuid('department_id')`, `date('hire_date')`, `integer('base_lottery_score')`, `string('status')`, audit, `timestamps()`, `softDeletes()`
- **FK:** `department_id` → `employee_departments.id`
- **Indexes:** status

#### `employee_dependents`
- **Source:** `…/employee/2026_07_11_000003_create_employee_dependents_table.php`

### employee_dependents
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| employee_id | uuid | NO | — | FK → employee_employees.id; onDelete=cascade; INDEX |
| first_name | string | NO | — | — |
| last_name | string | NO | — | — |
| relationship | string(32) | NO | — | INDEX |
| age | integer | YES | — | — |
| national_code | string(10) | YES | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

#### `requests` **[EVOLVED]**
- **Source:** `database/migrations/modules/request/2026_06_26_000001_create_requests_table.php:13`
- **Status:** [EVOLVED] — chain retained from prior map
- **Chain:**
  1. CREATE — `…/request/2026_06_26_000001_create_requests_table.php`
  2. ALTER — `…/request/2026_07_18_000001_add_assigned_stage1_approver_identity_id_to_requests_table.php` adds `uuid('assigned_stage1_approver_identity_id')` nullable + **FK** → `identity_users.id` + index
  3. ALTER — `…/request/2026_07_20_000001_drop_assigned_stage1_approver_identity_fk_from_requests_table.php` **drops FK only** (column + index retained)

### requests
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | INDEX; PK |
| code | string | NO | — | UNIQUE |
| employee_id | uuid | NO | — | INDEX; INDEX |
| dormitory_id | uuid | NO | — | — |
| type | string(32) | NO | — | — |
| check_in_date | date | NO | — | — |
| check_out_date | date | NO | — | — |
| status | string(64) | NO | — | INDEX; INDEX |
| submitted_at | timestamp | YES | — | — |
| cancelled_at | timestamp | YES | — | — |
| rejection_reason | text | YES | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |
| assigned_stage1_approver_identity_id | uuid | YES | — | INDEX; FK previously present then DROPPED; column retained |

**Indexes (composite / named):**
- **INDEX:** $table->index(['employee_id', 'status']); — database/migrations/modules/request/2026_06_26_000001_create_requests_table.php:33

**Notes (retained):**
- 3. ALTER — `…/request/2026_07_20_000001_drop_assigned_stage1_approver_identity_fk_from_requests_table.php` **drops FK only** (column + index retained)

#### `request_approvals`
- **Source:** `…/request/2026_06_26_000002_create_request_approvals_table.php`

### request_approvals
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | INDEX; PK |
| request_id | uuid | NO | — | FK → requests.id; onDelete=cascade; INDEX; INDEX |
| stage | string(32) | NO | — | — |
| decision | string(32) | NO | — | — |
| approver_id | uuid | NO | — | — |
| reason | text | YES | — | — |
| decided_at | timestamp | NO | — | INDEX |
| created_at | timestamp | NO | — | — |

**Indexes (composite / named):**
- **INDEX:** $table->index(['request_id', 'decided_at']); — database/migrations/modules/request/2026_06_26_000002_create_request_approvals_table.php:29

#### `request_dependent_snapshots`
- **Source:** `…/request/2026_06_26_000003_create_request_dependent_snapshots_table.php`

### request_dependent_snapshots
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| request_id | uuid | NO | — | FK → requests.id; onDelete=cascade; INDEX |
| source_dependent_id | uuid | YES | — | — |
| first_name | string | NO | — | — |
| last_name | string | NO | — | — |
| relationship | string(64) | NO | — | — |
| national_code | string(10) | YES | — | — |
| captured_at | timestamp | NO | — | — |
| created_at | timestamp | NO | — | — |

#### `request_members`
- **Source:** `…/request/2026_06_26_000004_create_request_members_table.php`

### request_members
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | INDEX; PK |
| request_id | uuid | NO | — | FK → requests.id; onDelete=cascade; INDEX; INDEX |
| employee_id | uuid | NO | — | INDEX |
| is_leader | boolean | NO | false | — |
| created_at | timestamp | NO | — | — |

**Indexes (composite / named):**
- **INDEX:** $table->index(['request_id', 'employee_id']); — database/migrations/modules/request/2026_06_26_000004_create_request_members_table.php:26

#### `request_mission_details`
- **Source:** `…/request/2026_06_26_000005_create_request_mission_details_table.php`

### request_mission_details
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| request_id | uuid | NO | — | FK → requests.id; onDelete=cascade; PK |
| mission_document_url | string | YES | — | — |
| description | text | NO | — | — |
| created_at | timestamp | NO | — | — |

---

### Dormitory

#### `dormitories`
- **Source:** `…/dormitory/2026_07_10_000001_create_dormitories_table.php`

### dormitories
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| code | string | NO | — | UNIQUE |
| name | string | NO | — | INDEX |
| status | string(32) | NO | — | INDEX |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

**Notes (retained):**
- **RAW CHECK:** status ∈ available|unavailable|maintenance|inactive

#### `dormitory_buildings`
- **Source:** `…/000002_…`

### dormitory_buildings
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; PK |
| dormitory_id | uuid | NO | — | FK → dormitories.id; onDelete=restrict; UNIQUE; INDEX |
| code | string | NO | — | UNIQUE |
| name | string | NO | — | — |
| status | string(32) | NO | — | INDEX |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique(['dormitory_id', 'code']); — database/migrations/modules/dormitory/2026_07_10_000002_create_dormitory_buildings_table.php:31

#### `dormitory_floors`
- **Source:** `…/000003_…`

### dormitory_floors
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; PK |
| building_id | uuid | NO | — | FK → dormitory_buildings.id; onDelete=restrict; UNIQUE; INDEX |
| label | string | NO | — | UNIQUE |
| status | string(32) | NO | — | INDEX |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique(['building_id', 'label']); — database/migrations/modules/dormitory/2026_07_10_000003_create_dormitory_floors_table.php:30

#### `dormitory_rooms`
- **Source:** `…/000004_…`

### dormitory_rooms
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; PK |
| floor_id | uuid | NO | — | FK → dormitory_floors.id; onDelete=restrict; UNIQUE; INDEX |
| code | string | NO | — | UNIQUE |
| name | string | NO | — | — |
| capacity_total | integer | NO | — | — |
| status | string(32) | NO | — | INDEX |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique(['floor_id', 'code']); — database/migrations/modules/dormitory/2026_07_10_000004_create_dormitory_rooms_table.php:32

#### `dormitory_beds` **[EVOLVED]**
- **Source:** `database/migrations/modules/dormitory/2026_07_10_000005_create_dormitory_beds_table.php:14`
- **Status:** [EVOLVED] — chain retained from prior map
- **Chain:**
  1. CREATE — `…/dormitory/2026_07_10_000005_create_dormitory_beds_table.php` (occupancy CHECK vacant|occupied)
  2. ALTER/RAW — `…/dormitory/2026_07_12_000001_add_reserved_occupancy_and_signal_ref_to_dormitory_beds.php` expands CHECK to vacant|reserved|occupied; adds `uuid('last_signal_reference_id')` nullable

### dormitory_beds
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; INDEX; PK |
| room_id | uuid | NO | — | FK → dormitory_rooms.id; onDelete=restrict; UNIQUE; INDEX |
| label | string | NO | — | UNIQUE |
| status | string(32) | NO | — | INDEX; INDEX |
| physical_occupancy_state | string(32) | NO | — | INDEX; INDEX |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |
| last_signal_reference_id | uuid | YES | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique(['room_id', 'label']); — database/migrations/modules/dormitory/2026_07_10_000005_create_dormitory_beds_table.php:31
- **INDEX:** $table->index(['status', 'physical_occupancy_state'], 'dormitory_beds_availability_idx'); — database/migrations/modules/dormitory/2026_07_10_000005_create_dormitory_beds_table.php:35

**Raw SQL:**
- **RAW:** `ALTER TABLE dormitory_beds DROP CONSTRAINT IF EXISTS dormitory_beds_occupancy_check` — database/migrations/modules/dormitory/2026_07_12_000001_add_reserved_occupancy_and_signal_ref_to_dormitory_beds.php:14

#### `dormitory_manager_assignments`
- **Source:** `…/dormitory/2026_07_16_000001_…`

### dormitory_manager_assignments
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; PK |
| user_id | uuid | NO | — | FK → constrained('identity_users'); onDelete=restrict; UNIQUE |
| dormitory_id | uuid | NO | — | FK → constrained('dormitories'); onDelete=restrict; UNIQUE |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique(['user_id', 'dormitory_id']); — database/migrations/modules/dormitory/2026_07_16_000001_create_dormitory_manager_assignments_table.php:27

#### `dormitory_unit_manager_assignments`
- **Source:** `…/dormitory/2026_07_16_000002_…`

### dormitory_unit_manager_assignments
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; PK |
| user_id | uuid | NO | — | FK → constrained('identity_users'); onDelete=restrict; UNIQUE |
| room_id | uuid | NO | — | FK → constrained('dormitory_rooms'); onDelete=restrict; UNIQUE |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique(['user_id', 'room_id']); — database/migrations/modules/dormitory/2026_07_16_000002_create_dormitory_unit_manager_assignments_table.php:27

#### `dormitory_assignments`
- **Source:** `…/dormitory/2026_07_20_000001_…`

### dormitory_assignments
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| user_id | uuid | NO | — | FK → constrained('identity_users'); onDelete=restrict |
| dormitory_id | uuid | NO | — | FK → constrained('dormitories'); onDelete=restrict |
| assigned_at | timestamp | NO | — | — |
| revoked_at | timestamp | YES | — | — |

**Notes (retained):**
- **RAW:** partial unique index on `(user_id, dormitory_id) WHERE revoked_at IS NULL`

---

### Lottery / Allocation / Check-in / Voucher

#### `lottery_programs`
- **Source:** `…/lottery/2026_06_30_000001_…`

### lottery_programs
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| title | string | NO | — | — |
| dormitory_id | uuid | NO | — | INDEX; UUID ref only — no FK in migration |
| capacity | integer unsigned | NO | — | — |
| registration_starts_at | timestamp | NO | — | — |
| registration_ends_at | timestamp | NO | — | INDEX |
| status | string(64) | NO | — | INDEX; INDEX |
| random_seed | string | YES | — | — |
| scoring_config_version | string | YES | — | — |
| cancelled_reason | text | YES | — | — |
| locked_at | timestamp | YES | — | — |
| drawn_at | timestamp | YES | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

**Indexes (composite / named):**
- **INDEX:** $table->index(['status', 'registration_ends_at']); — database/migrations/modules/lottery/2026_06_30_000001_create_lottery_programs_table.php:34

#### `lottery_registrations`
- **Source:** `…/lottery/2026_06_30_000002_…`

### lottery_registrations
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; INDEX; PK |
| program_id | uuid | NO | — | FK → lottery_programs.id; onDelete=(unspecified in migration); UNIQUE; INDEX |
| request_id | uuid | NO | — | UNIQUE |
| employee_id | uuid | NO | — | INDEX; INDEX |
| weighted_score | decimal(16) | YES | — | — |
| enrolled_at | timestamp | NO | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique(['program_id', 'request_id']); — database/migrations/modules/lottery/2026_06_30_000002_create_lottery_registrations_table.php:30
- **INDEX:** $table->index(['program_id', 'employee_id']); — database/migrations/modules/lottery/2026_06_30_000002_create_lottery_registrations_table.php:32

#### `lottery_results`
- **Source:** `…/lottery/2026_06_30_000003_…`

### lottery_results
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; INDEX; PK |
| program_id | uuid | NO | — | FK → lottery_programs.id; onDelete=(unspecified in migration); UNIQUE; INDEX |
| registration_id | uuid | NO | — | FK → lottery_registrations.id; onDelete=(unspecified in migration); UNIQUE |
| rank | integer unsigned | NO | — | INDEX |
| outcome | string(32) | NO | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique(['program_id', 'registration_id']); — database/migrations/modules/lottery/2026_06_30_000003_create_lottery_results_table.php:33
- **INDEX:** $table->index(['program_id', 'rank']); — database/migrations/modules/lottery/2026_06_30_000003_create_lottery_results_table.php:34

#### `lottery_eligible_snapshots`
- **Source:** `…/lottery/2026_06_30_000004_…`

### lottery_eligible_snapshots
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| program_id | uuid | NO | — | FK → lottery_programs.id; onDelete=(unspecified in migration); UNIQUE |
| payload | json | NO | — | — |
| random_seed | string | NO | — | — |
| scoring_config | json | NO | — | — |
| scoring_config_version | string | YES | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

#### `allocations` **[EVOLVED]**
- **Source:** `database/migrations/modules/allocation/2026_07_01_000001_create_allocations_table.php:16`
- **Status:** [EVOLVED] — chain retained from prior map
- **Chain:**
  1. CREATE + RAW daterange — `…/allocation/2026_07_01_000001_create_allocations_table.php` (`btree_gist`; `date_range daterange NOT NULL`)
  2. RAW EXCLUDE — `…/allocation/2026_07_01_000003_add_allocation_overlap_exclusion.php` constraint `allocations_person_date_range_exclusion`

### allocations
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| person_id | uuid | NO | — | — |
| bed_id | uuid | NO | — | — |
| method | string(32) | NO | — | — |
| status | string(32) | NO | — | — |
| source_request_id | uuid | YES | — | — |
| source_lottery_result_id | uuid | YES | — | — |
| released_at | timestamptz | YES | — | — |
| release_reason | text | YES | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |
| date_range | daterange | NO | — | — |

**Raw SQL:**
- **RAW:** `ALTER TABLE allocations ADD COLUMN date_range daterange NOT NULL` — database/migrations/modules/allocation/2026_07_01_000001_create_allocations_table.php:33

**Notes (retained):**
- 1. CREATE + RAW daterange — `…/allocation/2026_07_01_000001_create_allocations_table.php` (`btree_gist`; `date_range daterange NOT NULL`)
- 2. RAW EXCLUDE — `…/allocation/2026_07_01_000003_add_allocation_overlap_exclusion.php` constraint `allocations_person_date_range_exclusion`

#### `allocation_items`
- **Source:** `…/allocation/2026_07_01_000002_…`

### allocation_items
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| allocation_id | uuid | NO | — | FK → allocations.id; onDelete=cascade |
| bed_id | uuid | NO | — | — |
| sequence | smallint unsigned | NO | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

#### `check_in_records`
- **Source:** `…/check_in/2026_07_01_000001_…`
- **Columns:** uuid PK; `allocation_id`, `operator_id` UUIDs; `timestampTz` check-in/out; audit; softDeletes — **no FK declared**

#### `voucher_issuance_triggers`
- **Source:** `…/voucher/2026_07_01_000001_…` + RAW `stay_period daterange`

### voucher_issuance_triggers
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | INDEX; PK |
| correlation_id | string | NO | — | UNIQUE |
| employee_id | uuid | NO | — | INDEX |
| dormitory_id | uuid | YES | — | — |
| request_id | uuid | YES | — | — |
| source | string(32) | NO | — | — |
| status | string(32) | NO | — | INDEX |
| issuance_path_completed_at | timestamptz | YES | — | — |
| superseded_by_trigger_id | uuid | YES | — | — |
| upstream_facts | jsonb | NO | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |
| stay_period | daterange | NO | — | — |

**Indexes (composite / named):**
- **INDEX:** $table->index(['employee_id', 'status']); — database/migrations/modules/voucher/2026_07_01_000001_create_voucher_issuance_triggers_table.php:34

**Raw SQL:**
- **RAW:** `ALTER TABLE voucher_issuance_triggers ADD COLUMN stay_period daterange NOT NULL` — database/migrations/modules/voucher/2026_07_01_000001_create_voucher_issuance_triggers_table.php:37

#### `voucher_eligibility_outcomes`
- **Source:** `…/voucher/2026_07_01_000002_…`

### voucher_eligibility_outcomes
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| trigger_id | uuid | NO | — | UNIQUE |
| correlation_id | string | NO | — | INDEX |
| employee_id | uuid | NO | — | INDEX |
| dormitory_id | uuid | YES | — | — |
| request_id | uuid | YES | — | — |
| outcome | string(32) | NO | — | — |
| reason_codes | jsonb | NO | — | — |
| rationale | text | NO | — | — |
| evaluated_at | timestamptz | NO | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

#### `vouchers`
- **Source:** `…/voucher/2026_07_01_000003_…` + RAW `stay_period daterange`

### vouchers
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| eligibility_outcome_id | uuid | NO | — | UNIQUE |
| trigger_id | uuid | NO | — | — |
| correlation_id | string | NO | — | INDEX |
| employee_id | uuid | NO | — | INDEX |
| dormitory_id | uuid | YES | — | — |
| request_id | uuid | YES | — | — |
| upstream_source | string(32) | NO | — | — |
| code | string(32) | NO | — | UNIQUE |
| lifecycle_state | string(32) | NO | — | INDEX |
| validity_start | timestamptz | NO | — | — |
| validity_end | timestamptz | NO | — | — |
| issued_at | timestamptz | NO | — | — |
| archived_at | timestamptz | YES | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |
| stay_period | daterange | NO | — | — |

**Raw SQL:**
- **RAW:** `ALTER TABLE vouchers ADD COLUMN stay_period daterange NOT NULL` — database/migrations/modules/voucher/2026_07_01_000003_create_vouchers_table.php:40

#### `voucher_lifecycle_transitions`
- **Source:** `…/voucher/2026_07_01_000004_…`

### voucher_lifecycle_transitions
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| voucher_id | uuid | NO | — | INDEX |
| from_state | string(32) | YES | — | — |
| to_state | string(32) | NO | — | — |
| correlation_id | string | NO | — | INDEX |
| occurred_at | timestamptz | NO | — | — |
| payload | jsonb | NO | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | — |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

---

### Audit / Notification / Reporting / System / Workflow

#### `audit_logs`
- **Source:** `…/audit/2026_07_02_000001_…` (+ RAW partial index)

### audit_logs
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | INDEX; INDEX; INDEX; PK |
| correlation_id | string(191) | NO | — | UNIQUE |
| event_type | string(64) | NO | — | INDEX |
| entity_type | string(64) | NO | — | INDEX |
| entity_id | uuid | NO | — | INDEX |
| actor_type | string(16) | NO | — | INDEX |
| actor_id | string(128) | NO | — | INDEX |
| source_context | string(32) | NO | — | — |
| old_values | jsonb | YES | — | — |
| new_values | jsonb | YES | — | — |
| metadata | jsonb | YES | — | — |
| payload_hash | string(64) | NO | — | — |
| occurred_at | timestamptz | NO | — | INDEX; INDEX; INDEX |
| archived_at | timestamptz | YES | — | — |
| created_at | timestamptz | NO | — | — |

**Indexes (composite / named):**
- **INDEX:** $table->index(['entity_type', 'entity_id', 'occurred_at'], 'audit_logs_entity_idx'); — database/migrations/modules/audit/2026_07_02_000001_create_audit_logs_table.php:32
- **INDEX:** $table->index(['actor_type', 'actor_id', 'occurred_at'], 'audit_logs_actor_idx'); — database/migrations/modules/audit/2026_07_02_000001_create_audit_logs_table.php:33
- **INDEX:** $table->index(['event_type', 'occurred_at'], 'audit_logs_event_idx'); — database/migrations/modules/audit/2026_07_02_000001_create_audit_logs_table.php:34

#### `notification_logs`
- **Source:** `…/notification/2026_07_02_000001_…` (+ RAW partial unread index)

### notification_logs
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; INDEX; PK |
| correlation_id | string(128) | NO | — | UNIQUE |
| notification_type | string(64) | NO | — | UNIQUE |
| recipient_employee_id | uuid | NO | — | UNIQUE; INDEX |
| title | string(255) | NO | — | — |
| message | text | NO | — | — |
| entity_type | string(64) | YES | — | — |
| entity_id | uuid | YES | — | — |
| deep_link_route | string(255) | YES | — | — |
| source_context | string(32) | NO | — | — |
| priority | string(16) | NO | — | — |
| read_at | timestamptz | YES | — | — |
| archived_at | timestamptz | YES | — | INDEX |
| delivery_status | string(16) | NO | — | — |
| skip_reason | string(64) | YES | — | — |
| created_by | uuid | YES | — | — |
| updated_by | uuid | YES | — | — |
| deleted_by | uuid | YES | — | — |
| created_at | timestamp | YES | — | INDEX |
| updated_at | timestamp | YES | — | — |
| deleted_at | timestamp | YES | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique( ['correlation_id', 'recipient_employee_id', 'notification_type'], 'notification_logs_dedup_uniq', ); — database/migrations/modules/notification/2026_07_02_000001_create_notification_logs_table.php:36
- **INDEX:** $table->index( ['recipient_employee_id', 'archived_at', 'created_at'], 'notification_logs_inbox_idx', ); — database/migrations/modules/notification/2026_07_02_000001_create_notification_logs_table.php:40

#### `reporting_projection_cursors`
- **Source:** `…/reporting/2026_07_03_000001_…`

### reporting_projection_cursors
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | PK |
| projection_family | string(32) | NO | — | UNIQUE |
| archive_visibility_tier | string(32) | NO | — | UNIQUE |
| last_source_audit_log_id | uuid | YES | — | — |
| last_occurred_at | timestamptz | YES | — | — |
| projection_version | string(32) | NO | — | — |
| refreshed_at | timestamptz | YES | — | — |
| refresh_mode | string(32) | NO | — | — |
| status | string(16) | NO | — | — |
| last_error | text | YES | — | — |
| created_at | timestamptz | NO | — | — |
| updated_at | timestamptz | NO | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique( ['projection_family', 'archive_visibility_tier'], 'reporting_projection_cursors_family_tier_uniq', ); — database/migrations/modules/reporting/2026_07_03_000001_create_reporting_projection_cursors_table.php:27

#### `reporting_correlation_projection_entries`
- **Source:** `…/reporting/2026_07_03_000002_…`

### reporting_correlation_projection_entries
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; INDEX; INDEX; PK |
| correlation_id | string(191) | NO | — | UNIQUE; INDEX |
| source_audit_log_id | uuid | NO | — | UNIQUE; INDEX |
| occurred_at | timestamptz | NO | — | INDEX |
| entity_type | string(64) | NO | — | — |
| entity_id | uuid | NO | — | — |
| actor_type | string(16) | NO | — | — |
| actor_id | string(128) | NO | — | — |
| event_type | string(64) | NO | — | — |
| source_context | string(32) | NO | — | — |
| archive_visibility_tier | string(32) | NO | — | UNIQUE |
| ingested_at | timestamptz | NO | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique( ['correlation_id', 'source_audit_log_id', 'archive_visibility_tier'], 'reporting_corr_proj_entry_uniq', ); — database/migrations/modules/reporting/2026_07_03_000002_create_reporting_correlation_projection_entries_table.php:27
- **INDEX:** $table->index( ['correlation_id', 'occurred_at'], 'reporting_corr_proj_correlation_idx', ); — database/migrations/modules/reporting/2026_07_03_000002_create_reporting_correlation_projection_entries_table.php:31
- **INDEX:** $table->index( ['source_audit_log_id'], 'reporting_corr_proj_source_audit_idx', ); — database/migrations/modules/reporting/2026_07_03_000002_create_reporting_correlation_projection_entries_table.php:35

#### `reporting_audit_window_aggregates` **[EVOLVED]**
- **Source:** `database/migrations/modules/reporting/2026_07_03_000003_create_reporting_audit_window_aggregates_table.php:13`
- **Status:** [EVOLVED] — chain retained from prior map
- **Chain:** CREATE `…/000003_…` → ALTER `…/2026_07_05_000001_add_distinct_ref_tracking_to_reporting_aggregates.php` adds `jsonb('distinct_entity_refs'|'distinct_actor_refs')`

### reporting_audit_window_aggregates
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | INDEX; PK |
| window_start | timestamptz | NO | — | UNIQUE; INDEX |
| window_end | timestamptz | NO | — | UNIQUE; INDEX |
| granularity | string(16) | NO | — | UNIQUE; INDEX |
| event_type | string(64) | YES | — | UNIQUE |
| source_context | string(32) | YES | — | UNIQUE |
| actor_type | string(16) | YES | — | UNIQUE |
| entity_type | string(64) | YES | — | UNIQUE |
| archive_visibility_tier | string(32) | NO | — | UNIQUE |
| event_count | integer unsigned | NO | — | — |
| distinct_entity_count | integer unsigned | NO | — | — |
| distinct_actor_count | integer unsigned | NO | — | — |
| top_event_types | jsonb | YES | — | — |
| refreshed_at | timestamptz | NO | — | — |
| projection_version | string(32) | NO | — | — |
| distinct_entity_refs | jsonb | NO | '[]' | — |
| distinct_actor_refs | jsonb | NO | '[]' | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique( [ 'window_start', 'window_end', 'granularity', 'event_type', 'source_context', 'actor_type', 'entity_type', 'archive_visibility_tier', ], 'repor — database/migrations/modules/reporting/2026_07_03_000003_create_reporting_audit_window_aggregates_table.php:30
- **INDEX:** $table->index( ['window_start', 'window_end', 'granularity'], 'reporting_audit_window_agg_window_idx', ); — database/migrations/modules/reporting/2026_07_03_000003_create_reporting_audit_window_aggregates_table.php:43

#### `reporting_actor_activity_summaries` **[EVOLVED]**
- **Source:** `database/migrations/modules/reporting/2026_07_03_000004_create_reporting_actor_activity_summaries_table.php:13`
- **Status:** [EVOLVED] — chain retained from prior map
- **Chain:** CREATE `…/000004_…` → ALTER same `2026_07_05_000001_…` adds `jsonb('distinct_entity_refs')`

### reporting_actor_activity_summaries
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; INDEX; PK |
| actor_type | string(16) | NO | — | UNIQUE; INDEX |
| actor_id | string(128) | NO | — | UNIQUE; INDEX |
| window_start | timestamptz | NO | — | UNIQUE; INDEX |
| window_end | timestamptz | NO | — | UNIQUE |
| granularity | string(16) | NO | — | UNIQUE |
| event_count | integer unsigned | NO | — | — |
| distinct_event_types | jsonb | NO | — | — |
| distinct_entities_touched | integer unsigned | NO | — | — |
| archive_visibility_tier | string(32) | NO | — | UNIQUE |
| refreshed_at | timestamptz | NO | — | — |
| projection_version | string(32) | NO | — | — |
| distinct_entity_refs | jsonb | NO | '[]' | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique( [ 'actor_type', 'actor_id', 'window_start', 'window_end', 'granularity', 'archive_visibility_tier', ], 'reporting_actor_activity_summ_uniq', ); — database/migrations/modules/reporting/2026_07_03_000004_create_reporting_actor_activity_summaries_table.php:27
- **INDEX:** $table->index( ['actor_type', 'actor_id', 'window_start'], 'reporting_actor_activity_actor_idx', ); — database/migrations/modules/reporting/2026_07_03_000004_create_reporting_actor_activity_summaries_table.php:38

#### `reporting_projection_ingest_receipts`
- **Source:** `…/reporting/2026_07_03_000005_…`

### reporting_projection_ingest_receipts
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | UNIQUE; PK |
| projection_family | string(32) | NO | — | UNIQUE |
| source_audit_log_id | uuid | NO | — | UNIQUE |
| archive_visibility_tier | string(32) | NO | — | UNIQUE |
| ingested_at | timestamptz | NO | — | — |

**Indexes (composite / named):**
- **UNIQUE:** $table->unique( ['projection_family', 'source_audit_log_id', 'archive_visibility_tier'], 'reporting_proj_ingest_receipt_uniq', ); — database/migrations/modules/reporting/2026_07_03_000005_create_reporting_projection_ingest_receipts_table.php:20

#### `settings`
- **Source:** `…/system/2026_07_20_000001_create_settings_table.php`
- **Columns:** `uuid('id')` primary, `string('key')` unique, `json('value')`, `timestamps()`

#### `workflow_request_approval_instances`
- **Source:** `…/workflow/2026_07_21_000001_…` (+ RAW partial unique one-running-per-request)

### workflow_request_approval_instances
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | INDEX; PK |
| request_id | uuid | NO | — | INDEX |
| status | string(32) | NO | — | INDEX |
| stage1_approver_identity_id | uuid | YES | — | — |
| current_stage | string(32) | YES | — | — |
| started_at | timestamptz | NO | — | — |
| completed_at | timestamptz | YES | — | — |
| created_at | timestamptz | YES | — | — |
| updated_at | timestamptz | YES | — | — |

**Indexes (composite / named):**
- **INDEX:** $table->index(['request_id', 'status']); — database/migrations/modules/workflow/2026_07_21_000001_create_workflow_request_approval_instances_table.php:24

**Notes (retained):**
- **Source:** `…/workflow/2026_07_21_000001_…` (+ RAW partial unique one-running-per-request)

#### `workflow_request_approval_step_executions`
- **Source:** `…/workflow/2026_07_21_000002_…`

### workflow_request_approval_step_executions
| Column | Type | Nullable | Default | FK / Notes |
|--------|------|----------|---------|------------|
| id | uuid | NO | — | INDEX; PK |
| workflow_instance_id | uuid | NO | — | FK → workflow_request_approval_instances.id; onDelete=cascade; INDEX |
| stage | string(32) | NO | — | — |
| status | string(32) | NO | — | — |
| actor_identity_id | uuid | YES | — | — |
| reason | text | YES | — | — |
| activated_at | timestamptz | NO | — | INDEX |
| completed_at | timestamptz | YES | — | — |
| created_at | timestamptz | YES | — | — |
| updated_at | timestamptz | YES | — | — |

**Indexes (composite / named):**
- **INDEX:** $table->index(['workflow_instance_id', 'activated_at']); — database/migrations/modules/workflow/2026_07_21_000002_create_workflow_request_approval_step_executions_table.php:30

**Notes (retained):**
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

- WAVE-A expanded 34 former stub product tables to column inventories (static migration parse). Non-stub entries (`users`, framework tables, `identity_users`, `employee_employees`, `activity_log`, `check_in_records`, `settings`, Telescope tooling) were not rewritten in WAVE-A.
- WAVE-MAP-B / GAP-DB-04: Spatie **permission** table DDL expanded from `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php` (with `config/permission.php` for physical names / `teams=false`). Spatie `activity_log` lives under Framework; full column inventory completed in WAVE-MAP-B.1 (sole published migration `database/migrations/2026_06_22_174847_create_activity_log_table.php`).
- Module Infrastructure models not mapped (**DR-REG-05** OPEN).
- No live `\d` / `migrate:status` consulted (wave constraint).
- FK `onDelete=(unspecified in migration)` means no explicit `cascadeOnDelete` / `nullOnDelete` / `onDelete(...)` in the migration (DB/driver default applies).
