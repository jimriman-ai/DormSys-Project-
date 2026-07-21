# UUID Inventory (Wave 1 / HD-W1-Q3)

**Date:** 2026-07-21  
**Scope:** Document current PK type + generation mechanism. **No v7 migrations in Wave 1.**  
**Evidence sources:** migrations under `database/migrations/**`, `app/Support/Traits/HasUuid.php`, `app/Shared/Infrastructure/Uuid/UuidGenerator.php`.

## Generation mechanisms (canonical)

| Mechanism | Version | Evidence |
|-----------|---------|----------|
| `HasUuid` trait on `creating` | **UUIDv7** (`Ramsey\Uuid\Uuid::uuid7()`) | `app/Support/Traits/HasUuid.php:21` |
| `UuidGenerator` | **UUIDv7** | `app/Shared/Infrastructure/Uuid/UuidGenerator.php` |
| Domain VO / service explicit `Uuid::uuid7()` | **UUIDv7** | e.g. Workflow VOs, DormitoryStructureMutationService |
| DB column `uuid` without app default | **Unspecified at DB** — value supplied by app on insert | Most module migrations: `$table->uuid('id')->primary()` only |
| `$table->id()` / `bigIncrements` | **Integer auto-increment** | HD-W1-Q2 KEEP exceptions |

`BaseModel` uses `HasUuid` → Eloquent models extending it get **v7** on create when id is null.

---

## A. Domain / module tables (UUID PK)

| Table | PK type | Generation (observed) | Detected version | Wave 2 target |
|-------|---------|----------------------|------------------|---------------|
| `identity_users` | uuid PK | `UserModel` → `BaseModel` → `HasUuid` | v7 at create | KEEP v7 (no Wave 1 migrate) |
| `employee_departments` | uuid PK | HasUuid / BaseModel | v7 | KEEP |
| `employee_employees` | uuid PK | HasUuid | v7 | KEEP |
| `employee_dependents` | uuid PK | HasUuid | v7 | KEEP |
| `requests` | uuid PK | HasUuid | v7 | KEEP |
| `request_approvals` | uuid PK | HasUuid | v7 | KEEP |
| `request_dependent_snapshots` | uuid PK | HasUuid | v7 | KEEP |
| `request_members` | uuid PK | HasUuid | v7 | KEEP |
| `request_mission_details` | uuid PK = `request_id` FK | Parent request id (not new generate) | inherits parent | KEEP |
| `dormitories` … `dormitory_beds` | uuid PK | HasUuid + some services call `Uuid::uuid7()` | v7 | KEEP |
| `dormitory_manager_assignments` | uuid PK | HasUuid | v7 | KEEP |
| `dormitory_unit_manager_assignments` | uuid PK | HasUuid | v7 | KEEP |
| `dormitory_assignments` | uuid PK | HasUuid | v7 | KEEP |
| `allocations` / `allocation_items` | uuid PK | HasUuid | v7 | KEEP |
| `check_in_records` | uuid PK | HasUuid | v7 | KEEP |
| `lottery_*` (4 tables) | uuid PK | HasUuid | v7 | KEEP (module FROZEN — inventory only) |
| `voucher_*` (4 tables) | uuid PK | HasUuid | v7 | KEEP |
| `notification_logs` | uuid PK | HasUuid | v7 | KEEP |
| `audit_logs` | uuid PK | HasUuid | v7 | KEEP |
| `reporting_*` (5 tables) | uuid PK | HasUuid | v7 | KEEP (module FROZEN — inventory only) |
| `settings` | uuid PK | HasUuid | v7 | KEEP |
| `workflow_request_approval_instances` | uuid PK | HasUuid | v7 | KEEP |
| `workflow_request_approval_step_executions` | uuid PK | HasUuid | v7 | KEEP |

**Note:** Migration DDL does not embed `uuid_generate_v7()`; version is enforced in **application** `HasUuid` / explicit `Uuid::uuid7()` calls. Rows inserted outside Eloquent without supplying id would violate NOT NULL / lack default — not observed as normal path.

---

## B. HD-W1-Q2 KEEP — package / framework integer PKs (do not migrate)

| Table / family | PK | Status |
|----------------|-----|--------|
| Spatie `roles`, `permissions`, pivots (`model_has_*`, `role_has_*`) | `$table->id()` | **KEEP** exception |
| `jobs`, `job_batches`, `failed_jobs` | `$table->id()` | **KEEP** |
| `activity_log` | `$table->id()` | **KEEP** |
| `telescope_*` (`sequence` bigIncrements) | auto-increment | **KEEP** |
| `cache`, `sessions`, `migrations`, password reset tokens | framework | **KEEP** |

---

## C. Pending separate HD (do not touch in Wave 1)

| Table | PK | Note |
|-------|-----|------|
| `users` (Laravel default) | `$table->id()` bigint | Powers `App\Models\User` / Auth Foundation web login. **DO NOT TOUCH** — HD pending (HD-W1-Q2). |

---

## D. Wave 2 backlog (HD-W1-Q3 deferred work)

1. Confirm every domain factory/seeder path goes through `HasUuid` or `Uuid::uuid7()` (no `Str::uuid()` v4).
2. Optional: DB-level `uuid_generate_v7()` defaults once extension confirmed — **requires STOP-3 + Lead**.
3. Resolve `users` table PK under separate HD (not this inventory).

---

## Confidence

| Claim | Confidence |
|-------|------------|
| Domain Eloquent creates use v7 via `HasUuid` | **HIGH** |
| Migration DDL lacks v7 DB default | **HIGH** |
| Lottery/Reporting inventory rows are documentation only (FROZEN) | **HIGH** |
