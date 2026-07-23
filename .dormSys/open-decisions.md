---
ratified: true
ratified_by: Lead
ratified_wave: REGISTRY-RATIFY-02
ratified_at: 2026-07-22T11:31:02Z
snapshot_sha256: 956945da642ba1f785cf14dce7dd8eb00779b4cfba2acae50309ab2cb23c2d5c
post_ratify_appends: ACCEPTED-OPEN
hash_scope: ratified_body_only
scope_note: Ratification asserts registry accuracy only. It does NOT resolve blockers recorded inside (HDAC-01/02/03/05, Spec04 BLOCKED, Business Owner UNRESOLVED, DR-REG-03/04/05), and does NOT extend to .specify/** or docs/governance/**.
---
# .dormSys Open Decisions Registry (RATIFIED)

> **Authority status:** RATIFIED (`REGISTRY-RATIFY-02`, Lead, 2026-07-22T11:31:02Z) — registry accuracy only.  
> **Scope limit:** Ratification does **not** resolve blockers recorded inside (HDAC-01/02/03/05, Spec04 BLOCKED, Business Owner UNRESOLVED, DR-REG-03/04/05), and does **not** extend to `.specify/**` or `docs/governance/**`.  
> **Bootstrap wave:** `REGISTRY-INIT-01`  
> **Generated:** 2026-07-22 (1405/04/31)  
> **Rule:** Evidence-cited only. Unknowns → `DECISION_REQUIRED` (status OPEN).

---

## Closed decisions (this wave)

| ID | Status | Wave | Statement | Evidence |
|----|--------|------|-----------|----------|
| **D-001** | **CLOSED** | REGISTRY-INIT-01 | Bootstrap `.dormSys` registries from repository evidence only. Agent generates draft; Lead ratifies before any use. | Lead message authorizing REGISTRY-INIT-01; files created under `.dormSys/` |
| **D-002** | **CLOSED** | REGISTRY-INIT-01 | `database/schema` does not exist and is not expected. `database/migrations` is the schema source of truth. Remove `database/schema` from all future wave scopes. | Lead message; path probe: `database/schema` absent (0 files) during DB-DISCOVERY-01 / this wave |
| **DR-DB-01** | **ACCEPTED** | MAP-ERRATA-01 | jobs map errata: `attempts`→`unsignedSmallInteger`, `reserved_at` nullable (GAP-DB-01 / GAP-DB-02). | Lead, 2026-07-22; DB-DISCOVERY-01 |
| **DR-REG-07** | **ACCEPTED** | REG-HASH-CONV-01 | snapshot_sha256 = sha256 of file excluding the `snapshot_sha256:` line | Lead, 2026-07-22 |
| **MANAGER-ASSIGN-CREATE** | **CLOSED** | MANAGER-ASSIGN-CREATE | Manager assignment Eloquent models live under `App\Models\Dormitory` (`DormitoryManagerAssignment`, `DormitoryUnitManagerAssignment`). `DormitoryAssignment` excluded / deferred to a separate wave. | Lead prompt MANAGER-ASSIGN-CREATE, 1405-04-31 / 2026-07-22; migrations `2026_07_16_000001` / `000002` |
| **D4** | **CLOSED** | D4-CLOSE | `dormitory_assignments` must be documented in `.dormSys/database-map.md`. Gap (map file absent on release) resolved by restoring map from `main`. | Lead Option A (D4-CLOSE), 1405-04-31 / 2026-07-22; wave DB-MAP-RESTORE; `.dormSys/progress-log.md` `[DB-MAP-RESTORE]`; `.dormSys/database-map.md` §`dormitory_assignments` |
| **DP-XMOD-BELONGS** | **CLOSED** | DECISION-CLOSE-01 | **Option C (allowlist):** Persistence-level read `belongsTo` across modules is allowed (AP-04 soft UUID). Cross-module Eloquent for **workflow / authorization / mutation** is forbidden. **Allowlist boundary:** permitted = Persistence `belongsTo` used only for read-side navigation of soft UUID refs (e.g. `RequestModel::employee()`, `AllocationItemModel::bed()`); forbidden = using those relations (or new cross-module Eloquent) inside workflow orchestration, authorization/gates, or write/mutation paths — those remain Application contracts/ports only (`AGENTS.md` §2; `specs/007-allocation-checkin/tasks.md` R5/R6/R7). **Rationale:** Disk already has widespread Persistence `belongsTo` (e.g. `app/Modules/Request/Infrastructure/Persistence/Models/RequestModel.php`, `AllocationItemModel.php`) while policy text forbids blanket cross-module Eloquent — allowlist reconciles both. **Next-wave boundary:** governance/AGENTS/ADR errata + Architecture Guard allowlist wording only — no relation unwind in this closure. **Suggested order:** 1st among post-close waves. | Lead DECISION-CLOSE-01, 1405/05/01 / 2026-07-23; `.dormSys/decision-package-01.md` §5; `AGENTS.md` Architecture Rules §2 |
| **A2** | **CLOSED** | DECISION-CLOSE-01 | **Option A:** `sourceLotteryResultId` / column `source_lottery_result_id` **must** reference `lottery_results.id`. Current consumer wiring of `registration_id` into that field is **drift/bug** (`ProposedAllocationConsumer.php:44`). Domain/Persistence naming already imply result id (`Allocation.php` `$sourceLotteryResultId`; `AllocationModel.php` `source_lottery_result_id`; map §allocations). Lottery read contract currently exposes winner `registration_id` only (`LotteryResultReadContract.php`). **Mandatory gate:** Fix Wave is allowed **only after Data Audit** on real rows of `allocations.source_lottery_result_id` (Lead text cited `allocation_items.source_lottery_result_id` — see drift note in DECISION-CLOSE-01 report; physical column is on `allocations`). Schema alone is insufficient. **Next-wave boundary:** (1) Data Audit wave on stored UUIDs vs `lottery_results`/`lottery_registrations`; (2) then authorized Fix Wave for consumer/contract/`sourceLotteryResult()` — no silent fix under this closure. **Suggested order:** 2nd (after DP-XMOD-BELONGS; Data Audit before Fix). | Lead DECISION-CLOSE-01, 1405/05/01 / 2026-07-23; `ProposedAllocationConsumer.php:44`; `.dormSys/database-map.md` §allocations; `LotteryResultReadContract.php` |
| **DP-ALLOC-ITEM-BED-FK** | **CLOSED** | DECISION-CLOSE-01 | **Option A:** `allocation_items.bed_id` remains **soft UUID (AP-04)** — no physical FK; keep existing Eloquent `bed()`. **Rationale:** Map Notes `—` and create mig FK only on `allocation_id` (`2026_07_01_000002_create_allocation_items_table.php`); PHPDoc already Eloquent-only (`AllocationItemModel.php:48–56`); Spec04 port forbids FK from beds to allocation_* (`allocation-physical-state-port.md`). **Next-wave boundary:** documentation/policy alignment only if needed — **no** FK migration; relation retention is ratified. **Suggested order:** 3rd. | Lead DECISION-CLOSE-01, 1405/05/01 / 2026-07-23; `.dormSys/database-map.md` §allocation_items; `AllocationItemModel.php` |
| **DP-BED-SIGNAL-OWNERSHIP** | **CLOSED** | DECISION-CLOSE-01 | **Option A:** `last_signal_reference_id` ownership stays in **Application/Infrastructure** (`BedModel` + Allocation physical-state services/repos); **not** added to Domain `Bed`. **Rationale:** Contract stores signal on `dormitory_beds.last_signal_reference_id` as traceability (`specs/004-accommodation-resource/contracts/allocation-physical-state-port.md`); Domain `Bed.php` has no signal property; App/Infra already read/write it (`AllocationBedPhysicalStateRepository.php`; assert in `AllocationAssignabilityLivePathTest.php`). **Next-wave boundary:** optional doc-only note of intentional split — **no** Domain entity property wave unless Lead reopens. **Suggested order:** 4th. | Lead DECISION-CLOSE-01, 1405/05/01 / 2026-07-23; `Domain/Entities/Bed.php`; `BedModel.php`; physical-state port |

---

## DECISION_REQUIRED (OPEN)

| ID | Question | Evidence pointer | Owner | Status |
|----|----------|------------------|-------|--------|
| **DR-REG-01** | Spatie permission migration creates tables via `Schema::create($tableNames['…'])`. Exact physical table names are resolved from config at runtime. `config/` was Excluded from this wave. What are the authoritative table names to record in `database-map.md`? | `database/migrations/modules/identity/2026_06_26_000002_create_permission_tables.php` (keys observed: `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`) | LEAD | **CLOSED** — Spatie table names recorded from `config/permission.php` (REGISTRY-FIX-01). Lead, wave REGISTRY-RATIFY-02, 2026-07-22. |
| **DR-REG-02** | `app/Models/User.php` has no explicit `$table` property. May the draft map this model to table `users` using Laravel’s default naming convention, or must `$table` be declared before mapping is recorded as fact? | `app/Models/User.php`; create migration `database/migrations/0001_01_01_000000_create_users_table.php` (`Schema::create('users', …)`) | LEAD | **CLOSED** — `users` mapped by Laravel convention; recorded as convention-based in `database-map.md`. Lead, wave REGISTRY-RATIFY-02, 2026-07-22. |
| **DR-REG-03** | This wave’s read scope for documents was only `docs/governance`. Spec trees such as `specs/` and `.specify/docs/` were not read. Should a future wave expand `spec-catalog.md` to those paths? | Scope declaration REGISTRY-INIT-01; `docs/governance` inventory in `.dormSys/spec-catalog.md` | LEAD | **OPEN** — ACCEPTED-OPEN at ratification (Lead, REGISTRY-RATIFY-02); deferred post-ratify, does not block Discovery. Lead, wave REGISTRY-RATIFY-02, 2026-07-22. |
| **DR-REG-04** | Existing canonical decision ledger exists at `docs/governance/open-decisions.md`. What is the post-ratification relationship between `.dormSys/open-decisions.md` and `docs/governance/open-decisions.md` (supersede / mirror / subset / independent)? | `docs/governance/open-decisions.md` (present); `.dormSys/open-decisions.md` (this registry) | LEAD | **OPEN** — ACCEPTED-OPEN at ratification (Lead, REGISTRY-RATIFY-02); deferred post-ratify, does not block Discovery. Backlog: canonical ledger sync (`docs/governance/open-decisions.md`) deferred until this DR closes. Lead, wave REGISTRY-RATIFY-02, 2026-07-22. |
| **DR-REG-05** | Eloquent/persistence models under `app/Modules/*/Infrastructure/Persistence/Models/` (and similar) were outside write/read model scope (`app/Models` only). Should a future wave map those models to tables? | Scope: `app/Models` only; `app/Models` contains `User.php` only (observed) | LEAD | **OPEN** — ACCEPTED-OPEN at ratification (Lead, REGISTRY-RATIFY-02); deferred post-ratify, does not block Discovery. Lead, wave REGISTRY-RATIFY-02, 2026-07-22. |
| **DR-REG-06** | Telescope migration uses `$schema->create(...)`. Confirm these three tables belong in the product schema map for Protocol waves, or mark them tooling-only / out-of-map. | `database/migrations/2026_06_22_184914_create_telescope_entries_table.php` | LEAD | **CLOSED** — Telescope classified tooling-only; relocated under Tooling (non-product). Lead, wave REGISTRY-RATIFY-02, 2026-07-22. |

### Post-close execution order (Lead DECISION-CLOSE-01 — boundary definition only, no Fix)

1. **DP-XMOD-BELONGS** — AGENTS/ADR allowlist wording  
2. **A2** — Data Audit on `allocations.source_lottery_result_id` **before** any Fix Wave  
3. **DP-ALLOC-ITEM-BED-FK** — soft UUID ratified (doc/policy only if needed)  
4. **DP-BED-SIGNAL-OWNERSHIP** — intentional App/Infra ownership (doc-only if needed)

---

## Notes (non-decisions)

- No FROZEN / PARKED / ACCEPTED rows copied from `docs/governance/open-decisions.md` into this draft (would require either full re-read+mirror policy — **DR-REG-04** — or invention).
- Draft files are for Lead ratification only.
- DECISION-CLOSE-01 closed A2 / DP-XMOD-BELONGS / DP-ALLOC-ITEM-BED-FK / DP-BED-SIGNAL-OWNERSHIP (moved to Closed table; removed from OPEN list).
