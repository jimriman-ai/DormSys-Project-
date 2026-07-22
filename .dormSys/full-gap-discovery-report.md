# DormSys Full Gap Discovery Report

> **Wave:** FULL-GAP-DISCOVERY-01  
> **Mode:** READ-ONLY DISCOVERY ONLY  
> **Branch (observed):** `release/f2-employee-auth-ui-l9`  
> **Generated:** 1405/04/31 | 2026-07-22  
> **Authority:** Disk + migrations are authoritative. Progress-log claims verified against disk; superseded claims called out under GAP-G.  
> **Scope:** `app/**`, `database/migrations/**`, `.dormSys/**`, `docs/**`  
> **Excluded:** `vendor/**`, `node_modules/**`, `storage/**`, `tests/**`, git operations (read-only branch contrast noted only where evidence required)

---

## Executive Summary

- Total gaps found: **34**
- Critical: **5**
- High: **10**
- Medium: **13**
- Low: **6**

**Headline verdict:** Schema↔fillable/casts for Persistence models is largely healthy. Material defects cluster in (1) **false “physical FK present” claims** vs final migrations, (2) **two assignment tables without models**, (3) **intra-module `belongsTo` missing** despite physical FKs, (4) **semantic ID misuse** in lottery→allocation emit, and (5) **documentation ledger drift** (missing ratified maps; stale COMPLETE lines).

---

## GAP-A Database Schema

| Item | Expected | Actual | Evidence | Severity |
|------|----------|--------|----------|----------|
| `allocation_items.bed_id` physical FK | Either DB FK exists, or model/docs treat column as AP-04 soft UUID | Column is plain `uuid`; only `allocation_id` has FK; model comment asserts “physical FK present” | `database/migrations/modules/allocation/2026_07_01_000002_create_allocation_items_table.php:16-27`; `app/Modules/Allocation/Infrastructure/Persistence/Models/AllocationItemModel.php:49`; progress-log MIG-FIX-02 at `.dormSys/progress-log.md:12` (claim not on disk) | Critical |
| `requests.assigned_stage1_approver_identity_id` physical FK | Final schema matches model authority comment | Column retained; FK **dropped** in `up()`; model still says “physical FK present”; progress-log claims MIG-FIX-01 revive (absent on this branch) | `database/migrations/modules/request/2026_07_20_000001_drop_assigned_stage1_approver_identity_fk_from_requests_table.php:18-19`; `app/Modules/Request/Infrastructure/Persistence/Models/RequestModel.php:90`; `.dormSys/progress-log.md:11` | Critical |
| Cross-module FK revive vs ADR-006 | Cross-module FK prohibited (AP-04) | Progress-log asserts revive/add of cross-module FKs; this branch has drop-only / no bed FK — claim contradicts both disk and ADR | `.specify/docs/ADR/006-module-table-naming-convention.md:26`; `.dormSys/progress-log.md:11-12` | Critical |
| Lottery FKs `onDelete` policy | Explicit cascade/restrict per standards | `program_id` / `registration_id` FKs with no `onDelete` clause (DB default) | `database/migrations/modules/lottery/2026_06_30_000003_create_lottery_results_table.php:25-31` | Low |
| Tables without ownership clarity in Persistence | Product tables mapped to module Persistence models or intentional non-Eloquent note | `dormitory_manager_assignments` / `dormitory_unit_manager_assignments` have migrations + FKs but no Persistence models (see GAP-C); `settings` intentionally QueryBuilder-only | Manager migs `…/2026_07_16_000001…:17` / `…000002…:17`; settings `…/system/2026_07_20_000001…:17` + `QueryBuilderSettingsReader.php:12` | High (assignments) / N/A intentional (settings) |

---

## GAP-B Model Fields

