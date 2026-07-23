# STOP-GATE-TRIAGE-01

> Decision Gate — **Read-only.** No code/config/test/migration changes.  
> CLOSED decisions (A2, XMOD, ALLOC-ITEM-FK, BED-SIGNAL) not reinterpreted.  
> Captured: 1405/05/01 (2026-07-23).

## Scope of “recent waves” (for Pre-existing vs Regression)

Uncommitted session waves on `release/f2-employee-auth-ui-l9`:

- **A2-FIX-01** (consumer/contracts → `lottery_result_id`)
- **LOTTERY-STAGE1-CREATE-01** (`CreateLotteryRegistrationRequestAction` + `tests/Pest.php` Lottery Stage-1 bind)
- **XMOD-ERRATA-01** (docs only: `AGENTS.md` / `CLAUDE.md`)
- Discovery docs (validation baseline, git audit) — not product code

**Not** treated as “recent” for regression attribution: committed release history (e.g. `a41dc8d` DOM-GAP-RESTORE). Where timing vs those commits is unproven → **Ambiguous**.

## Suite inventory (evidence)

| Source | Result |
|--------|--------|
| VALIDATION-BASELINE-01 full Pest | **46 failed**, 1960 passed (~1373s) |
| Same run Architecture share | **45** Architecture failures |
| Same run Feature share | **1** Feature file failure header: `ProductionHttpHardeningTest` |
| Spot re-run (this wave) | `ProductionHttpHardeningTest.php` alone → **4 failed** / 4 passed — all `NoStage1ApproverAvailableException` via `CreateLotteryRegistrationRequestAction.php:35` (file `require_once`s enrollment `it()` cases from Lottery helper file) |
| PHPStan full | GREEN (baseline) — out of Pest failure set |

---

## Failure table

| Failure | Layer | Module | Pre-existing / Regression | Evidence | Requires Lead Decision? |
|---------|-------|--------|---------------------------|----------|-------------------------|
| ProductionHttpHardening — lottery draw overlap mapping (and enrollment cases pulled via `require_once` when that file is the entry) | Feature / Presentation-HTTP | Production (+ Lottery helper) | **Regression** | Stack: `AssignStage1ApproverSnapshotAction.php:27` ← `CreateLotteryRegistrationRequestAction.php:35`. **HEAD** create action has **no** Stage-1; **WT** adds it (LOTTERY-STAGE1-CREATE-01). `tests/Pest.php` Stage-1 bind covers `Feature/Modules/Lottery` but **not** `Feature/Production`. Message: `No active Stage-1 Dormitory Manager approver is available.` | **No** (not Architecture/Schema; local harness/product-seed class — Lead still chooses path, but not a new CLOSED-decision reopen) |
| AllocationBoundaryTest ×6 (Request/Dormitory/Employee Infra + Persistence) | Architecture | Allocation | **Pre-existing** (vs recent uncommitted waves) | Messages: module must not use `*\{Request,Dormitory,Employee}\Infrastructure\*`. Uncommitted A2/Stage-1 do not add these imports. `AllocationModel` already has cross-module `belongsTo` at `a41dc8d`. **Ambiguous** vs whether suite was green before DOM-GAP-RESTORE (no bisect). | **Yes — Architecture** (Pest guard vs CLOSED DP-XMOD-BELONGS Option C allowlist tension; decision is path-to-green, not reopening Option C text) |
| CheckInBoundaryTest ×1 (Allocation Persistence) | Architecture | CheckIn | **Pre-existing** (vs recent waves) | Same Infra/Persistence pattern; not touched by A2/Stage-1 WT | **Yes — Architecture** (same allowlist/guard tension) |
| LotterySupplierBoundaryTest ×6 (Request/Employee/Dormitory Infra + Persistence) | Architecture | Lottery | **Pre-existing** (vs recent waves) | A2-FIX changes Application contracts/services only; no new Lottery→Infra Persistence imports in WT diff for those relations | **Yes — Architecture** |
| RequestConsumerBoundaryTest ×3 (Employee/Dormitory Infra + Persistence) | Architecture | Request | **Pre-existing** (vs recent waves) | Not introduced by Stage-1 Application change | **Yes — Architecture** |
| VoucherBoundaryTest ×2 (Request Infra + Persistence) | Architecture | Voucher | **Pre-existing** (vs recent waves) | Untouched by recent waves | **Yes — Architecture** |
| ReportingBoundaryTest ×1 (Audit Infra) | Architecture | Reporting | **Pre-existing** (vs recent waves) | Untouched by recent waves | **Yes — Architecture** |
| ModuleBoundaryTest ×19 (cross-module Infra/Application imports across Request, Workflow, Dormitory, Allocation, CheckIn, Lottery, Voucher, Notification, Reporting) | Architecture | Multi | **Pre-existing** (vs recent waves) | Includes Request Application → Workflow Domain (same as scan). ApproveRequestStageAction Workflow imports present since Workflow track (`git log` shows long-lived). | **Yes — Architecture** |
| ForbiddenImportsScanTest — standalone scan exit ≠ 0 | Architecture | Request + Allocation | **Pre-existing** | Identical MATRIX FOREIGN DOMAIN + UNREGISTERED ADAPTER output as pre-A2 conversation / VALIDATION-BASELINE-01 `arch:scan`. Paths: `ApproveRequestStageAction.php:18–19`, `RejectRequestAction.php:19–21`, `RequestLifecycleCommandAdapter.php:8` | **Yes — Architecture** |
| CrossModuleAdapterLocationTest — unregistered adapter | Architecture | Allocation | **Pre-existing** | Adapter since Wave 3 (`02ecb0a`); not in `architectureLegacyCrossModuleAdapterPaths()` allowlist | **Yes — Architecture** |
| ModuleInventoryParityTest — matrix foreign domain findings | Architecture | Request→Workflow | **Pre-existing** | Same five Request→Workflow Domain exception imports as scan (`ModuleInventoryParityTest.php:86`) | **Yes — Architecture** |
| IntegrationCompositionRootTest — `boot()` declaring class | Architecture | Integrations / Providers | **Pre-existing** (vs recent waves) | `IntegrationServiceProvider` declares `boot()` at HEAD; not in A2/Stage-1 WT | **Yes — Architecture** (composition-root rule) |
| MutationAuthorizationBoundaryTest ×2 — Workflow actions unaccounted / business mutation list | Architecture | Workflow / Mutation | **Pre-existing** (vs recent waves) / **Ambiguous** age | Lists `ApplyRequestApprovalAutoApprovalsAction`, `DecideRequestApprovalStageAction`, `StartRequestApprovalWorkflowAction` as unaccounted (`MutationAuthorizationBoundaryTest.php:64`). Not touched by A2/Stage-1. Exact first-fail commit not established. | **Yes — Architecture** (mutation registry policy) |

