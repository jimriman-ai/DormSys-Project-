# WP-REGRESSION-01 Report

**Mode:** REVIEW-ONLY  
**Date:** 1405/04/28 \| 2026-07-19  
**Branch:** `release/f2-employee-auth-ui-l9`  
**Agent:** Regression Analysis Agent  

> No code, migrations, test edits, or VCS mutations performed. This file is the sole authorized deliverable.

---

## 1. Executive Summary

Observed “full suite failures” on the **host** runner (`php artisan test`) are **not Sprint B product regressions**.

**Evidence:**

| Runner | Command | Named inventory result |
|--------|---------|------------------------|
| Host (Windows) | `php artisan test --filter=ReleaseAllocationTest` | **4 failed**, 0 assertions — `SQLSTATE[08006] … could not translate host name "pgsql"` (terminal `219969`) |
| Sail container | `docker compose exec -T laravel.test php artisan test --filter="ReleaseAllocationTest\|…\|EmployeeLoginRateLimitTest"` | **52 passed** (155 assertions), `EXIT:0` (terminal `219967`) |

Prior Phase Closure full suite in Sail: **1903 passed** (5483 assertions), `EXIT:0` (terminal `219966`, WP-PHASE-CLOSURE-01).

**Classification of all prompted failure classes:** **C — ENVIRONMENT / CONFIGURATION** when executed on host; **not failing** under Sail baseline.

**Sprint B code overlap with Allocation / Employee / Identity create-deactivate / Auth session suites:** **No** (see §4).

**Recommendation:** **Option C** (environment repair / use Sail for gate suites) before any Sprint C product fix work. Do **not** treat host failures as Sprint B regressions.

---

## 2. Failure Inventory

**Host failure signature (shared):**  
`SQLSTATE[08006] [7] could not translate host name "pgsql" to address: Name or service not known (Connection: pgsql, Host: pgsql, Port: 5432, Database: testing, …)`  
**Config pin:** `phpunit.xml:31` — `<env name="DB_HOST" value="pgsql"/>`  
**Stack first hit (sample):** RefreshDatabase / migration existence probe before test body — e.g. `ReleaseAllocationTest.php:33` (host run `219969`).

**Sail status for same inventory:** all **PASS** (`219967`).

| Test | Module | Failure (host) | Suspected Layer | Evidence |
|------|--------|----------------|-----------------|----------|
| `ReleaseAllocationTest` (all 4 cases) | Allocation | `pgsql` DNS / DB connect | Env / PHPUnit DB | Host `219969`; Sail PASS `219967:9` |
| `AllocationOverlapTest` | Allocation | Same class (host suite FAIL list `138.txt`); Sail PASS | Env | Host inventory prompt + Sail `219967:15` |
| `CreateEmployeeActionTest` | Employee | Same; Sail PASS | Env | Sail `219967:20` |
| `CreateUserActionTest` | Identity | Same; Sail PASS | Env | Sail `219967:23` |
| `DeactivateUserActionTest` | Identity | Same; Sail PASS | Env | Sail `219967:27` |
| `CreatePersonalRequestStage1SnapshotTest` | Request | Same; Sail PASS | Env | Sail `219967:32` |
| `RequestTransitionMatrixTest` | Request | Same; Sail PASS | Env | Sail `219967:36` |
| `ApiAuthSessionEntryTest` | Auth | Same; Sail PASS | Env | Host FAIL list `138.txt:17–24`; Sail `219967:54` |
| `AuthContractRegressionTest` | Auth | Same; Sail PASS | Env | Host `138.txt:26–29`; Sail `219967:63` |
| `AuthEdgeCaseTest` | Auth | Same; Sail PASS | Env | Host `138.txt:31–37`; Sail `219967:68` |
| `EmployeeLoginRateLimitTest` | Auth | Same; Sail PASS | Env | Host `138.txt:40–44`; Sail `219967:77` |

**Related production files for host failure path:** not domain logic — DB connector via `phpunit.xml:31` + host DNS for Docker service name `pgsql`.  
**Domain/application boundary:** N/A for failure cause (connection never established; 0 assertions).