| Item | Expected | Actual | Evidence | Severity |
|------|----------|--------|----------|----------|
| `EmployeeModel.identity_id` mass-assign / cast awareness | Column present; create-time assignment path clear | Column in migration (`unique`); not in `$fillable`; immutability on update only; no cast; no `identity()` relation | `database/migrations/modules/employee/2026_06_26_000002_create_employee_employees_table.php:15`; `EmployeeModel.php:33-42,44-50` | Medium |
| `AllocationModel.date_range` cast | Typed handling for PostgreSQL `daterange` | In `$fillable`; no cast; `@property string` | `AllocationModel.php:36-57`; allocations create migration `date_range` column | Medium |
| `VoucherModel.stay_period` cast | Typed handling for `daterange` | In `$fillable`; no cast; `@property string` | `VoucherModel.php:52,62-71`; `database/migrations/modules/voucher/2026_07_01_000003_create_vouchers_table.php:40` | Medium |
| `VoucherIssuanceTriggerModel.stay_period` cast | Same as vouchers | No daterange cast (same pattern) | voucher trigger mig ADD COLUMN `stay_period`; trigger model casts omit it | Medium |
| `LotteryResultModel.rank` cast | Integer cast matching `unsignedInteger` | Fillable only; no `'rank' => 'integer'` | `LotteryResultModel.php:24-39`; mig `:17` | Low |
| `LotteryProgramModel.capacity` cast | Integer cast | No integer cast in casts array | `LotteryProgramModel` fillable/casts vs lottery programs mig | Low |
| Reporting aggregate integer fields | Integer casts for count columns | `event_count` / distinct_* fillable without integer casts | `ActorActivitySummaryModel`, `AuditWindowAggregateModel` | Low |
| SoftDeletes / UUID baseline | BaseModel children use HasUuid + SoftDeletes | Present on `BaseModel`; AuditLog / DormitoryAssignment / Reporting / Request children correctly specialize | `app/Support/Models/BaseModel.php:19-22,33-49` | Clean (baseline) |
| HasAuditActors | Either implemented or never claimed COMPLETE | Trait absent; BaseModel has UUID string casts for audit actor columns only; progress-log later CLOSED correctly | `BaseModel.php:40-49`; Glob `*HasAuditActors*` = 0; `.dormSys/progress-log.md:13` vs `:20-23` | Medium (doc; see GAP-G) |

---

## GAP-C Entity Completeness

| Item | Expected | Actual | Evidence | Severity |
|------|----------|--------|----------|----------|
| Persistence model for `dormitory_manager_assignments` | Module Persistence model (sibling: `DormitoryAssignment`) | Migration exists; **no model file** on this branch; progress-log claims created | `database/migrations/modules/dormitory/2026_07_16_000001_create_dormitory_manager_assignments_table.php:17-27`; `Test-Path` Persistence path false; `.dormSys/progress-log.md:8` | High |
| Persistence model for `dormitory_unit_manager_assignments` | Module Persistence model | Migration exists; **no model file** on this branch | `database/migrations/modules/dormitory/2026_07_16_000002_create_dormitory_unit_manager_assignments_table.php:17`; path absent | High |
| `settings` Eloquent model | Explicit ownership decision | No Eloquent by design (`QueryBuilderSettingsReader`) | `database/migrations/modules/system/2026_07_20_000001_create_settings_table.php:17`; `app/Modules/System/Infrastructure/Settings/QueryBuilderSettingsReader.php:12` | Decision / intentional |
| Models without tables | Every Persistence `$table` has create migration | **None observed** among 35 Persistence models | Inventory: Persistence Models ↔ module migrations | Clean |
| Dual User surfaces | Intentional dual-guard (DGAP-10 CLOSED) | Scaffold `app/Models/User` → `users` + `UserModel` → `identity_users` coexist | `docs/governance/open-decisions.md` DGAP-10; identity users mig + `0001_01_01_000000_create_users_table.php` | Clean (by decision) |
| Framework/package tables without domain models | Tooling-only / package ownership | `cache*`, `jobs*`, `activity_log`, Telescope, Spatie permission tables — no module Persistence models expected | Root migrations + `modules/identity/2026_06_26_000002_create_permission_tables.php` | Clean (out of product Persistence) |

