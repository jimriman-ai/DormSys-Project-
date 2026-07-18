# Architecture Decision Record — Modular Boundary Repair

**Status:** Accepted and stabilized (2026-07-04)  
**Scope:** `app/Modules/`, `app/Integrations/`, `app/Providers/IntegrationServiceProvider.php`  
**CI:** `composer run arch` (mandatory) · `tests/Architecture/` — 768 mandatory tests at first decay-prevention batch

This record documents what was repaired, why, and how to maintain the approved model. It reflects **actual code and tests**, not a target-state diagram.

---

## Summary of what was repaired

| Problem category | Symptom before repair | Repair applied |
|------------------|----------------------|----------------|
| Domain ↔ Infrastructure coupling | State classes referenced Eloquent model types | Removed Infrastructure imports/generics from Domain states |
| Application → Infrastructure injection | Actions wired to concrete adapters/repos | Application injects ports/contracts; Infrastructure implements |
| Cross-module wiring in Infrastructure | Adapters in module A called module B repos | Deleted in-module cross adapters; moved to `app/Integrations/` |
| Hidden composition | `PendingRequestReadPort` bound in Employee + Integration providers | Single binding in `IntegrationServiceProvider`; null stub removed |
| Audit Domain leakage | Identity/Voucher/Reporting imported Audit Domain enums | Switched to `AuditEntryDto` + Application ports |
| CheckIn ↔ Allocation Domain leak | Adapter used `AllocationId` VO | Bridge uses `AllocationReadContract` with string summaries |
| Composition root phase | Integration bindings in `boot()` | Moved to `register()` (post-cleanup) |

**Deleted files (do not recreate):**

- `Allocation/Infrastructure/Adapters/RequestReadAdapter.php`
- `Allocation/Infrastructure/Adapters/LotteryResultReadAdapter.php`
- `Request/Infrastructure/Adapters/EmployeeEligibilityGateway.php`
- `Request/Infrastructure/Adapters/PendingRequestReadAdapter.php`
- `CheckIn/Infrastructure/Adapters/AllocationAssignmentReadAdapter.php`
- `Employee/Infrastructure/Adapters/NullPendingRequestReadAdapter.php`

---

## Root cause categories

1. **Convenience adapters** — cross-module reads implemented as Infrastructure adapters inside the consumer module.
2. **Framework leakage** — Domain states typed against Eloquent models for Spatie states.
3. **Composition sprawl** — module service providers bound foreign ports with null stubs that were overridden later by registration order.
4. **Incomplete enforcement inventory** — CheckIn promoted to active module but not added to `architectureModuleNames()`.
5. **Pre-Integrations edges** — Lottery and Reporting cross-module wiring predates `app/Integrations/` policy.

---

## Accepted repair patterns

| Pattern | Where | Enforcement |
|---------|-------|-------------|
| Four layers per module | `app/Modules/{Module}/` | `ServiceProviderRegistrationTest` |
| Application contracts as cross-module API | `Application/Contracts/`, `Ports/` | `ModuleBoundaryTest` |
| Integrations bridges | `app/Integrations/{Consumer}/` | **POLICY** + feature/arch tests |
| Single integration composition root | `IntegrationServiceProvider::register()` | **POLICY** + review grep |
| Supplier read contracts with string/DTO types | e.g. `AllocationReadContract::getAllocationSummary(string)` | Reduces foreign Domain in bridges |
| Pest arch matrix | `ModuleBoundaryTest.php` + `LayerDependencyTest.php` | **ENFORCED** — 751 tests |
| Module-specific boundary tests | `*BoundaryTest.php` files | **ENFORCED** for documented edges |
| Null stubs for undeferred suppliers | `NullDormitoryReadAdapter`, etc. | Own-module only; Wave 1 pattern |

---

## Rejected anti-patterns

| Anti-pattern | Why rejected |
|--------------|--------------|
| Cross-module Eloquent / repository calls | Violates bounded context ownership |
| Infrastructure adapter calling foreign Infrastructure | Hidden from arch tests; deleted in repair |
| Application importing foreign Domain | Consumer depends on supplier internals |
| Duplicate port bindings across providers | Order-dependent overrides; removed for `PendingRequestReadPort` |
| Business logic in Integrations | Bridges become god objects |
| Runtime boundary checks | Constitution forbids production overhead (F01-026) |
| Reintroducing deleted in-module cross adapters | Explicitly removed and documented |

---

## Known remaining debt (honest inventory)

