# DOM-FIELD-GAP-DISCOVERY

_Date: 1405/04/31 | 2026-07-22_  
_Branch: `release/f2-employee-auth-ui-l9`_  
_Mode: Discovery only. No fixes._  
_Expected: migrations + `main` · Actual: Persistence models on this branch_

## Verdict

**Not CLEAN overall.** Fillable/casts vs migrations are largely aligned across Persistence models. Confirmed gaps: **2 entity tables without models on release**, **7 intra-module `belongsTo` relation removals vs `main`**, and **1 intentional `identity_id` fillable omission** (same pattern on `main`). No high-confidence cast-type drifts found.

---

## Findings

| Model | Missing/Drifted Item | Type | Evidence |
|-------|----------------------|------|----------|
| _(none on release)_ | table `dormitory_manager_assignments` — no Eloquent model on release; present on `main` as `app/Models/DormitoryManagerAssignment.php` | entity | mig `database/migrations/modules/dormitory/2026_07_16_000001_create_dormitory_manager_assignments_table.php:17`; release path absent (`Test-Path` false); `main` path via `git ls-tree main` |
| _(none on release)_ | table `dormitory_unit_manager_assignments` — no Eloquent model on release; present on `main` as `app/Models/DormitoryUnitManagerAssignment.php` | entity | mig `database/migrations/modules/dormitory/2026_07_16_000002_create_dormitory_unit_manager_assignments_table.php:17`; release path absent; `main` path via `git ls-tree main` |
| `EmployeeModel` | mig column `identity_id` not in `$fillable` (guarded via updating hook; `@property` documents it) | fillable | mig `…/employee/2026_06_26_000002_create_employee_employees_table.php:15`; fillable `app/Modules/Employee/Infrastructure/Persistence/Models/EmployeeModel.php:33-42`; guard `:46-48` |
| `LotteryRegistrationModel` | `program()` BelongsTo present on `main`, absent on release (`program_id` column still fillable) | relation / main-drift | `main:…/LotteryRegistrationModel.php` has `program()`; release file has `employee()`+`request()` only (`…/LotteryRegistrationModel.php`) |
| `LotteryEligibleSnapshotModel` | `program()` BelongsTo present on `main`, absent on release | relation / main-drift | `main:…/LotteryEligibleSnapshotModel.php` vs release model (no `program()`) |
| `LotteryResultModel` | `program()`, `registration()` BelongsTo present on `main`, absent on release | relation / main-drift | `main:…/LotteryResultModel.php` vs release |
| `RequestApprovalModel` | `request()` BelongsTo present on `main`, absent on release | relation / main-drift | `main:…/RequestApprovalModel.php:76` vs release (has `approver()` only from GAP-10 restore) |
| `RequestDependentSnapshotModel` | `request()` BelongsTo present on `main`, absent on release | relation / main-drift | `main:…/RequestDependentSnapshotModel.php:76` vs release (has `sourceDependent()` only) |
| `RequestMemberModel` | `request()` BelongsTo present on `main`, absent on release | relation / main-drift | `main:…/RequestMemberModel.php:64` vs release (has `employee()` only) |
| `RequestMissionDetailsModel` | `request()` BelongsTo present on `main`, absent on release | relation / main-drift | `main:…/RequestMissionDetailsModel.php:52` vs release |

### Observational (not a product gap)

| Model | Item | Type | Evidence |
|-------|------|------|----------|
| _(none)_ | table `settings` has no Eloquent model — `QueryBuilderSettingsReader` by design | entity (intentional) | mig `database/migrations/modules/system/2026_07_20_000001_create_settings_table.php:17`; reader `app/Modules/System/Infrastructure/Settings/QueryBuilderSettingsReader.php:12` |

---

## Not found (high-confidence)

| Check | Result |
|-------|--------|
| CAST_DRIFT (fillable/mig type vs `casts()`) | None confirmed |
| Unexpected `$fillable` keys with no mig column | None confirmed |
| Fillable/casts array diffs vs `main` | None confirmed (relation methods differ only) |

---

## CLEAN (fillable/casts ↔ migration)

Persistence models reviewed with no fillable/cast/mig column drift (relation main-drift may still apply — marked \* above):

Allocation\*, Audit, CheckIn, Dormitory structure + `DormitoryAssignment`, Employee `Dependent`/`Department`, Identity `UserModel`, Lottery Program (+ Eligible/Result/Registration \*), Notification, Reporting×5, Request\* (+ Mission/Approval/Member/Snapshot \*), Voucher×4, Workflow×2.

---

## Advisor

| | |
|--|--|
| **Current** | Field discovery after relation parity close |
| **Recommended** | Next Fix wave: (1) restore missing intra-module `request()`/`program()` BelongsTo from `main`; (2) Lead decide Persistence vs `app/Models` home for manager assignment entities on release |
| **Reason** | Relation removals are the largest release↔main drift; entity tables already exist in schema |
| **Risks** | Porting `app/Models/*Assignment` without module placement policy |
| **Trade-offs** | Quick `main` copy vs Persistence-layer placement under Dormitory module |

## BLOCKED

None for discovery. Implementation requires Lead authorization.