**Additional host FAIL noise (same env class, not in prompt list):** e.g. `DatabaseConnectionTest` (`138.txt:140–142`), `RedisConnectionTest` (`138.txt:158–160`), broad Auth/Feature modules — consistent with unreachable Sail network aliases from host PHP.

---

## 3. Classification Matrix

| Test group | Class | Evidence |
|------------|-------|----------|
| Allocation (`ReleaseAllocationTest`, `AllocationOverlapTest`) | **C** | Host SQLSTATE pgsql; Sail PASS; Sprint B commits do not touch Allocation (`git show e30960d` / `3907a4a` file lists) |
| Employee (`CreateEmployeeActionTest`) | **C** | Same |
| Identity (`CreateUserActionTest`, `DeactivateUserActionTest`) | **C** | Same |
| Request (`CreatePersonalRequestStage1SnapshotTest`, `RequestTransitionMatrixTest`) | **C** | Sail PASS; Stage1 snapshot test adjacent to Sprint B Request work but green in container |
| Auth (Api/Contract/Edge/RateLimit) | **C** | Sail PASS; Sprint B commits do not list Auth Feature tests or LoginUserAction |

| Class | Count (prompted groups) | Notes |
|-------|-------------------------|-------|
| A SPRINT-B REGRESSION | **0** | No failing product assertion under Sail; no overlapping production files in Sprint B commits for these modules |
| B PRE-EXISTING FAILURE | **0** (as product failures) | Would require Sail red; Sail green |
| C ENVIRONMENT | **All prompted** | Host `DB_HOST=pgsql` unresolvable |
| D TEST DRIFT | **0** | Expectations satisfy under Sail |
| E UNCONFIRMED | **0** for prompted set under dual-runner evidence |

---

## 4. Sprint B Regression Analysis

### 4.1 L0 Baseline

| Item | Evidence |
|------|----------|
| Branch | `release/f2-employee-auth-ui-l9` |
| Dirty tree (uncommitted) | `docs/features/stage1-approver-console/implementation-lock.md`, `docs/governance/{governance-log,open-decisions,project-state,roadmap}.md` — **docs only** (`git status --short`) |
| Tip | `3907a4a500beee7fc38205291ff329e04f43b1b3` — `record SB-D10 (retroactive) + Wave 2 doc sync [WP-DOC-SYNC-01]` |
| Prior | `e30960d1606ae05834836930ee24b48eac37a0d9` — subject `SHA: UNVERIFIED` (includes ListPending + UI-M2 lock docs + Stage1 Livewire touch) |

### 4.2 Sprint B implementation surface (committed)

**`e30960d` (WP-UI-M2-01 / ListPending boundary + UI-M2 docs):**  
`ListPendingStage1RequestsAction.php`, `RequestServiceProvider.php`, `Stage1ApproverConsolePage.php`, UI-M2 lock/l3 docs, governance docs.

**`3907a4a` (WP-RQ-W2-01 Wave 2 + WP-DOC-SYNC-01):**  
`ExemptMutationActionRegistry.php` (+ListPending), `Stage1ApproverConsolePage.php`, Stage1 Blade, `Stage1ApproverConsoleFilterTest.php`, ActionsTest NC helper, governance/lock docs.

**WP-DOC-SYNC-01 / closure docs:** governance markdown (also uncommitted deltas present).

### 4.3 Overlap with prompted failing areas

| Prompted module | Touched by Sprint B commits? | Overlap verdict |
|-----------------|------------------------------|-----------------|
| Allocation | **No** | No Sprint B regression path |
| Employee Create | **No** | No |
| Identity Create/Deactivate | **No** | No |
| Auth session / rate-limit Feature | **No** in `e30960d`/`3907a4a` | No |
| Request Stage1 snapshot / transition matrix | Snapshot test file historically present; **passes in Sail**; Wave 2 changed Livewire/list/filter + ListPending read Action | Not a red regression under baseline runner |

**IdentityRoleGuard:** last substantive commits predate these two tips (e.g. `7517508`, `32c677b`, `25104a7` per `git log` on that path) — **not** attributed to WP-RQ-W2-01 / WP-DOC-SYNC-01 tip files for this triage.

