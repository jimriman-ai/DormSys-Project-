# DECISION-PACKAGE-01

_Date: 1405/05/01 | 2026-07-23_  
_Wave: DECISION-PACKAGE-01 (report-only, doc-only)_  
_Source gaps: `.dormSys/domain-gap-report-02.md` (re-verified against current repo)_  
_Rule: Options for Lead. **No option selected.** Recommendations are advisory only._

## Re-verification summary

| Gap ID (this package) | Report-02 row | Status after re-verify |
|-----------------------|---------------|------------------------|
| **A2** | AllocationModel + consumer | **STILL OPEN** — evidence below |
| **A3-RESIDUAL** | AllocationModel header `bed()` | **STILL OMIT-honored** — already decided; packaged for reaffirm/reopen only |
| **DP-ALLOC-ITEM-BED-FK** | `allocation_items.bed_id` | **STILL OPEN** — soft UUID; Eloquent docs aligned |
| **DP-BED-SIGNAL-OWNERSHIP** | Domain `Bed` vs `last_signal_reference_id` | **STILL OPEN** |
| **DP-XMOD-BELONGS** | Cross-module Eloquent vs `AGENTS.md` | **STILL OPEN** |

None marked `RESOLVED-BY-DRIFT`.

---

## 1. A2 — Lottery result id vs registration_id (Decision Gate)

### Context

Column `allocations.source_lottery_result_id` and Domain `Allocation::$sourceLotteryResultId` imply a lottery **result** id. Lottery intake consumer currently passes winner **`registration_id`**. Persistence has no `sourceLotteryResult()` relation. Implementation is Decision-Gate blocked.

### Evidence (Expected vs Actual)

| | Citation |
|--|----------|
| **Expected (schema)** | `.dormSys/database-map.md` §allocations: `source_lottery_result_id` uuid YES, Notes `—` (no FK) |
| **Expected (domain)** | `app/Modules/Allocation/Domain/Models/Allocation.php` — constructor/`sourceLotteryResultId` property |
| **Expected (ledger)** | `.dormSys/open-decisions.md` **A2** Status **OPEN**; `.dormSys/progress-log.md` `DOM-GAP-10-CLOSE` OPEN DECISION line |
| **Actual (persistence)** | `AllocationModel.php`: fillable `source_lottery_result_id`; methods `employee()`, `sourceRequest()`, `items()` only — **no** `sourceLotteryResult()` / **no** `bed()` |
| **Actual (consumer)** | `ProposedAllocationConsumer.php:44`: `sourceLotteryResultId: (string) $winner['registration_id']` |
| **Related observed (same file, not a separate gap ID)** | `ProposedAllocationConsumer.php:40`: `bedId: (string) $winner['dormitory_id']` — payload key is `dormitory_id` while parameter is `bedId` (Lead may treat under A2 or separate) |

### Options

| Option | Description | Risks | Trade-offs |
|--------|-------------|-------|------------|
| **A** | Treat stored UUID as **lottery_results.id**; fix consumer to pass result id; add Eloquent `sourceLotteryResult()` (soft UUID / AP-04) | Requires correct winner payload shape; data already written with registration ids may be wrong | Aligns name↔semantics; needs authorized fix wave + possible data repair |
| **B** | Rename/repurpose semantics to **registration_id** (column rename or documented alias); relation → `LotteryRegistrationModel` | Migration/rename cost; breaks name `source_lottery_result_id` | Matches current consumer behavior; schema rename is heavy |
| **C** | Keep soft UUID; **no** Eloquent relation; document that value is registration id until redesign | Ongoing naming confusion; audit/reporting ambiguity | Minimal code; debt remains |
| **D** | Defer indefinitely (park A2) | Blocks Allocation Decision Closure and relation completeness | Buys time; no integrity improvement |

### Impact if left open

No authorized fix of consumer or `sourceLotteryResult()`; matrix `DOM-GAP-10-CLOSE (A2)` stays OPEN; lottery→allocation provenance remains ambiguous.

### Recommended default _(advisory only)_

**Option A** — name and Domain property already say “result”; consumer should match after Lead authorizes payload contract. Lead must still choose.

---

