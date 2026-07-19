# DormSys — Project State (Session Snapshot)

> **NON-AUTHORITY.** Session navigation index only.

<!-- AUTO-UPDATED by Cursor after each prompt. Lead commits. -->
_Last updated: 1405/04/28 | 2026-07-19 | Sprint: F3 Sprint B | Session: Unit-test “failures” = host DB_HOST=pgsql (no code fix)_

**Authority note:** PA-03 PASS; SB-D7 pending Lead. Listed unit tests are green in Sail container; host `php artisan test` cannot resolve `pgsql`.

---

## 0. Session Delta (this prompt only)

| Change | Target | Old → New | Evidence |
|--------|--------|-----------|----------|
| Investigate | 10 listed unit tests | hypothesized domain regressions → **disproven** | host errors all `could not translate host name "pgsql"` |
| Verify | same 10 tests in container | **10 passed** | `docker exec … --filter=CreateEmployeeActionTest\|ReleaseAllocationTest\|…` |
| Code | production | **unchanged** | no behavior bug proven |

---

## 0.1 Current Work Level (سطح کاری فعلی)

🧑‍⚖️ **Human Decision — test runner environment** (run via Sail/docker; do not flip `phpunit.xml` `DB_HOST=pgsql`)

---

## 2. Active Feature Map

| ID | Title | Status | Stage | Blocker |
|----|-------|--------|-------|---------|
| UI-M2 | Unit Manager Dashboard | **L3 ACCEPTED (SB-D6)** \| PA-03 **PASS** \| L6+ NOT authorized \| Lock pending SB-D7 | Sprint B | Lead SB-D7 |

---

## 6. Lifecycle Matrix (affected)

| Feature | L3 | L6 | L8 | L9 | Notes |
|---------|----|----|----|----|-------|
| UI-M2 | ✅ SB-D6 | ❌ pending SB-D7 + Lock | — | — | unchanged |

---

## 7. Next Step

**Action:** Lead run suite via Sail/docker (`docker exec dormsysproject-laravel.test-1 php artisan test`); issue SB-D7 when ready.  
**Owner:** Lead  
**Gate:** SB-D7; operational test runner  
**Target files:** none for these unit “failures”  
**Done when:** suite run inside container (or Lead decides host `.env` override policy)  
**Blocker:** host DNS for `pgsql` if using bare `php artisan test`  

**Suggested user prompt:**
> Run full suite via Sail container. Issue SB-D7 for UI-M2 when ready.

---

## 8. Gap Registry

| Gap ID | Status | Notes |
|--------|--------|-------|
| PA-03 | **DONE** | Review PASS |
| SB-D7 | **ABSENT** | Lead may issue |
| Host phpunit vs Sail DB_HOST | **OBSERVED** | `phpunit.xml:31`; not a domain regression |
