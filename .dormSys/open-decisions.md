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
| **A2** | LotteryResultModel / registration_id drift. Blocked action: `sourceLotteryResult()` implementation. Requires Lead authorization. | `app/Modules/Allocation/Application/Services/ProposedAllocationConsumer.php:44`; DOM-GAP-10-CLOSE | LEAD | **OPEN** — Logged 1405-04-31 / 2026-07-22 (wave A2-DECISION-REGISTER). |

---

## Notes (non-decisions)

- No FROZEN / PARKED / ACCEPTED rows copied from `docs/governance/open-decisions.md` into this draft (would require either full re-read+mirror policy — **DR-REG-04** — or invention).
- Draft files are for Lead ratification only.
