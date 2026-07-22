# DB Remediation Protocol v1.2

_Status: CANONICAL for DormSys DB remediation sessions (Lead-approved creation 2026-07-22)._  
_Authority: Operational protocol. Does **not** supersede `docs/governance/open-decisions.md` or AUTH instruments._

---

## 1. Purpose

Define a repeatable path from **Discovery Phase −1** through **HD closeout (HD-A…HD-H)** and optional **module unfreeze** (e.g. HD-02 Lottery Option B), without silent production drift.

---

## 2. Discovery Phase −1 checklist

Complete before any schema or unfreeze execution:

| # | Check | Done when |
|---|--------|-----------|
| D1 | Inventory tables + migrations for the target domain | Paths + table list recorded |
| D2 | Map cross-domain UUID refs / FKs / ports | Edges listed (Request, Allocation, etc.) |
| D3 | List FROZEN / Hard STOP overlaps (HD-02/03, DBT-3, …) | Explicit non-touch list |
| D4 | Map risks vs active Architecture guards (e.g. G-REQ-*) | Guard impact noted |
| D5 | Propose Lead options (A discovery-only / B scoped / C full) | Options in handoff |
| D6 | Confirm protocol path exists (`docs/governance/db-remediation-protocol-v1.2.md`) | This file |

**STOP** if D3 conflicts with requested work and Lead has not scoped an exception.

---

## 3. Remediation steps HD-A through HD-H

Evidence baseline: `docs/governance/database-architecture-map-v1.md` (Phase 0 matrix).  
Each HD requires Lead decision before production/schema change.

| ID | Topic | Typical touch surface | Guard conditions |
|----|-------|----------------------|------------------|
| **HD-A** | `check_in_records`: missing index / no partial unique on open stay | 1 migration | No Lottery/Reporting; include `up`+`down`; Pest covering uniqueness |
| **HD-B** | `CreateAllocationFromRequestAction` bedId fallback | Application Action (not schema-first) | Explicit Lead scope; no dormant FK invention |
| **HD-C** | `activity_log` migration missing `down()` | Migration patch | Rollback test; append-only audit rules unchanged |
| **HD-D** | Lottery scoring reader ≠ port (`LotteryScoringConfigReader`) | Lottery / Settings | **FROZEN while HD-02 frozen**; D-SETTINGS-LOTTERY-X until unfreeze |
| **HD-E** | Allocation exclusion = person + range only (not bed) | Migration alter | PostgreSQL GiST; dual-book risk documented |
| **HD-F** | `sessions.user_id` indexed, no FK; bigint → `users` | Migration / Auth | Align with HD-H dual-stack; no Identity UUID coercion |
| **HD-G** | `ModuleMigrationPathsTest` omits system / check_in | **Test-only** | Prefer test PR; zero production |
| **HD-H** | Dual auth: `users` (bigint) vs `identity_users` (uuid) | Auth / Backend | **Hard STOP overlap with DBT-3**; no silent guard migrate |

### HD closeout procedure

1. Cite Map v1 evidence row.  
2. Lead DECIDES option (fix / defer / accept exception).  
3. Implement only within decision scope.  
4. Validate: migrate + rollback (if schema) + targeted Pest.  
5. Register debt if deferred (`open-decisions.md` via Lead).

---

## 4. Module unfreeze (e.g. HD-02 Option B)

### 4.1 Option shapes

| Option | Meaning |
|--------|---------|
| **A** | Discovery-only; remain frozen |
| **B** | **Test-only** scoped unfreeze (no production) |
| **C** | Product/schema unfreeze (requires AUTH + WP) |

### 4.2 Option B guard conditions (mandatory)

1. **Zero production code** (`app/`, `database/` migrations, `routes/`).  
2. **Explicit ALLOWED vs FROZEN artifact list** (tables, ports, tests).  
3. If a test requires FROZEN artifacts → **BLOCKED** (document; do not “fix through”).  
4. Architecture guards (e.g. G-REQ-01…08) must still **PASS**.  
5. Session handoff under `docs/audit/` with pass/fail matrix.  
6. HD-03 / DBT-3 remain frozen unless separate Lead instrument.

### 4.3 HD-02 Option B reference scope (2026-07-22)

| ALLOWED | FROZEN (no touch) |
|---------|-------------------|
| Tables: `lottery_programs`, `lottery_results`, `lottery_eligible_snapshots` | `lottery_registrations` |
| Program lifecycle tests (create/open/close/cancel) without enroll | `ProposedAllocation` / Allocation lottery-sourced flows |
| Domain/unit program & scoring pure tests (no enroll port) | Any DBT-3 / `auth:api` dependent changes |

---

## 5. Test Execution Constraints

### 5.1 Serial shared-DB rule (mandatory)

Lottery suite (**Hd02OptionB** / group `hd02-option-b`) **MUST** run **serially** relative to Architecture guardrail suites (**GReqGuards** / group `g-req-guards`).

| Rule | Detail |
|------|--------|
| Rationale | Shared Sail `testing` PostgreSQL database — parallel `artisan test` processes deadlock on `RefreshDatabase` (observed exit 2 / `SQLSTATE[40P01]`) |
| Canonical verification | **Serial run only** — first `GReqGuards`, then `Hd02OptionB` |
| Verified (1405/04/31) | **32 + 56 = 88 passed**, exit 0 (serial); parallel = KNOWN-INVALID |
| Enforcement (test config) | Pest groups `g-req-guards`, `hd02-option-b`, `serial-shared-db`; cross-process `flock` in `tests/Support/serial-shared-db-lock.php`; PHPUnit testsuites in `phpunit.xml` |

### 5.2 Canonical commands

```bash
docker compose exec -T laravel.test php artisan test --no-ansi --testsuite=GReqGuards
docker compose exec -T laravel.test php artisan test --no-ansi --testsuite=Hd02OptionB
```

Do **not** launch both suites as concurrent processes against the same `DB_DATABASE=testing`.

---

## 6. Execution phases (summary)

| Phase | Name | Output |
|-------|------|--------|
| −1 | Discovery | Handoff + inventory |
| 0 | Protocol + HD matrix | This protocol; Map v1 HD readiness |
| 1 | Scoped execution (Option B = tests) | Unfreeze markers (if any) / allowed suite green |
| 2+ | Schema/product (Option C only) | Migrations + WP Done-when |

---

## 7. Definition of Done (Option B)

- [x] Protocol v1.2 present  
- [x] ALLOWED/FROZEN/BLOCKED inventory published  
- [x] No production diff  
- [x] G-REQ (or stated Architecture set) green  
- [x] Allowed Pest suite executed; results in handoff  
- [x] Serial execution constraints documented + test-config enforcement  
- [x] Lead can decide next slice without re-discovery  

---

## 8. Related artifacts

| Artifact | Path |
|----------|------|
| Architecture map | `docs/governance/database-architecture-map-v1.md` |
| Schema audit | `docs/governance/schema-migration-audit-2026-07-21.md` |
| HD-02 Phase −1 | `docs/audit/t3-handoff-hd02-unfreeze-discovery.md` |
| Option B execution | `docs/audit/hd02-option-b-execution.md` |
| Open decisions | `docs/governance/open-decisions.md` |
| Serial lock helper | `tests/Support/serial-shared-db-lock.php` |