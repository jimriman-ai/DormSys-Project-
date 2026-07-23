# REGRESSION-FIX-01

> Targeted Fix — Production Stage-1 regression only.  
> 1405/05/01 (2026-07-23).

## Change

| File | Change |
|------|--------|
| `tests/Pest.php` | Added `'Feature/Production'` to Stage-1 `beforeEach` bind scopes (same fixture as Request/Allocation/Lottery/…) |

No product/architecture/schema changes.

## Advisor

| | |
|--|--|
| **Current (applied)** | Extend existing Pest Stage-1 fixture bind to `Feature/Production` |
| **Alternative considered** | Local `beforeEach` only inside `ProductionHttpHardeningTest` |
| **Reason** | Matches established multi-module Pest pattern; covers all Production feature tests |
| **Risks / Trade-offs** | Slightly broader than one-file bind; no Architecture/Schema impact |

## Validation

| Metric | Before (STOP-GATE / VALIDATION-BASELINE) | After |
|--------|------------------------------------------|-------|
| Full Pest failed | **46** | **45** |
| Full Pest passed | 1960 | **1961** |
| ProductionHttpHardeningTest (spot) | 4 failed | **8 passed** |
| Feature / NoStage1 in full suite | present | **absent** |
| New non-Arch failures | — | **none observed** |
| Remaining failures | — | Architecture cluster only (path B) |

Commands:

- Spot: `php -d memory_limit=512M vendor/bin/pest --no-coverage tests/Feature/Production/ProductionHttpHardeningTest.php`
- Full: `php -d memory_limit=512M vendor/bin/pest --no-coverage`