---

## 5. Core Risk Assessment

### 5.1 Category severity (as **product** risk)

| Category | Severity | Assessment |
|----------|----------|------------|
| C ENVIRONMENT (host runner) | **HIGH for process** (blocks Phase Closure if gate uses host PHP) / **LOW for product** | Does not indicate broken Allocation/Auth/Request domain when Sail is used |
| A Sprint B regression | N/A (none evidenced) | — |

### 5.2 Attention areas

1. **Identity/Auth contract**  
   - Host FAIL list includes Auth Feature suites (`138.txt:17–90`).  
   - Sail: prompted Auth filters **PASS** (`219967`).  
   - **Does not evidence** authentication contract breakage under approved container runner.

2. **Request lifecycle / R-07**  
   - `RequestTransitionMatrixTest` + Stage1 snapshot **PASS** in Sail.  
   - Sprint B Wave 2 is list/filter presentation + read Action; approve/reject Actions not rewritten in tip commits’ Wave 2 surface.  
   - **No evidenced R-07 violation** from this triage.

3. **Allocation / occupancy**  
   - Host FAIL = DB connect before assertions.  
   - Sail: `ReleaseAllocationTest` + `AllocationOverlapTest` **PASS**.  
   - **No evidenced occupancy correctness regression** from Sprint B.

---

## 6. Recommended Next Action

### Option A — Fix before Sprint C  
**Not recommended** for these failures as product bugs. No failing assertions under Sail; no Sprint B file overlap with Allocation/Identity/Employee/Auth session failures.

### Option B — Accept as baseline debt and document  
**Partial:** document **host PHPUnit + `DB_HOST=pgsql`** as known runner debt (merge-agnostic / local Windows). Do **not** accept as domain baseline red.

### Option C — Environment repair required first — **RECOMMENDED**  
**Reasoning:** Failure root cause is host inability to resolve Docker DNS name `pgsql` (`phpunit.xml:31`). Repair = run closure/CI gates via `docker compose exec … php artisan test` (or equivalent Sail), or provide a Lead-approved host override policy (out of scope for this WP to implement).

**Affected modules (process):** all RefreshDatabase Feature/Unit suites when run on host.  
**Affected modules (product):** none evidenced.

**Phase Closure unblock:** Re-run full suite **in Sail**; treat host red as **non-authoritative** unless Lead redefines the gate runner.

---

## 7. Evidence Appendix

### Commands

```text
git status --short
git branch --show-current
git log -5 --oneline
git show 3907a4a --stat
git show e30960d --stat
php artisan test --filter=ReleaseAllocationTest          # host → EXIT 2, SQLSTATE pgsql
docker compose exec -T laravel.test php artisan test --filter="ReleaseAllocationTest|AllocationOverlapTest|CreateEmployeeActionTest|CreateUserActionTest|DeactivateUserActionTest|CreatePersonalRequestStage1SnapshotTest|RequestTransitionMatrixTest|ApiAuthSessionEntryTest|AuthContractRegressionTest|AuthEdgeCaseTest|EmployeeLoginRateLimitTest"
# → 52 passed, EXIT 0
```

### Config

- `phpunit.xml:31` — `DB_HOST=pgsql`

### Terminal artifacts

- Host suite FAIL inventory: `terminals/138.txt` (active `php artisan test`)
- Host Allocation detail: `terminals/219969.txt`
- Sail inventory green: `terminals/219967.txt`
- Prior full Sail green (1903): `terminals/219966.txt` (WP-PHASE-CLOSURE-01)

### STOP check

- No code fix required for classification.  
- No migration required.  
- No governance decision change required.  
- Sprint B scope **is** separable from failure cause (env vs Request UI Wave 2).  
- **No STOP discrepancy** beyond: Phase Closure must not use host `php artisan test` as authoritative red without Sail confirmation.

---

**Report status:** COMPLETE (REVIEW-ONLY)  
**Verdict for prompted “failures”:** **ENVIRONMENT (C)** — Option **C** next.
