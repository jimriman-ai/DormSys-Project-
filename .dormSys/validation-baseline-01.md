# VALIDATION-BASELINE-01

> Discovery only. No fixes. Captured 1405/05/01 (2026-07-23).  
> Environment: Sail container `dormsysproject-laravel.test-1`.

## Commands executed

| Gate | Exact command |
|------|----------------|
| Architecture scan (`composer arch:scan`) | `php scripts/architecture/forbidden-imports-scan.php` |
| Architecture Pest (`composer arch:test`) | `php -d memory_limit=512M vendor/bin/pest tests/Architecture/ --exclude-group architecture-advisory --no-coverage` |
| PHPStan (`composer phpstan`) | `php vendor/bin/phpstan analyse --memory-limit=1G --no-progress` |
| Full Pest | `php -d memory_limit=512M vendor/bin/pest --no-coverage` |

## Results summary

| Gate | Result | Failure cause (if red) |
|------|--------|------------------------|
| PHPStan (full) | **GREEN** | — (0 errors; exit 0; ~53s alone after hung parallel attempt) |
| Architecture Guard — `arch:scan` | **RED** | MATRIX FOREIGN DOMAIN: Request Application imports Workflow Domain exceptions (`ApproveRequestStageAction`, `RejectRequestAction`). UNREGISTERED CROSS MODULE ADAPTERS: `Allocation\…\RequestLifecycleCommandAdapter` → `RequestRepositoryContract`. Exit 1. |
| Architecture Guard — `arch:test` | **RED** | **45 failed**, 902 passed (~820s). Same scan failure plus module boundary / supplier-boundary / adapter-location / IntegrationServiceProvider `boot()` / mutation-auth / inventory-parity violations (Persistence `belongsTo` / Infra imports across modules). |
| Full Pest suite | **RED** | **46 failed**, 1960 passed (~1373s). **45 Architecture** (same family as `arch:test`) + **1 Feature**: `ProductionHttpHardeningTest` → `NoStage1ApproverAvailableException` at `AssignStage1ApproverSnapshotAction` called from `CreateLotteryRegistrationRequestAction` (Production Pest scope has no Stage-1 fixture bind). |

## Verdicts

| Question | Answer |
|----------|--------|
| Full-suite green? | **No** |
| Architecture Guard FAIL still active (pre-existing scan)? | **Yes** — identical Request↔Workflow + unregistered Allocation adapter findings |
| PHPStan baseline | **Green** |
| Non-arch product/test red? | **Yes — 1**: Production HTTP hardening / lottery create Stage-1 (observed after LOTTERY-STAGE1-CREATE-01) |

## Non-Architecture failure (detail)

- **Test:** `Tests\Feature\Production\ProductionHttpHardeningTest`
- **Exception:** `NoStage1ApproverAvailableException` — “No active Stage-1 Dormitory Manager approver is available.”
- **Stack:** `AssignStage1ApproverSnapshotAction.php:27` ← `CreateLotteryRegistrationRequestAction.php:35`
- **Note:** `tests/Pest.php` Stage-1 `beforeEach` binds Request/Allocation/Lottery/CheckIn/Mutation — **not** `Feature/Production`.

## Architecture failure themes (observed messages)

1. **Forbidden-import scan** — Request Application → Workflow Domain exceptions; unregistered Allocation→Request adapter.
2. **Cross-module Persistence/Infra imports** — Allocation, Lottery, Request, CheckIn, Voucher, Reporting, ModuleBoundaryTest family (tension with DP-XMOD-BELONGS Option C allowlist vs strict Pest arch rules).
3. **IntegrationServiceProvider** — overrides `boot()` (test expects register-only).
4. **MutationAuthorizationBoundaryTest** — Workflow mutation actions listed as violations.
5. **ModuleInventoryParityTest** — matrix vs app modules mismatch (message truncated in log; treat as inventory drift).

## Advisor (run methodology)

| | |
|--|--|
| **Current** | Parallel PHPStan + full Pest + arch:test in one Sail container |
| **Recommended** | Sequential: `arch:scan` → PHPStan → Pest (or Pest excluding Architecture then `arch:test`) |
| **Reason** | Parallel run left PHPStan host docker-exec hung (~11m, no process in container); re-run alone succeeded in ~53s |
| **Risks / Trade-offs** | Sequential slower wall-clock; fewer false hangs / cleaner attribution |

## Ambiguities (no guess)

- Whether all 45 `arch:test` failures are “pre-existing vs newly introduced by recent waves” was **not** bisected in this discovery (would need historical CI / prior baseline).
- Whether Production failure is solely missing Pest bind vs missing production seed of Stage-1 manager was **not** separated beyond stack evidence above.