| Item | Type | CI today | Next step |
|------|------|----------|-----------|
| `CheckIn/OperatorRoleGate` → `Identity\Domain\UserId` | **[SUPERSEDED]** | N/A — debt closed | OperatorRoleGate uses `IdentityUserReadContract` (not removed); CheckIn in `architectureModuleNames()`; `AllocationAssignmentReadPort` binds via `IntegrationServiceProvider`. See § CheckIn matrix enrollment (2026-07-04). |
| CheckIn absent from `architectureModuleNames()` | **[SUPERSEDED]** | N/A — debt closed | CheckIn enrolled in 12-module matrix (same section). |
| `Lottery/Application/Adapters/RequestReadAdapter` | **Legacy tolerated** | Passes — Application→Application allowed | Move to Integrations when authorized |
| Reporting Infrastructure → Audit Application adapters | **[SUPERSEDED]** | N/A — moved | Bridges under `app/Integrations/Reporting/`; bind via `IntegrationServiceProvider`. |
| `IdentityServiceProvider` binds `AuditPermissionReadPort` | **[SUPERSEDED]** | N/A — moved | `AuditPermissionReadPort` binds in `IntegrationServiceProvider` only. |
| `ApprovedRequestReadBridge` uses `RequestId` VO | **Safe but brittle** | Passes | Optional when `RequestReadContract` accepts string |
| `DormitoryReadAdapter`, `AllocationPhysicalStateAdapter` | **Dead in prod DI** | N/A — test-only instantiation | Keep until tests refactored |
| `Request/README.md` references deleted adapter | **Documentation gap** | N/A | Update when docs touched |

---

## Safe cleanup already applied (post-repair)

| Change | File | Effect |
|--------|------|--------|
| Integration bindings → `register()` | `IntegrationServiceProvider.php` | Composition root at correct phase; behavior unchanged |
| Removed unwired null stub | `NullPendingRequestReadAdapter.php` deleted | Single production path via `PendingRequestReadBridge` |

---

## Maintenance workflow

### Single-module feature

1. Domain → `Domain/`
2. Use case → `Application/Services/`
3. Contract → `Application/Contracts/`
4. Persistence → `Infrastructure/Repositories/`
5. Bind in module `{Module}ServiceProvider`

No Integrations involvement.

### Cross-module feature

1. Identify consumer (port owner) and supplier (contract owner)
2. Add/reuse supplier Application contract
3. Add consumer port if needed
4. Implement bridge in `app/Integrations/{Consumer}/`
5. Bind in `IntegrationServiceProvider::register()` only
6. Add/update arch test for the edge
7. Run `php artisan test tests/Architecture/`

### Contract change

- Update bridge + all implementations in **one PR**
- Run full architecture + affected feature tests

### Definition of Done (architecture)

```bash
php artisan test tests/Architecture/
composer run phpstan
composer run pint
```

Architecture failures block merge at the same priority as feature test failures.

### CI decay-prevention batch (2026-07-04)

First mandatory guardrails added without changing business logic:

- `ModuleInventoryParityTest` — bootstrap/disk/matrix parity
- `IntegrationCompositionRootTest` — integration port binding location
- `CrossModuleAdapterLocationTest` — blocks new legacy-style adapters
- `ForbiddenImportsScanTest` + `scripts/architecture/forbidden-imports-scan.php`
- `ArchitectureAdvisoryTest` — non-blocking debt visibility (`composer run arch:advisory`)

See [ci-enforcement-matrix.md](./ci-enforcement-matrix.md) and [known-exceptions-registry.md](./known-exceptions-registry.md).

### CheckIn matrix enrollment (2026-07-04)

Closed CheckIn → Identity Domain leak:

- Added `IdentityUserReadContract::userHasRole(string, string)`
- `OperatorRoleGate` consumes read contract only (no `UserId` import)
- Removed `architectureCheckInForeignDomainImportAllowlist()`
- Added `CheckIn` to `architectureModuleNames()` (12-module matrix)

### When to update this record

- New module added to matrix
- Legacy adapter migrated to Integrations
- CheckIn added to enforcement inventory
- Material change to composition root bindings

Do **not** update for routine feature PRs.

---

## References

| Document | Path |
|----------|------|
| Boundary rules | [boundary-rules.md](./boundary-rules.md) |
| CI enforcement matrix | [ci-enforcement-matrix.md](./ci-enforcement-matrix.md) |
| Known exceptions registry | [known-exceptions-registry.md](./known-exceptions-registry.md) |
| Integration policy | [integration-layer-policy.md](./integration-layer-policy.md) |
| PR checklist | [pr-review-checklist.md](./pr-review-checklist.md) |
| Arch inventory | `tests/Architecture/architecture.php` |
| Context map | `.specify/docs/context-map.md` |
| Constitution | `.specify/memory/constitution.md` |

---

## Governance summary

DormSys modular architecture is **enforced in CI** for 12 matrix modules via Pest arch tests. Cross-module wiring for the repaired edges is **centralized** in `IntegrationServiceProvider`. Legacy Lottery adapter edge is **tolerated, not a template** for new work.
