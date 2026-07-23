# ARCH-GUARD-ALLOWLIST-01 (C1)

> Encodes DP-XMOD-BELONGS **Option C** in Architecture Guard only. No production code changes.  
> 1405/05/01 (2026-07-23).

## Pre-check

| Check | Result |
|-------|--------|
| Option C = allowlist (not blanket-forbid) | **Confirmed** — `.dormSys/open-decisions.md` DP-XMOD-BELONGS; `AGENTS.md` §2 |
| Guard Freeze | **None observed** |
| Drift | None blocking — Actual guards were stricter than Option C; aligned |

## Allowlist entries

| Entry | Mechanism |
|-------|-----------|
| `architectureOptionCForeignPersistenceModelAllowlist()` | All `App\Modules\{Module}\Infrastructure\Persistence\Models` |
| Pest `->ignoring($optionC)` after `->not->toUse(...)` | Allocation / Lottery / Request / Voucher / CheckIn / Reporting boundary tests |
| ModuleBoundary Infra isolation | `->ignoring("{$foreign}\Infrastructure\Persistence\Models")` |
| Removed | Explicit “must not import foreign Persistence” arch rules that contradicted Option C |
| Docs | `docs/architecture/known-exceptions-registry.md` §5 Option C |

## Validation

| Metric | Before (post REGRESSION-FIX) | After |
|--------|------------------------------|-------|
| Full Pest failed | **45** | **8** |
| Full Pest passed | 1961 | **1987** |
| `arch:scan` | RED (C2+C3) | RED (unchanged C2+C3 findings) |

### Remaining 8 (not C1 Persistence; C2–C5)

| Failure | Cluster |
|---------|---------|
| `ForbiddenImportsScanTest` + `ModuleInventoryParity` + ModuleBoundary Request App→Workflow Domain | **C2** |
| `CrossModuleAdapterLocationTest` + ModuleBoundary Allocation Infra→Request (adapter) + scan UNREGISTERED | **C3** |
| `IntegrationCompositionRootTest` boot() | **C4** |
| `MutationAuthorizationBoundaryTest` ×2 | **C5** |

### C1 disposition

- Option C Persistence `belongsTo` guard lag: **resolved**.
- Residual ModuleBoundary `Allocation\Infrastructure` ↛ `Request\*` is **`RequestLifecycleCommandAdapter`** importing Request Application/Domain — **outside Option C allowlist**; deferred to **C3** (no silent broaden of ignore).

## Advisor

| | |
|--|--|
| **Current (applied)** | Pest `ignoring` of foreign Persistence Models namespaces after `not->toUse` |
| **Alternative** | Ignore entire Persistence subtree or exclude owner Models from targets |
| **Reason** | Matches Option C width (Models belongsTo only); keeps non-Model Infra bans |
| **Risks** | Ignoring filters dependency edges by prefix — Models-only is correct; broadening would hide C3 |

## Guardrails

- No production / schema / CLOSED-decision text changes.
- C2–C5 not “fixed.”