---

## GAP-D Relations

| Item | Expected | Actual | Evidence | Severity |
|------|----------|--------|----------|----------|
| `LotteryEligibleSnapshotModel.program()` | `belongsTo` for physical FK `program_id` | No relation methods | FK mig `…/lottery/2026_06_30_000004_create_lottery_eligible_snapshots_table.php`; model `LotteryEligibleSnapshotModel.php` (no BelongsTo) | Medium |
| `LotteryRegistrationModel.program()` | `belongsTo` for physical FK `program_id` | Has `employee()` + `request()` only | `LotteryRegistrationModel.php:47-65`; registrations mig FK on `program_id` | Medium |
| `LotteryResultModel.program()` | `belongsTo` for physical FK | No relations | `LotteryResultModel.php:17-40`; `…/lottery_results…:25-27` | Medium |
| `LotteryResultModel.registration()` | `belongsTo` for physical FK | No relations | Same model; mig `:29-31` | Medium |
| `RequestApprovalModel.request()` | `belongsTo` for cascade FK `request_id` | Has `approver()` only | `RequestApprovalModel.php:73-81`; `…/request_approvals…:23-26` | Medium |
| `RequestDependentSnapshotModel.request()` | `belongsTo` for cascade FK | Has cross-ref only (no `request()`) | Dependent snapshot model vs `…/request_dependent_snapshots` FK | Medium |
| `RequestMemberModel.request()` | `belongsTo` for cascade FK | Has `employee()` only | RequestMemberModel vs members mig FK | Medium |
| `RequestMissionDetailsModel.request()` | `belongsTo` (PK = `request_id` FK) | No relations | `RequestMissionDetailsModel.php:16-46`; mission details mig FK | Medium |
| `AllocationItemModel.bed()` | Relation OK if soft-ref or FK documented correctly | Relation exists; **FK claim wrong** (see GAP-A) | `AllocationItemModel.php:48-56` | Critical (authority) |
| `RequestModel.assignedStage1Approver()` | Soft UUID relation after WP-REQ-01 drop | Relation exists; **FK claim wrong** | `RequestModel.php:90-97` | Critical (authority) |
| Cross-module Eloquent `belongsTo` batch | AGENTS.md: no cross-module Eloquent; ports/services for reads | Widespread Persistence `belongsTo` across modules (Allocation→Dormitory/Employee/Request, Voucher→…, etc.); progress-log claims Lead-ratified DOM-GAP-10 | e.g. `AllocationItemModel.php:7,53-55`; `RequestModel.php` employee/dormitory; `.dormSys/progress-log.md:31-32`; ADR-006 `:26` | High |
| A3 header `AllocationModel.bed()` | OMIT per Lead | Absent (honored) | `.dormSys/progress-log.md:30`; `AllocationModel.php` has employee/sourceRequest/items only | Clean (decision honored) |

---

## GAP-E Data Integrity

