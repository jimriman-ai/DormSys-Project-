# Session Handoff — T3 CLOSED | HD-02 Unfreeze Assessment (Discovery Phase −1)

_Date: 2026-07-21 (1405/04/30)_  
_Authority: NON-AUTHORITY discovery report. Does not supersede `docs/governance/open-decisions.md`._  
_Constraint: read-only — no production changes._

---

## 0. Confirmed session status

| Wave / item | Status | Evidence |
|-------------|--------|----------|
| T2 | **CLOSED** | `dcb1d13` (T2-4), `c8ea8a2` (B2a / related) |
| T3 G-REQ-01…08 | **CLOSED** | G-REQ-07 `f19bfed`, G-REQ-08 `cc07bb1` (and prior G-REQ commits) |
| CD-010-A1 | **SIGNED-OFF** | `open-decisions.md` |
| HD-02 Lottery | **FROZEN** (AUTH-013 / DECIDED—A) | new Lottery work frozen |
| HD-03 Reporting | **FROZEN** / OUT-OF-CURRENT-F3 | AUTH-013 |
| DBT-3 | **OPEN Hard STOP** | `auth:api` → `identity` migrate |

**Protocol note:** User referenced “DB Remediation Protocol v1.2”. No checked-in `docs/` copy of that protocol was found in this pass. This report follows the requested Discovery Phase −1 outline (tables, migrations, cross-domain deps, guardrail risks, Lead prerequisites). Treat protocol citation as **PROPOSED** until Lead points to the canonical file.

---

## 1. HD-02 Lottery — schema inventory

### 1.1 Migrations (`database/migrations/modules/lottery/`)

| Migration | Table | Notes |
|-----------|-------|-------|
| `2026_06_30_000001_create_lottery_programs_table.php` | `lottery_programs` | UUID PK; `dormitory_id` UUID **no FK**; soft deletes; status indexes |
| `2026_06_30_000002_create_lottery_registrations_table.php` | `lottery_registrations` | FK → `lottery_programs`; `request_id` + `employee_id` UUID **no cross-module FK**; unique `(program_id, request_id)` |
| `2026_06_30_000003_create_lottery_results_table.php` | `lottery_results` | FK → programs + registrations; rank/outcome |
| `2026_06_30_000004_create_lottery_eligible_snapshots_table.php` | `lottery_eligible_snapshots` | FK → programs; JSON payload + scoring_config; unique `program_id` |

### 1.2 Tables (summary)

| Table | Cross-domain UUID refs (no FK) | In-module FKs |
|-------|--------------------------------|---------------|
| `lottery_programs` | `dormitory_id`, actor audit UUIDs | — |
| `lottery_registrations` | `request_id`, `employee_id` | `program_id` → `lottery_programs` |
| `lottery_results` | — | `program_id`, `registration_id` |
| `lottery_eligible_snapshots` | — | `program_id` |

Related **non-lottery** surfaces touched by Lottery runtime (not lottery migrations):

| Surface | Relation |
|---------|----------|
| Request type `lottery_registration` | Created via `CreateLotteryRegistrationRequestAction` (Request module) |
| `settings` (via `LotteryScoringConfigReader` → `DB::table('settings')`) | **D-SETTINGS-LOTTERY-X** accepted exception until HD-02 unfreeze |
| Allocation `source_lottery_result_id` | Allocation consumes lottery outcomes (R6 / ProposedAllocation) |

---

## 2. Cross-domain dependencies

### 2.1 Lottery → Request (strong)

| Edge | Mechanism | Risk if unfrozen carelessly |
|------|-----------|------------------------------|
| Approved lottery registration read | `RequestReadAdapter` → `RequestReadContract` | Stage-1 / approval chain must be green (T2 B2a / G-REQ-03) |
| Enroll / lock | `LotteryRequestReadPort.findApprovedLotteryRegistration` | `InvalidRequestTransitionException` / missing Stage-1 snapshot (T2 Feature FAIL cluster) |
| Request owns type | `RequestType::LotteryRegistration` + create action in Request | Request Feature `LotteryRegistrationRequestTest` still red under freeze |

### 2.2 Lottery → Allocation (outbound)

| Edge | Mechanism |
|------|-----------|
| Draw winners → allocations | `ProposedAllocationPort` / `ProposedAllocationConsumer` (Allocation) |
| Allocation method | `lottery_sourced` + `source_lottery_result_id` UUID ref |

### 2.3 Lottery → Employee / Dormitory / Identity

| Edge | Mechanism |
|------|-----------|
| Score | `EmployeeLotteryScorePort` (stub/null adapters present) |
| Dormitory | `dormitory_id` UUID on program (no FK) |
| Auth | Mutation HTTP / Livewire surfaces may still touch `auth:api` (DBT-3) |

### 2.4 Lottery → Reporting

| Edge | Finding |
|------|---------|
| Direct Reporting imports under `app/Modules/Lottery` | **None observed** in this scan |
| Reporting → Lottery | **None observed** under `app/Modules/Reporting` |

**Conclusion:** HD-02 unfreeze is **not** blocked by a hard Lottery↔Reporting code edge in-module; HD-03 remains a **separate** freeze. Shared risk is suite noise / program governance, not a FK graph.

