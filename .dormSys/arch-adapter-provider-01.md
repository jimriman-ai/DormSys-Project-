# ARCH-ADAPTER-PROVIDER-01 (C3 ∥ C4)

> 1405/05/01 (2026-07-23). Adapter + provider only. No C1/C2/C5, schema, or CLOSED-decision edits.

## Pre-check (repo evidence)

| Check | Result |
|-------|--------|
| Freeze on adapter/provider | **None** in `.dormSys/open-decisions.md` / constitution |
| C3 failures (current suite) | `CrossModuleAdapterLocationTest`; ModuleBoundary `Allocation\Infrastructure` ↛ `Request\*`; `arch:scan` UNREGISTERED adapter line |
| C4 failures | `IntegrationCompositionRootTest` — `IntegrationServiceProvider` overrides `boot()` |
| Root cause C3 | In-module cross adapter (`RequestLifecycleCommandAdapter`) contradicts ENFORCED Integrations policy + CheckIn parallel `RequestStayLifecycleCommandBridge` |
| Root cause C4 | Event listeners lived in `boot()`; composition-root rule is register-only |

## Drift

| Expected | Actual (pre-fix) | Disposition |
|----------|-------------------|-------------|
| Architecture: new cross-module adapters in `app/Integrations/` + bind in `IntegrationServiceProvider::register()` (`integration-layer-policy.md`, ADR, failing tests) | Adapter under Allocation Infrastructure + module provider bind | **Fixed** toward Architecture Expected |
| Spec07 contract still names `Infrastructure/Adapters/RequestLifecycleCommandAdapter` | Same stale path | **Reported only** — not rewriting CLOSED/spec text in this wave |
| Composition root: no `boot()` override | `boot()` with `Event::listen` | **Fixed** — listeners moved into `register()` |

## Changes

| Action | Path |
|--------|------|
| Add | `app/Integrations/Allocation/RequestLifecycleCommandBridge.php` |
| Delete | `app/Modules/Allocation/Infrastructure/Adapters/RequestLifecycleCommandAdapter.php` |
| Bind | `RequestLifecycleCommandPort` → bridge in `IntegrationServiceProvider::register()` |
| Unbind | same port from `AllocationServiceProvider` |
| Guard list | `architectureIntegrationPortClasses()` += `RequestLifecycleCommandPort` |
| C4 | Remove `boot()`; `Event::listen(...)` in `register()` |

## Advisor

| | |
|--|--|
| **Current (applied)** | Move adapter → Integrations bridge; bind at composition root; events in `register()` |
| **Rejected alternative** | Add path to `architectureLegacyCrossModuleAdapterPaths()` |
| **Reason** | Failing test + ADR reject new in-module cross adapters; legacy list is migrate-debt only |
| **Risks / trade-offs** | Bridge still uses Request Domain (same as CheckIn stay bridge). Spec07 path text drifts until a docs wave |

## Validation

| Gate | Command / result |
|------|------------------|
| PHPStan | `php vendor/bin/phpstan analyse --memory-limit=1G --no-progress` → **OK** |
| Focused C3/C4 + handoff | **11 passed** |
| Full Pest | **6 failed / 1989 passed** (one run); stable arch residual **5** = C2×3 + C5×2 |
| `arch:scan` | RED on **C2 MATRIX only** — UNREGISTERED adapter **gone** |

### Failure delta (of the prior 8)

| # | Failure | Cluster | After |
|---|---------|---------|-------|
| 1 | `CrossModuleAdapterLocationTest` | C3 | **RESOLVED** |
| 2 | `ForbiddenImportsScanTest` (MATRIX + UNREGISTERED) | C2+C3 | **C3 line gone**; MATRIX remains (C2) |
| 3 | `IntegrationCompositionRootTest` boot() | C4 | **RESOLVED** |
| 4 | ModuleBoundary Request App→Workflow Domain | C2 | unchanged |
| 5 | ModuleBoundary Allocation Infra→Request | C3 | **RESOLVED** |
| 6 | `ModuleInventoryParityTest` | C2 | unchanged |
| 7–8 | `MutationAuthorizationBoundaryTest` ×2 | C5 | unchanged |

**Suite count:** 8 → **5** stable Architecture (C2+C5). One intermittent `RequestReadContractTest` (history stage `hr` vs `department_manager`) failed in full suite, **passes in isolation**, passed prior baseline — not attributed to this wave; not fixed (out of C3/C4).

**C1:** Persistence allowlist untouched. **C2/C5:** not touched / not regressed (same failures).