**A2-FIX-01:** No Pest failure in the baseline set is attributed to `lottery_result_id` wiring (A2-core Pest was green in prior wave). Architecture failures unchanged by consumer field swap.

**XMOD-ERRATA-01:** Docs-only; no failure attributed.

---

## Distinct root causes (no fix suggestions)

1. **Production Pest scope lacks Stage-1 fixture bind** while WT `CreateLotteryRegistrationRequestAction` now requires `AssignStage1ApproverSnapshotAction` (LOTTERY-STAGE1-CREATE-01). Observed in `Feature/Production` (and enrollment `it()`s loaded via `require_once` when that file is entry).

2. **Architecture Pest / `arch:scan` forbid cross-module Infrastructure & Persistence imports** while Persistence models retain cross-module `belongsTo` (disk reality; CLOSED Option C allowlist is policy text — guard tests still fail).

3. **Request Application imports Workflow Domain exceptions** (`ApproveRequestStageAction`, `RejectRequestAction`) — flagged by `arch:scan`, `ForbiddenImportsScanTest`, `ModuleBoundaryTest`, `ModuleInventoryParityTest`.

4. **Unregistered cross-module adapter** — `Allocation\Infrastructure\Adapters\RequestLifecycleCommandAdapter` imports `RequestRepositoryContract` without legacy-path registration.

5. **IntegrationServiceProvider overrides `boot()`** — composition-root test expects no declaring `boot()` on that class.

6. **Workflow Application mutation actions not in mutation-authorization accountancy lists** — three Workflow services appear as unaccounted in `MutationAuthorizationBoundaryTest`.

---

## Ambiguities (explicit)

- Whether Architecture Persistence-boundary failures first appeared at DOM-GAP-RESTORE (`a41dc8d`) vs earlier: **not bisected** → Ambiguous for that sub-timeline; classified **Pre-existing vs A2/Stage-1/XMOD session waves** only.
- Whether Production failure is “missing Pest bind” vs “missing production Stage-1 manager seed” as the *sole* environmental gap: stack proves create calls AssignStage1 and resolver returns empty in that test process; **both** are consistent with evidence; not separated further.

---

## Advisor (triage methodology only — not failure fixes)

| | |
|--|--|
| **Current approach** | Classify from VALIDATION-BASELINE-01 full-suite log + HEAD vs WT diffs + one Production spot re-run |
| **Recommended approach** | Same classification basis; optional future gate: store JUnit/CLI `--log-junit` on each baseline so Pre-existing/Regression is commit-diffable without narrative memory |
| **Reason** | Full re-suite (~23m) adds little once inventory is fixed; HEAD/WT diff uniquely proves Stage-1 regression |
| **Risks / Trade-offs** | Re-using baseline risks drift if WT changes mid-gate; spot-check Production confirmed still red. Parallel suite runs previously hung PHPStan (baseline advisor). |

---

## Guardrail confirmation

- No fixes applied.
- No CLOSED decision text edited.
- No speculative architecture/schema changes.