## 2. A3-RESIDUAL — Allocation header `bed()` (already OMIT)

### Context

Lead previously **OMIT**ted `AllocationModel::bed()`; `AllocationItemModel::bed()` is sole authoritative Bed relation. Report-02 listed this as residual of a closed OMIT, not a new undecided gap.

### Evidence (Expected vs Actual)

| | Citation |
|--|----------|
| **Expected (decision)** | `.dormSys/progress-log.md` `DOM-GAP-09B-CLOSE`: `OMIT bed() on Allocation header; AllocationItem::bed() ratified as sole authoritative Bed relation` |
| **Expected (matrix)** | `.dormSys/audit-status-matrix.md` `DOM-GAP-09B-CLOSE (A3) \| OMIT` |
| **Expected (schema)** | `.dormSys/database-map.md` §allocations: `bed_id` uuid NO, Notes `—` (no FK) |
| **Actual** | `AllocationModel.php`: `bed_id` fillable; **no** `bed()` method — OMIT honored |

### Options

| Option | Description | Risks | Trade-offs |
|--------|-------------|-------|------------|
| **A** | **Reaffirm OMIT** — document in open-decisions Closed; no code | Header `bed_id` column remains without Eloquent | Stable; residual column semantics stay soft |
| **B** | **Reopen** — allow header `bed()` Eloquent (still no FK unless separate schema decision) | Conflicts with item-level authority; dual Bed relations | Convenience vs clarity |
| **C** | **Reopen + remove/stop writing header `bed_id`** (future schema/app wave) | Large; write-path debt called out in 09B-CLOSE | Cleaner model; needs authorized remediation |

### Impact if left open

Not blocking if OMIT stands; confusion only if new waves re-propose header `bed()` without reading 09B-CLOSE.

### Recommended default _(advisory only)_

**Option A** — reaffirm OMIT; do not reopen without explicit Lead override.

---

## 3. DP-ALLOC-ITEM-BED-FK — Physical FK on `allocation_items.bed_id`

### Context

Item-level `bed_id` has Eloquent `bed()` and correct “no physical FK” docs; map/mig still have **no** FK. Question: add physical FK or keep soft UUID?

### Evidence (Expected vs Actual)

| | Citation |
|--|----------|
| **Expected (map)** | `.dormSys/database-map.md` §allocation_items: `bed_id` Notes `—` |
| **Expected (mig)** | `database/migrations/modules/allocation/2026_07_01_000002_create_allocation_items_table.php:16` uuid `bed_id`; `:24–27` FK **only** `allocation_id` |
| **Actual (model)** | `AllocationItemModel.php:48–56` — Eloquent `bed()`; PHPDoc Eloquent-only / no physical FK (ALLOC-DOC-ALIGN-01) |

### Options

| Option | Description | Risks | Trade-offs |
|--------|-------------|-------|------------|
| **A** | **Add** migration FK `allocation_items.bed_id` → `dormitory_beds.id` (+ onDelete policy) | Orphans/failing rows block migrate; cross-module FK vs AP-04 soft-ref culture | DB integrity; needs Critical DB Remediation wave |
| **B** | **Keep soft UUID**; document AP-04 intentional for items.bed_id | Orphan `bed_id` possible at DB level | Matches current map/mig; Eloquent already present |
| **C** | Keep soft UUID **and remove** Eloquent `bed()` (ports only) | Breaks callers of `bed()`; large unwind | Strict AGENTS cross-module reading |

### Impact if left open

Docs/model aligned; integrity policy undecided; future “Critical DB Remediation” cannot proceed.

### Recommended default _(advisory only)_

**Option B** unless Lead prioritizes DB-enforced integrity (**A**). Do not choose C without architecture pass (ties to DP-XMOD-BELONGS).

---

## 4. DP-BED-SIGNAL-OWNERSHIP — `last_signal_reference_id` on Domain `Bed`

### Context

Schema and `BedModel` carry `last_signal_reference_id`; Domain entity `Bed` does not. Application/Infra handle signal via allocation physical-state services.

### Evidence (Expected vs Actual)