### 2.5 Request → Lottery (supplier only)

Request README: supplier of accommodation applications; **no** Lottery execution logic. Still ships `CreateLotteryRegistrationRequestAction` and type enum — unfreeze will pressure Request Feature tests that create/approve lottery-registration requests.

---

## 3. Risks vs G-REQ-01…08 (Presentation / Request guards)

| Guard | Relevance to HD-02 unfreeze |
|-------|------------------------------|
| **G-REQ-01** transition matrix | Lottery Feature fails often throw `InvalidRequestTransitionException` (T2 A2). Unfreeze without fixing Request OA-05-03 / approval fixture paths will keep Lottery suite red. |
| **G-REQ-02** api-guard | Lottery HTTP/Livewire may use `actingAs(..., 'api')` similarly to Request UI (DBT-3). Unfreeze does **not** clear DBT-3; new Lottery Presentation tests must not expand api-guard debt without markers/Lead. |
| **G-REQ-03** Stage-1 bind | Enroll/approve paths that call `approveRequestStageForTest` must remain under Pest bind scopes or explicit bind — already scanned. Lottery Feature tests under `tests/Feature/Modules/Lottery/` were **excluded** from G-REQ-03; after unfreeze, extend scan or require same contract. |
| **G-REQ-04…08** Presentation purity | Lottery already has `Presentation/Http`. Unfreeze work that adds Livewire/HTTP must obey no Eloquent/DB/Bus/Event/`App::make` rules (G-REQ-04…08 are **module-wide** for Presentation). |
| **D-SETTINGS-LOTTERY-X** | Direct `DB::table('settings')` in scoring reader — accepted until HD-02 unfreeze; unfreeze should include sunset plan (WP-SYS / settings contract). |

---

## 4. Known FAIL / debt that will surface on unfreeze (from T2 snapshot)

Non-exhaustive, from T2 Feature FAIL dump + triage:

- `LotteryBackgroundJobsTest`, `LotteryHttpFlowCompletionTest`, `LotteryIntegrationBoundaryTest`, `LotteryMutationAuthorizationTest`, `LotteryProgramDrawTest`, `LotteryProgramLockTest`, `LotteryResultReadContractTest`, `LotterySemanticIsolationTest`
- `LotteryAllocationHttpFlowTest` (Allocation + lottery)
- `LotteryRegistrationRequestTest` (Request module; Stage-1 snapshot missing)
- Mutation/Production suites that embed lottery datasets

**Do not treat these as new regressions from T3** — they were FROZEN/SKIP under HD-02.

---

## 5. Lead prerequisites for HD-02 unfreeze (decision checklist)

PROPOSED gate list for Lead (not invented approvals — checklist for deliberation):

1. **Explicit AUTH / decision instrument** superseding or amending HD-02 freeze (“new Lottery work FROZEN”) for a scoped WP (not blanket “all green”).
2. **Scope packet:** which WPs allowed (e.g. test remediation only vs schema vs scoring settings sunset for D-SETTINGS-LOTTERY-X).
3. **Request coupling plan:** Stage-1 bind + OA-05-03 for `lottery_registration` request flows (ties T2 A2 / G-REQ-01/03).
4. **DBT-3 posture:** confirm Lottery Presentation stays on existing guards or is deferred until identity migration (Hard STOP remains).
5. **Allocation consumer:** confirm ProposedAllocation / lottery_sourced allocation tests in or out of first unfreeze slice.
6. **HD-03:** confirm Reporting stays frozen (no implied unfreeze).
7. **Success metric:** e.g. scoped Pest filter green + Architecture G-REQ-04…08 still green — **not** full suite.

---

## 6. Recommendation (PROPOSED)

| Option | Description |
|--------|-------------|
| **A — Discovery-only close** | Accept this Phase −1 report; **no** HD-02 unfreeze yet. Next: Lead AUTH for scoped Lottery WP. |
| **B — Scoped unfreeze (test-only)** | Unfreeze **tests/Feature/Modules/Lottery** remediation only; no schema; settings exception remains until separate WP. |
| **C — Full product unfreeze** | Requires AUTH + settings sunset + Request/Allocation coupling plan — highest risk. |

**Suggested next Lead prompt:** choose A/B/C; if B/C, name exact WP id and Done-when.

---

## 7. Out of scope this pass

- HD-03 Reporting Discovery Phase −1 (not started; can mirror this template on Lead order)
- Production / migration edits
- Auto-fix of Lottery Feature fails
- Reinterpretation of AUTH-013

---

## 8. Artifact index

| Path | Role |
|------|------|
| This file | Handoff + HD-02 Phase −1 discovery |
| `database/migrations/modules/lottery/*` | Schema evidence |
| `app/Modules/Lottery/README.md` | Port map |
| `docs/governance/open-decisions.md` | HD-02 / HD-03 / DBT-3 / D-SETTINGS-LOTTERY-X |
| T3 Architecture guards | `tests/Architecture/Presentation*GuardTest.php`, `RequestDomainBoundaryTest.php`, `Stage1HelperContractTest.php`, `RequestTestApiGuardTest.php`, `RequestTransitionGuardTest.php` |
