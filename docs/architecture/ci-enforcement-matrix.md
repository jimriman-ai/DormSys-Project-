# CI Architecture Enforcement Matrix

**Status:** Approved — first mandatory batch (2026-07-04)  
**Mandatory CI gate:** `composer run arch`  
**Advisory (non-blocking):** `composer run arch:advisory`  
**Inventory source:** `tests/Architecture/architecture.php`

Legend:

- **Blocking** — fails CI / merge gate
- **Advisory** — visible debt signal; does not block merge
- **PR checklist** — manual reviewer gate; not automated

---

## Enforcement matrix

| Rule name | Protects | Scope | Enforcement type | Blocking | False-positive risk | Rollout |
|-----------|----------|-------|------------------|----------|---------------------|---------|
| Domain → Eloquent ban | Pure Domain layer | `App\Modules\*\Domain` | Architecture test (`LayerDependencyTest`) | Yes | Low | **Now** |
| Domain → Infrastructure ban | Layer inward flow | `App\Modules\*\Domain` | Architecture test + static scan | Yes | Low | **Now** |
| Domain → Facades ban | Framework isolation in Domain | `App\Modules\*\Domain` | Architecture test + static scan | Yes | Low | **Now** |
| Application → Infrastructure ban | Port-based Application layer | `App\Modules\*\Application` | Architecture test + static scan | Yes | Low | **Now** |
| Application → foreign Domain ban (matrix) | Cross-module contract boundary | 12 matrix modules | Architecture test (`ModuleBoundaryTest`) + static scan | Yes | Low | **Now** |
| Active-module inventory parity | Bootstrap ↔ disk ↔ matrix inventory | All module providers | Architecture test (`ModuleInventoryParityTest`) | Yes | Low | **Now** |
| Integration port composition root | Single wiring location | 5 integration ports | Architecture test (`IntegrationCompositionRootTest`) + static scan | Yes | Low | **Now** |
| Legacy port binding location | Tolerated debt visibility | Lottery + Identity legacy ports | Architecture test (`IntegrationCompositionRootTest`) | Yes (wrong file only) | Low | **Now** |
| Cross-module adapter location (new code) | Integrations-first policy | `Application/Adapters`, `Infrastructure/Adapters` | Architecture test (`CrossModuleAdapterLocationTest`) + static scan | Yes | Low–medium | **Now** |
| Forbidden import static scan | Obvious textual violations | Domain + Application layers | Script + architecture test | Yes | Low | **Now** |
| Module pair boundary isolation | Full matrix cross-talk | 12 matrix modules | Architecture test (`ModuleBoundaryTest`) | Yes | Low | **Now** |
| Service provider registration | Module bootstrapping parity | Matrix modules | Architecture test (`ServiceProviderRegistrationTest`) | Yes | Low | **Now** |
| Edge-specific boundary tests | Approved context edges | Request, Lottery, Reporting, etc. | Architecture test (module edge files) | Yes | Low | **Now** |
| CheckIn full matrix enrollment | Complete CheckIn isolation | CheckIn all layers | `ModuleBoundaryTest` | Yes | Low | **Done (2026-07-04)** |
| Recursive Infrastructure → foreign Application | Reporting↔Audit legacy | Reporting Infrastructure | Advisory test | No | High if enforced now | **Later** |
| Foreign Domain on public contracts | Contract surface purity | Cross-module ports/DTOs | Advisory + review | No | Medium | **Later** |
| Cross-module Eloquent / FK ban | Constitution persistence rule | Migrations/models | PR checklist | No | N/A | **Later** |
| Integration bridge business logic | Thin bridge policy | `app/Integrations/*` | PR checklist | No | Medium | **PR checklist** |
| New matrix module onboarding | Inventory completeness | New modules | PR checklist + inventory test update | Yes when added | Low | **PR checklist** |

---

## Local commands

```bash
# Mandatory decay-prevention gate (matches CI pre-test step)
composer run arch

# Architecture tests only (excludes advisory group)
composer run arch:test

# Fast static scan only
composer run arch:scan

# Non-blocking debt visibility
composer run arch:advisory

# Full application test suite (includes architecture via artisan test)
composer run test
```

---

## CI placement

| Job | Step | Command |
|-----|------|---------|
| `.github/workflows/tests.yml` | Architecture decay prevention (mandatory) | `composer run arch` |
| `.github/workflows/tests.yml` | Full test suite | `php artisan test` |

Architecture checks run **before** the full suite so boundary failures surface early.

**PR-level governance:** reviewers use [pr-review-checklist.md](./pr-review-checklist.md); authors complete `.github/pull_request_template.md`.

---

## Related documents

- [known-exceptions-registry.md](./known-exceptions-registry.md)
- [boundary-rules.md](./boundary-rules.md)
- [integration-layer-policy.md](./integration-layer-policy.md)
- [pr-review-checklist.md](./pr-review-checklist.md)