| | Citation |
|--|----------|
| **Expected (map)** | `.dormSys/database-map.md` §dormitory_beds: `last_signal_reference_id` uuid YES; EVOLVED note mig `2026_07_12_000001_…` |
| **Actual (persistence)** | `BedModel.php:19,36` — property + fillable |
| **Actual (domain)** | `app/Modules/Dormitory/Domain/Entities/Bed.php` — constructor fields: id, roomId, label, status, occupancy only — **no** signal |
| **Actual (app/infra)** | `AllocationBedPhysicalStateRepository.php` / `AllocationPhysicalStateApplicationService.php` read/write `last_signal_reference_id` |

### Options

| Option | Description | Risks | Trade-offs |
|--------|-------------|-------|------------|
| **A** | **Document intentional** — signal is Infra/App concern; Domain Bed stays occupancy/status only | Domain incomplete vs full table row | Small; matches current call paths |
| **B** | **Add** Domain property + factory/update methods; wire repositories | Touches Domain + Infra + possibly Application | Full alignment; larger wave |
| **C** | Remove column from product use (unlikely) | Breaks correlation/signal checks in Application | Not supported by current usage |

### Impact if left open

No blocker for Persistence completeness; Domain↔schema ownership remains ambiguous for future entity work.

### Recommended default _(advisory only)_

**Option A** — ratify intentional split; optional follow-up doc-only ADR note.

---

## 5. DP-XMOD-BELONGS — Cross-module Eloquent `belongsTo` vs AGENTS.md

### Context

`AGENTS.md` forbids cross-module Eloquent queries / cross-module FKs (UUID value refs). Persistence models widely declare cross-module `belongsTo`.

### Evidence (Expected vs Actual)

| | Citation |
|--|----------|
| **Expected (policy)** | `AGENTS.md` Architecture Rules §2: “No cross-module Eloquent queries… Cross-module foreign keys are prohibited — store UUIDs as value references.” |
| **Actual (examples)** | `RequestModel.php` `employee()`/`dormitory()` → Employee/Dormitory; `LotteryRegistrationModel` `employee()`/`request()`; `AllocationModel` `employee()`/`sourceRequest()`; `AllocationItemModel` `bed()` → `BedModel`; Voucher models’ employee/dormitory/request relations |

### Options

| Option | Description | Risks | Trade-offs |
|--------|-------------|-------|------------|
| **A** | **Ratify exception** — Persistence `belongsTo` across modules allowed when no physical FK (AP-04); update AGENTS/ADR | Policy text vs code diverge until docs updated | Low churn; matches disk |
| **B** | **Unwind** — remove cross-module Eloquent; Application ports only | Large; breaks DX/tests | Strict AGENTS compliance |
| **C** | Hybrid — allow listed modules/relations only (allowlist ADR) | Allowlist maintenance | Middle path |

### Impact if left open

Architecture Guard / AGENTS tension continues; every relation wave re-litigates the rule.

### Recommended default _(advisory only)_

**Option A** with ADR + AGENTS errata — code already committed to pattern; unwind (B) is a separate mega-wave.

---

## Registry actions (this wave)

| ID | Already in `.dormSys/open-decisions.md`? | Action this wave |
|----|-------------------------------------------|------------------|
| **A2** | Yes — OPEN | Packaged only; **not** closed |
| **A3** | No (OMIT via progress-log/matrix only) | Packaged as residual; **not** appended as OPEN (would falsely reopen) |
| **DP-ALLOC-ITEM-BED-FK** | No | Append OPEN |
| **DP-BED-SIGNAL-OWNERSHIP** | No | Append OPEN |
| **DP-XMOD-BELONGS** | No | Append OPEN |

---

## Advisor (process)

| | |
|--|--|
| **Current approach** | Five decision-gated gaps; zero free fixes |
| **Recommended approach** | Lead decides A2 first (unblocks Allocation), then DP-XMOD-BELONGS (unblocks policy), then schema/signal items |
| **Reason** | A2 blocks concrete Allocation work; XMOD blocks every future relation wave’s legality |
| **Risks** | Deciding schema FK before XMOD may conflict with AP-04 soft-ref culture |
| **Trade-offs** | Sequential Lead sessions vs one mega-ratify |
