# DOMAIN-GAP-DISCOVERY-02

_Date: 1405/05/01 | 2026-07-23_  
_Mode: Report-only. No code or schema changes._  
_Authority: `.dormSys/database-map.md` (Expected schema/FK) vs Persistence/`App\Models` Eloquent + module migrations (Actual)._

## Method

1. Enumerated product tables and FK notes from `.dormSys/database-map.md`.
2. Inventories Eloquent `$table` models under `app/Modules/*/Infrastructure/Persistence/Models/` and `app/Models/Dormitory/`.
3. Compared physical FK columns → `belongsTo(..., '<fk_col>')`; soft UUID columns → relation presence and PHPDoc accuracy; Domain entities sampled where map columns lack Domain properties.
4. Decision Gate items listed as **decision-gated / BLOCKED** only (no resolution).

## Headline

| Check | Result |
|-------|--------|
| Physical FK → Eloquent `belongsTo` (product tables) | **CLEAN** — every map/mig physical FK on a modeled table has a matching relation |
| Manager assignment tables | **CLEAN** — models at `app/Models/Dormitory/{DormitoryManagerAssignment,DormitoryUnitManagerAssignment}.php` with `user()`/`dormitory()`/`room()` |
| Doc drift (`physical FK present` vs map/mig) | **CLEAN for remaining claims** — verified true for Request/Lottery/DormitoryAssignment methods that still use that phrase; `AllocationItemModel::bed()` and `RequestModel::assignedStage1Approver` already corrected |
| Remaining incompleteness | **5 gaps** — **0 fixable** without Lead; **5 decision-gated / OPEN DECISION** |

---

## Remaining gaps

| Entity / surface | Gap type | Expected evidence | Actual evidence | Suggested wave size |
|------------------|----------|-------------------|-----------------|---------------------|
| `AllocationModel` (+ consumer) | decision-gated | Map `allocations.source_lottery_result_id` uuid nullable (`.dormSys/database-map.md` §allocations); Domain `Allocation::$sourceLotteryResultId` (`app/Modules/Allocation/Domain/Models/Allocation.php`); open-decisions **A2** OPEN (`ProposedAllocationConsumer.php:44`) | Persistence: fillable `source_lottery_result_id` present; **no** `sourceLotteryResult()` BelongsTo (`AllocationModel.php`). Consumer: `sourceLotteryResultId: (string) $winner['registration_id']` (`ProposedAllocationConsumer.php:44`) | **L** — Allocation Decision Closure (Lead A2) |
| `AllocationModel` header `bed` | decision-gated (OMIT honored) | Map `allocations.bed_id` uuid, **no FK** (`.dormSys/database-map.md` §allocations); progress-log / matrix **A3 OMIT** (`DOM-GAP-09B-CLOSE`) | Soft `bed_id` in fillable; **no** `bed()` method (`AllocationModel.php`) — matches OMIT | **—** (no fix wave; residual of CLOSED OMIT) |
| `allocation_items.bed_id` | OPEN DECISION (schema) | Map Notes `—` (no FK) for `bed_id`; create mig FK **only** on `allocation_id` (`database/migrations/modules/allocation/2026_07_01_000002_create_allocation_items_table.php:16–27`) | Eloquent `bed()` present; PHPDoc Eloquent-only / no physical FK (`AllocationItemModel.php:48–56`) — **model docs aligned**; physical FK still absent | **M** — Critical DB Remediation (Lead: add FK mig or keep soft UUID) |
| `App\Modules\Dormitory\Domain\Entities\Bed` | missing field / OPEN DECISION | Map `dormitory_beds.last_signal_reference_id` uuid nullable (`.dormSys/database-map.md` §dormitory_beds / EVOLVED mig `2026_07_12_000001_…`) | `BedModel` fillable includes `last_signal_reference_id` (`BedModel.php:31–36`); Domain `Bed` has **no** signal property (`Domain/Entities/Bed.php`); Application reads via `AllocationBedPhysicalStateRepository` | **S** — only if Lead wants Domain ownership; else document intentional Infra/App ownership |
| Cross-module Eloquent `belongsTo` batch | OPEN DECISION (architecture) | `AGENTS.md`: “No cross-module Eloquent queries… Cross-module foreign keys are prohibited — store UUIDs as value references.” | Widespread Persistence `belongsTo` to other modules (e.g. `RequestModel::employee/dormitory`, `LotteryRegistrationModel::employee/request`, `VoucherModel::*`, `AllocationModel::employee/sourceRequest`, `AllocationItemModel::bed` → Dormitory) | **L** — ADR / Lead ratification of exception vs unwind |

