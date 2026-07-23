# A2-DATA-AUDIT-01

_Date: 1405/05/01 | 2026-07-23_  
_Mode: Read-only data audit. No code/schema changes._  
_Target DB:_ Sail PostgreSQL `laravel` (`dormsysproject-pgsql-1`, user `sail`)  
_Decision gate:_ A2 CLOSED Option A — Fix Wave only after this audit.

## Schema evidence (migration + live catalog)

| Fact | Evidence |
|------|----------|
| Column exists, nullable UUID | mig `database/migrations/modules/allocation/2026_07_01_000001_create_allocations_table.php:23` |
| Live column | `information_schema`: `allocations.source_lottery_result_id` uuid, `is_nullable=YES` |
| Target table PK | `lottery_results.id` uuid NOT NULL — mig `…/lottery/2026_06_30_000003_create_lottery_results_table.php:14` |
| No FK on `allocations.source_lottery_result_id` | Live FK query: only `lottery_results.program_id` / `lottery_results.registration_id` FKs; **none** on `allocations` |

## Query results (actual counts)

Executed via: `docker exec dormsysproject-pgsql-1 psql -U sail -d laravel`

| Metric | N | Query basis |
|--------|--:|------------|
| `allocations` total | **0** | `COUNT(*) FROM allocations` |
| `source_lottery_result_id` IS NULL | **0** | `WHERE source_lottery_result_id IS NULL` |
| `source_lottery_result_id` IS NOT NULL | **0** | `WHERE source_lottery_result_id IS NOT NULL` |
| `allocations.deleted_at` NOT NULL | **0** | soft-delete probe |
| `lottery_results` total | **0** | `COUNT(*) FROM lottery_results` |
| `lottery_registrations` total | **0** | `COUNT(*) FROM lottery_registrations` |
| Points to valid `lottery_results.id` | **0** | `EXISTS (SELECT 1 FROM lottery_results lr WHERE lr.id = a.source_lottery_result_id)` |
| Orphan vs `lottery_results` | **0** | NOT EXISTS against `lottery_results.id` |
| Equals a `lottery_registrations.id` | **0** | EXISTS against registrations |
| Equals registration but not result (A2-shaped drift) | **0** | registration hit AND result miss |

## Orphans / inconsistencies

**None observed** in this database — tables are empty; orphan and registration-vs-result probes all returned `0`.

## Classification

| Verdict | Applies? | Why |
|---------|----------|-----|
| **Safe to fix** | **YES (this DB)** | Zero non-null `source_lottery_result_id` rows → no stored wrong UUIDs to repair before consumer/contract Fix |
| Needs data cleanup | No (this DB) | No orphans / no registration-id-shaped values present |
| Blocker | No (this DB) | Schema present; audit gate satisfiable; empty set is a valid audit result |

**Scope caveat:** Audit is for the connected Sail DB `laravel` only. Other environments (staging/prod) require the same query pack before their Fix Waves.

## Recommended next step _(suggestion only)_

1. Re-run this query pack on any non-empty environment before Fix.  
2. If those envs also show `slr_nonnull = 0` (or all values already match `lottery_results.id`) → authorize **A2 Fix Wave** (consumer + lottery winner payload/`LotteryResultReadContract` to pass `lottery_results.id`; optional Persistence `sourceLotteryResult()`).  
3. If non-null values equal `lottery_registrations.id` → **Needs cleanup** mapping wave before Fix.  
4. Do **not** treat empty local DB as proof that production is clean.

## Advisor

| | |
|--|--|
| **Current** | Local Sail audit: empty allocations / lottery tables |
| **Recommended** | Proceed to A2 Fix design for local/dev once Lead accepts empty-audit as gate-pass; require env-specific re-audit before prod deploy |
| **Reason** | Decision gate is “audit real rows”; zero rows = no cleanup debt here |
| **Risks** | Fixing code then shipping to a DB with registration_id-shaped data without re-audit |
| **Trade-offs** | Fast local remediation vs mandatory per-env audit discipline |