| Item | Expected | Actual | Evidence | Severity |
|------|----------|--------|----------|----------|
| Lottery winner → allocation `bedId` | `bedId` must be a bed UUID | `dormitory_id` passed as `bedId` | `app/Modules/Allocation/Application/Services/ProposedAllocationConsumer.php:39-40` | Critical |
| Lottery winner → `sourceLotteryResultId` | Result id (or decided mapping) | `registration_id` passed as `sourceLotteryResultId` (A2 OPEN) | `ProposedAllocationConsumer.php:44`; `.dormSys/open-decisions.md:42`; `.dormSys/progress-log.md:33` | High |
| `employee_employees.identity_id` soft link | Clear ownership + orphan policy | Unique UUID column; no physical FK to `identity_users`; no Eloquent relation; DGAP-07 selected UUID-only | Employee mig `:15`; `EmployeeModel` no `identity()`; `docs/governance/open-decisions.md` DGAP-07 | Medium (policy-aligned residual) |
| `allocations.source_lottery_result_id` | Relation or explicit defer | Fillable; no `sourceLotteryResult()` (blocked by A2) | `AllocationModel.php:42-43,60-78`; open decision A2 | High (blocked) |
| `allocations.bed_id` header soft ref | OMIT Eloquent `bed()`; items authoritative | Soft UUID fillable; no header relation (OMIT honored) | `AllocationModel.php:38`; DOM-GAP-09B-CLOSE | Clean (decision) / orphan risk residual Low |
| `dormitory_beds.last_signal_reference_id` | Ownership / relation clarity | Soft UUID fillable; no relation | Bed alter migration + `BedModel` fillable | Low |
| Audit actor columns UUID-only | No Eloquent actors (DOM-GAP-06 CLOSED) | Cast as string on BaseModel; no relations | `BaseModel.php:46-48`; progress-log `:20-23` | Clean (policy) |
| Dangerous nullable cross-module refs | AP-04 soft UUID accepted | Many soft refs intentional; integrity relies on application invariants | Cross-module columns without FK (requests.employee_id, check_in.allocation_id, etc.) | Info / policy |

---

## GAP-F Architecture