---

## BLOCKED (Decision Gate — list only)

| ID | Statement | Evidence |
|----|-----------|----------|
| **A2** | Do not implement `sourceLotteryResult()` / do not “fix” `registration_id`→`source_lottery_result_id` wiring without Lead authorization | `.dormSys/open-decisions.md` A2 **OPEN**; `ProposedAllocationConsumer.php:44`; `DOM-GAP-10-CLOSE` |

---

## Intentional non-gaps (not counted)

| Surface | Why not a gap | Evidence |
|---------|---------------|----------|
| `EmployeeModel.identity_id` omitted from `$fillable` | Immutable after assign; `@property` + updating guard | Mig/map column exists; `EmployeeModel.php:33–50` |
| `settings` without Eloquent model | QueryBuilder-only by design | Map §settings; `QueryBuilderSettingsReader` (System) |
| Spatie `activity_log` / permission pivots | Package-owned; not DormSys Persistence domain models | Map Spatie / Framework sections |
| `AllocationItemModel::bed()` docs | Already aligned (ALLOC-DOC-ALIGN-01) | Map+mig no FK; PHPDoc Eloquent-only |
| `RequestModel::assignedStage1Approver` docs | Already aligned (DOMAIN-COMPLETENESS-SWEEP) | Map: FK dropped; PHPDoc Eloquent-only |
| Manager assignment entities | Models + FK relations present under `App\Models\Dormitory` | MANAGER-ASSIGN-CREATE **CLOSED**; migs `2026_07_16_000001/000002` |

---

## Physical FK coverage (CLEAN — citation sample)

| Table.column (Expected FK) | Actual relation |
|----------------------------|-----------------|
| `employee_employees.department_id` | `EmployeeModel::department()` |
| `employee_departments.parent_id` / `manager_id` | `DepartmentModel::parent()` / `manager()` |
| `employee_dependents.employee_id` | `DependentModel::employee()` |
| `request_* .request_id` (approvals, members, mission, dependent snapshots) | matching `request()` on each Request Persistence model |
| Dormitory hierarchy `dormitory_id`/`building_id`/`floor_id`/`room_id` | `BuildingModel`/`FloorModel`/`RoomModel`/`BedModel` |
| Assignment tables `user_id`/`dormitory_id`/`room_id` | `DormitoryAssignment` + `App\Models\Dormitory\*Assignment` |
| Lottery `program_id` / `registration_id` | `LotteryRegistrationModel`/`LotteryResultModel`/`LotteryEligibleSnapshotModel` |
| `allocation_items.allocation_id` | `AllocationItemModel::allocation()` |
| `workflow_request_approval_step_executions.workflow_instance_id` | `RequestApprovalWorkflowStepExecutionModel::instance()` |

---

## Counts for progress-log

| Metric | Value |
|--------|------:|
| Gaps found | **5** |
| Fixable without Lead | **0** |
| Decision-gated / OPEN DECISION | **5** |

(All five rows in “Remaining gaps” require Lead: A2 BLOCKED, A3 OMIT residual, schema FK open, Domain Bed ownership open, cross-module ADR open.)

---

## Advisor

| | |
|--|--|
| **Current approach** | Report incompleteness; leave Decision Gate and architecture/schema opens untouched |
| **Recommended approach** | Next: Lead closes **A2** (Allocation Decision Closure); optional parallel **ADR** for cross-module Eloquent; optional Critical DB Remediation for `allocation_items.bed_id` FK |
| **Reason** | Zero safe model-only fixes remain after COMPLETENESS + ALLOC-DOC-ALIGN |
| **Risks** | Treating soft `bed_id` as “broken” without Lead invents schema policy |
| **Trade-offs** | Fast A2-only path vs larger architecture cleanup |