| Item | Expected | Actual | Evidence | Severity |
|------|----------|--------|----------|----------|
| Cross-module Eloquent forbidden | `AGENTS.md` / ADR-006: UUID refs + Application services; no cross-module Eloquent | Persistence models import foreign-module models and define `belongsTo` | `AGENTS.md` architecture rule 2; ADR-006 `:26`; e.g. `AllocationItemModel.php:7`; `LotteryRegistrationModel.php:7-8`; Voucher models | High |
| DOM-GAP-10 “Lead-ratified” pattern vs canonical gate | Cross-module relation exception recorded in canonical Decision Gate | Claim lives in `.dormSys/progress-log.md`; **A2/DOM-GAP-10 not mirrored** in `docs/governance/open-decisions.md` | `.dormSys/progress-log.md:31-33`; `docs/governance/open-decisions.md` (no A2 row observed in Decision Gate table header scope) | High |
| ADR-003 vs UUID-only / DOM-GAP-06 | Aligned migration template vs SoftDeletes audit actors | ADR-003 still templates FK audit actors → `users`; current BaseModel + DOM-GAP-06 = UUID-only, no HasAuditActors | `.specify/docs/ADR/003-migration-template-standards.md` (FK audit actors); `.dormSys/progress-log.md:20-23` | Medium |
| Domain → Infrastructure import | Domain never imports Infrastructure | No `use App\Modules\*\Infrastructure` under Domain (scan) | Grep Domain/** | Clean |
| Module ownership of assignment entities | Persistence under `app/Modules/Dormitory/Infrastructure/...` | On this branch: models missing; (contrast) `main` places under `App\Models` — wrong layer vs AGENTS.md | Progress-log `:8`; release paths absent; AGENTS.md module layout | High |
| Application semantic misuse (lottery emit) | Ports preserve entity identity semantics | `ProposedAllocationConsumer` swaps registration→result and dormitory→bed | `ProposedAllocationConsumer.php:39-44` | Critical |

---

## GAP-G Documentation Drift

| Item | Expected | Actual | Evidence | Severity |
|------|----------|--------|----------|----------|
| Ratified maps present after MAP waves | `.dormSys/database-map.md`, `spec-catalog.md`, `domain-gaps.md`, `dom-gap-07/09/09b` cited by log/ledger | **Absent** on this branch (directory contains progress-log, open-decisions, audit-status-matrix, dom-field-gap-discovery only) | `.dormSys/progress-log.md:1-7,26-29`; `.dormSys/open-decisions.md:36-37`; `Get-ChildItem .dormSys` | Critical |
| DR-REG-01/02 CLOSED → write into `database-map.md` | CLOSED backed by artifact on active branch | Ledger CLOSED; **file missing** | `.dormSys/open-decisions.md:36-37` | High |
| Triple open-decision ledgers | One Decision Gate boundary (DR-REG-04) | Three ledgers; DR-REG-04 still OPEN | `docs/governance/open-decisions.md:6-7`; `.specify/governance/open-decisions.md`; `.dormSys/open-decisions.md:39` | High |
| DOM-FIX-01 COMPLETE claim | Models exist under module Persistence | Claim false on disk | `.dormSys/progress-log.md:8` | High |
| MIG-FIX-01/02 COMPLETE claims | Matching migrations on branch | Claims present; restore/add-FK migrations **absent** here | `.dormSys/progress-log.md:11-12` | Critical |
| HasAuditActors COMPLETE/RATIFIED then CLOSED | Single authoritative status | Stale COMPLETE/RATIFIED lines remain; later CLOSED matches disk | `.dormSys/progress-log.md:10,13,20-23` | Medium |
| Relation COMPLETE GAP-03/04/05 | Disk matches matrix | Verified present on disk (employee/bed/approver/user restorations) | `.dormSys/audit-status-matrix.md:10-12`; model methods present | Clean (current disk) |
| Audit matrix log-token-only | Status reflects disk truth or flags unverified claims | Matrix echoes log tokens; omits MIG-FIX / DOM-FIX-01 (no COMPLETE token) so false claims stay invisible | `.dormSys/audit-status-matrix.md:3-4,10-19` | Medium |
| `project-state.md` session depth | Reflects material drift | Narrow (matrix/A2 only); under-reports missing maps / MIG-FIX / assignment models | `docs/governance/project-state.md:12-63` | Medium |
| A2 only in `.dormSys` ledger | Canonical Decision Gate or explicit subset policy (DR-REG-04) | A2 registered in `.dormSys/open-decisions.md:42` only | Dual/triple ledger split | Medium |

---

## Clean Areas

Explicitly no material gap detected (within this wave’s evidence rules):

- **Dormitory structure Persistence:** `DormitoryModel`, `BuildingModel`, `FloorModel`, `RoomModel`, `BedModel` (aside from soft `last_signal_reference_id` INFO)
- **`DormitoryAssignment`:** FKs + `user()` / `dormitory()` align with migration
- **Employee structure:** `DepartmentModel`, `DependentModel` fillable/FK/relations vs migrations
- **`UserModel` ↔ `identity_users`**
- **`AuditLogModel` ↔ `audit_logs`** (append-only, HasUuid, no SoftDeletes)
- **`NotificationLogModel`** fillable/casts vs columns (soft employee ref policy-aligned)
- **Reporting Persistence ×5** column sets vs migrations (aside from LOW integer casts)
- **`CheckInRecordModel`** structure vs migration (soft refs per ratified AP-04 / DOM-GAP-01)
- **Workflow step ↔ instance** physical FK + `instance()` relation
- **Voucher intra-module relation set** present (aside from soft cross-module + daterange cast quality)
- **A3 OMIT** of `AllocationModel::bed()` honored
- **Domain layer** does not import Infrastructure (scan clean)
- **`settings` non-Eloquent** intentional and documented in reader
- **Dual User (`App\Models\User` vs `UserModel`)** CLOSED NOT-A-GAP by design (DGAP-10)
- **DOM-GAP-03/04/05 relation restorations** currently match disk
- **DOM-GAP-06 UUID-only audit actors** matches disk (HasAuditActors absent)

---

## Decision Required

### [DECISION-ID] DR-GAP-ASSIGN-01

**Question:** Create Persistence models for `dormitory_manager_assignments` and `dormitory_unit_manager_assignments` under `app/Modules/Dormitory/Infrastructure/Persistence/Models/`, or formally defer and retract progress-log DOM-FIX-01?

**Evidence:** Migrations `…/2026_07_16_000001…:17` and `…000002…:17`; models absent; `.dormSys/progress-log.md:8`.

**Possible options:**
- A) Create module Persistence models + relations (match `DormitoryAssignment` pattern)
- B) Defer with explicit OPEN status; correct progress-log
- C) Port from `main` `App\Models\*` only after Lead chooses ownership path (reject wrong layer)

---

### [DECISION-ID] DR-GAP-FKDOC-01

**Question:** For `allocation_items.bed_id` and `requests.assigned_stage1_approver_identity_id`, is the authority soft UUID (AP-04 / WP-REQ-01) or physical FK (MIG-FIX claims)?

**Evidence:** Allocation items mig `:16-27`; request FK drop `:18-19`; model comments `AllocationItemModel.php:49`, `RequestModel.php:90`; ADR-006 `:26`.

**Possible options:**
- A) Keep soft UUID; fix PHPDoc/comments; retract MIG-FIX claims on this branch
- B) Re-add physical FKs with explicit Lead exception to ADR-006 (cross-module FK)
- C) Split: bed_id soft (cross-module); stage1 soft (already dropped) — document only

---

### [DECISION-ID] DR-GAP-REL-01

**Question:** Must every intra-module physical FK have a matching Eloquent `belongsTo`, or is FK-only (no nav) an accepted Persistence standard?

**Evidence:** Eight missing relations (lottery program/registration; request children `request()`); FKs present in migrations.

**Possible options:**
- A) Restore all intra-module `belongsTo` (parity with `main`)
- B) Document “FK without Eloquent nav” as standard; close as NOT-A-GAP
- C) Restore only high-traffic paths (request children); leave lottery until needed

---

### [DECISION-ID] A2 (existing)

**Question:** LotteryResultModel / `registration_id` drift — may `sourceLotteryResult()` / emit path use `registration_id` as `source_lottery_result_id`, or must it use result id?

**Evidence:** `ProposedAllocationConsumer.php:44`; `.dormSys/open-decisions.md:42`; `.dormSys/progress-log.md:33`.

**Possible options:**
- A) Rename/remap emit to lottery **result** id; implement `sourceLotteryResult()`
- B) Rename column/semantics to `source_lottery_registration_id`; update contracts
- C) Park emit path; block lottery-sourced allocation until fixed

---

### [DECISION-ID] DR-GAP-BED-EMIT-01

**Question:** Is `ProposedAllocationConsumer` passing `dormitory_id` as `bedId` intentional temporary stub, or a defect requiring freeze?

**Evidence:** `ProposedAllocationConsumer.php:39-40`.

**Possible options:**
- A) Defect — require real `bed_id` in frozen winner payload
- B) Intentional stub — document + gate behind feature flag / fail-closed
- C) Resolve bed via dormitory assignment service before CreateAllocation

---

### [DECISION-ID] DR-REG-04 (existing)

**Question:** Relationship between `.dormSys/open-decisions.md` and `docs/governance/open-decisions.md` (and deprecated `.specify/governance/…`)?

**Evidence:** `.dormSys/open-decisions.md:39`; `docs/governance/open-decisions.md:6-7`.

**Possible options:**
- A) Supersede — `.dormSys` becomes canonical for Protocol waves
- B) Mirror — sync both ways on each wave
- C) Subset — `.dormSys` Protocol-only; promote A2 etc. into docs ledger
- D) Independent — explicit non-sync (accept drift risk)

---

### [DECISION-ID] DR-GAP-MAP-01

**Question:** Restore missing ratified `.dormSys` maps (`database-map`, `spec-catalog`, `domain-gaps`, dom-gap reports) onto this branch from `main`, or re-generate under a new wave?

**Evidence:** Files absent; progress-log and CLOSED DR-REG rows still cite them.

**Possible options:**
- A) Cherry-pick / restore artifacts from `main`
- B) Re-run MAP discovery on this branch
- C) Mark maps out-of-scope for release branch; amend CLOSED DR-REG notes

---

### [DECISION-ID] DR-GAP-XMOD-01

**Question:** Are cross-module Eloquent `belongsTo` relations an authorized exception to AGENTS.md / ADR-006, or debt to unwind toward ports?

**Evidence:** Cross-module imports in Persistence models; `.dormSys/progress-log.md:31-32`; ADR-006 `:26`.

**Possible options:**
- A) Ratify exception in canonical `docs/governance/open-decisions.md`
- B) Keep Eloquent nav; treat as temporary debt with unwind plan
- C) Remove cross-module Eloquent; replace with Application read ports

---

## Recommendation

### R1 — Authority comments vs schema

| | |
|--|--|
| **Current** | Model PHPDocs claim physical FKs that final migrations do not have; progress-log claims MIG-FIX revive/add not on this branch. |
| **Recommended** | Treat disk migrations as SoT; correct PHPDocs to AP-04 soft UUID (no code schema change in discovery). Retract or re-authorize MIG-FIX claims via Lead. |
| **Reason** | False authority in code misleads implementers into assuming DB integrity that does not exist. |
| **Risk** | If Lead intended physical FKs, soft-UUID docs would need reverse correction later. |
| **Trade-off** | Doc hygiene now vs waiting for ADR-006 exception decision. |

### R2 — Assignment entity models

| | |
|--|--|
| **Current** | Tables + FKs exist; Persistence models missing; progress-log claims creation. |
| **Recommended** | Authorize Persistence model wave under Dormitory module (not `App\Models`). |
| **Reason** | Authorization/assignment reads cannot use typed Eloquent without models; sibling pattern already exists. |
| **Risk** | Premature model without use-case may rot. |
| **Trade-off** | Early Persistence vs QueryBuilder/repository-only until F2/auth needs them. |

### R3 — Lottery emit semantic freeze

| | |
|--|--|
| **Current** | `dormitory_id`→`bedId` and `registration_id`→`sourceLotteryResultId`. |
| **Recommended** | Freeze lottery-sourced allocation emit until A2 + bed identity decided; do not implement `sourceLotteryResult()` on bad semantics. |
| **Reason** | Orphan/wrong-target allocations are integrity Critical. |
| **Risk** | Blocks lottery happy-path demos. |
| **Trade-off** | Safety vs feature velocity. |

### R4 — Documentation ledger hygiene

| | |
|--|--|
| **Current** | Missing maps; triple ledgers; COMPLETE lines without disk evidence. |
| **Recommended** | Close DR-REG-04; restore or regenerate maps; require disk gate for COMPLETE rows in audit matrix. |
| **Reason** | Protocol waves cannot rely on absent SoT artifacts. |
| **Risk** | Branch merge conflicts with `main` maps. |
| **Trade-off** | Restore-from-main speed vs regenerate freshness. |

### R5 — Intra-module relation parity

| | |
|--|--|
| **Current** | Eight physical FKs lack `belongsTo` on owning models. |
| **Recommended** | Lead pick DR-GAP-REL-01; if A, restore `program()`/`registration()`/`request()` only (no schema). |
| **Reason** | Lowest-risk consistency win; matches sibling patterns and `main`. |
| **Risk** | Cross-module relations remain separate High architecture debt. |
| **Trade-off** | Eloquent convenience vs keeping Persistence thinner. |

---

## Final Validation

Confirm:

- Files changed: **NONE except discovery report** (`.dormSys/full-gap-discovery-report.md`) — plus mandatory session snapshot update to `docs/governance/project-state.md` (governance rule; non-authority)
- Migration changed: **NO**
- Schema changed: **NO**
- Model changed: **NO**

---

## Inventory Appendix (observed)

| Inventory | Count |
|-----------|------:|
| Migration PHP files (root + modules) | 50 |
| Persistence models (`Infrastructure/Persistence/Models`) | 35 |
| Scaffold `app/Models/User.php` | 1 |
| `.dormSys` markdown artifacts on disk | 4 (+ this report) |
| Open Protocol decisions in `.dormSys` (material) | A2, DR-REG-03/04/05 |

---

END WAVE FULL-GAP-DISCOVERY-01
