# Architecture Decision Record — Modular Boundaries & Integration Layer

**Status:** Accepted (implemented)  
**Date:** 2026-07-04  
**Scope:** `app/Modules/`, `app/Integrations/`, `app/Providers/IntegrationServiceProvider.php`  
**Enforcement:** `tests/Architecture/` (670+ Pest tests), PHPStan level 8

This record documents **why** the current architecture exists, **what** it replaced, and **how** to maintain it — grounded in the actual codebase, not a generic Clean Architecture template.

---

## Context — what was going wrong

Before the boundary refactor, CI failed `LayerDependencyTest` and `ModuleBoundaryTest` with real violations:

| Problem | Symptom | Example location (removed or fixed) |
|---------|---------|-------------------------------------|
| Domain coupled to Infrastructure | State classes referenced Eloquent model types | `RequestState`, `LotteryProgramState` PHPDoc/`@extends` |
| Application injected Infrastructure | Actions constructed with concrete repositories/adapters | Allocation actions binding Infrastructure adapters directly |
| Cross-module wiring inside Infrastructure | Adapters in module A imported module B repositories | `Allocation/Infrastructure/Adapters/RequestReadAdapter.php` (deleted) |
| Employee ↔ Request coupling in wrong module | Adapter lived under Request Infrastructure | `Request/Infrastructure/Adapters/PendingRequestReadAdapter.php` (deleted) |
| Hidden composition | Cross-module ports bound in multiple providers | `PendingRequestReadPort` in both Employee and Integration providers |
| Audit domain leakage | Identity/Voucher/Reporting imported Audit **Domain** enums | Fixed to `AuditEntryDto` + application ports |

These were not stylistic issues — they allowed compile-time coupling that violates DormSys constitution module boundaries (F01-026+) and context-map integration rules (Application Service or Domain Event only).

---

## Decision — patterns chosen

### 1. Four layers per module with strict inward dependencies

```
Presentation / Infrastructure  →  Application  →  Domain
```

**Enforced by:** `tests/Architecture/LayerDependencyTest.php`

Application never imports Infrastructure (even own module). Infrastructure implements Application contracts and may import own Domain for mapping.

### 2. Module matrix isolation

For each pair of modules in `architectureModuleNames()`:

- Domain, Infrastructure, Presentation: **no** imports from foreign module (any layer)
- Application: may import foreign **Application** only — not foreign Domain, Infrastructure, or Presentation

**Enforced by:** `tests/Architecture/ModuleBoundaryTest.php` (auto-generated per module pair)

### 3. `app/Integrations/` as the cross-module composition layer

Cross-context **port implementations** that translate between modules live outside both modules:

```
app/Integrations/
├── Allocation/ApprovedRequestReadBridge.php
├── CheckIn/AllocationAssignmentReadBridge.php
└── Request/
    ├── EmployeeEligibilityBridge.php
    └── PendingRequestReadBridge.php
```

**Composition root:** `app/Providers/IntegrationServiceProvider.php` — registered **last** in `bootstrap/providers.php`.

### 4. Application contracts as the only cross-module API

Modules expose capability through:

- `Application/Contracts/*Contract.php` — public supplier surface
- `Application/Contracts/Ports/*Port.php` — consumer-defined outbound/inbound ports
- `Application/Contracts/Internal/*` — module-private gateway interfaces (implementations in Integrations)
- `Application/DTOs/*` — read models crossing boundaries

**Example:** Employee defines `PendingRequestReadPort`; Request exposes data via `PendingRequestQueryPort`; bridge connects them without Employee importing Request Domain.

### 5. Pest architecture tests as the enforcement mechanism

Chose Pest `arch()` expectations over Deptrac or runtime guards because:

- Rules live beside PHPUnit/Pest feature tests
- Failures name exact dependency violations
- Module list centralized in `tests/Architecture/architecture.php`
- Incremental module-specific rules added (`AllocationBoundaryTest`, `ReportingBoundaryTest`, etc.)

See `.specify/docs/ADR/002-module-boundary-enforcement.md` for original ADR intent.

### 6. Reporting as downstream read-only consumer (CD-017)

Reporting may consume Audit **application** contracts via dedicated infrastructure adapters, guarded by:

- `ReportingBoundaryTest` — blocks Audit Infrastructure; single-file rule for `AuditHistoryReadContract`

New cross-module read edges should prefer `app/Integrations/` for consistency.

---

## Patterns explicitly avoided

| Avoided pattern | Why | What we do instead |
|-----------------|-----|-------------------|
| Cross-module Eloquent queries | Breaks bounded context ownership | Application service + contract on supplier |
| Cross-module foreign keys | Constitution prohibits | Store UUID references without FK |
| Infrastructure adapters calling foreign repositories | Hides dependency from arch tests | Integration bridge + supplier contract |
| Binding foreign ports in module providers | Order-dependent overrides, duplicate wiring | `IntegrationServiceProvider` only |
| Domain state referencing Eloquent models | Domain ceases to be pure | State classes without Infrastructure generics |
| Application → own Infrastructure imports | Untestable without database | Inject contract interface |
| Business logic in Integrations | Bridges become god objects | Delegation only |
| Runtime boundary checks | Constitution forbids production overhead (F01-026) | Static Pest + PHPStan in CI |

**Do not recreate deleted adapters:**

- `Allocation/Infrastructure/Adapters/RequestReadAdapter.php`
- `Allocation/Infrastructure/Adapters/LotteryResultReadAdapter.php`
- `Request/Infrastructure/Adapters/EmployeeEligibilityGateway.php`
- `Request/Infrastructure/Adapters/PendingRequestReadAdapter.php`
- `CheckIn/Infrastructure/Adapters/AllocationAssignmentReadAdapter.php`

---

## Legacy debt (known, tracked)

These pass CI but should not be copied in new code:

| Item | Current location | Target state |
|------|------------------|--------------|
| Lottery → Request read adapter | `Lottery/Application/Adapters/RequestReadAdapter.php` | Move to `app/Integrations/Lottery/` when next touched |
| Reporting → Audit read adapter | `Reporting/Infrastructure/Adapters/AuditHistorySourceReadAdapter.php` | Keep guarded; new edges via Integrations |
| CheckIn → Identity domain type | `CheckIn/Application/Services/OperatorRoleGate.php` uses `UserId` | Identity contract accepting string ID, or Identity bridge |
| CheckIn not in arch matrix | `architectureModuleNames()` omits CheckIn | Add module + fix gaps when authorized |
| ApprovedRequestReadBridge uses `RequestId` VO | Required by `RequestReadContract` signature today | Acceptable until contract accepts string |

---

## Problems solved (verification)

| Gate | Result |
|------|--------|
| `LayerDependencyTest` + `ModuleBoundaryTest` | 670/670 pass |
| Domain → Infrastructure imports | Zero in `app/Modules/*/Domain` |
| Application → Infrastructure imports | Zero in `app/Modules/*/Application` |
| Cross-module Employee ↔ Request ↔ Allocation wiring | Centralized in `IntegrationServiceProvider` |
| Duplicate `PendingRequestReadPort` binding | Removed from `EmployeeServiceProvider` |

---

## Long-term maintenance guidance

### Adding a feature inside one module

1. Domain rules → `Domain/`
2. Use case → `Application/Services/` or `Application/Actions/`
3. Contract → `Application/Contracts/`
4. Persistence → `Infrastructure/Repositories/` implementing the contract
5. Bind contract → implementation in module `{Module}ServiceProvider`

No Integrations involvement.

### Adding a feature across modules

1. Identify **consumer** (defines need) and **supplier** (owns data/lifecycle)
2. Add or reuse **supplier application contract** (read or command)
3. Add **consumer port** if the consumer must not depend on supplier's contract directly
4. Implement bridge in `app/Integrations/{Consumer}/`
5. Register binding in `IntegrationServiceProvider::register()` only
6. Add or extend architecture test for the edge (copy `RequestConsumerBoundaryTest.php` pattern)
7. Update `.specify/docs/context-map.md` if relationship is new

### Adding a new module

Follow [boundary-rules.md](./boundary-rules.md) § "Adding a new module" — critical step is adding to `architectureModuleNames()` **before merge** so the matrix applies from day one.

### Changing a contract

- Treat as breaking change across bounded contexts
- Update all bridges and adapters in the same PR
- Run full architecture + affected feature tests
- Do not change contract + bridge in separate PRs (CI will pass individually but runtime breaks)

### CI commands (Definition of Done alignment)

```bash
php artisan test tests/Architecture/
composer run phpstan
composer run pint
```

Architecture regressions block merge — same priority as failing feature tests.

### When to update this record

Update `decision-record.md` when:

- A new integration pattern is adopted (e.g. all legacy adapters migrated to Integrations)
- `architectureModuleNames()` changes
- A new enforcement test suite is added
- Constitution or context-map boundary decisions change (CD-015, CD-016, CD-017, etc.)

Do **not** update for individual feature PRs — those belong in spec handoff docs under `.specify/docs/handoff/`.

---

## References

| Document | Path |
|----------|------|
| Boundary rules | [boundary-rules.md](./boundary-rules.md) |
| Integration policy | [integration-layer-policy.md](./integration-layer-policy.md) |
| PR checklist | [pr-review-checklist.md](./pr-review-checklist.md) |
| Architecture test inventory | `tests/Architecture/architecture.php` |
| Context map | `.specify/docs/context-map.md` |
| Catalog decisions | `.specify/docs/catalog-decisions.md` |
| Constitution | `.specify/memory/constitution.md` |
| Project commands | `CLAUDE.md`, `AGENTS.md` |

---

## Summary

DormSys modular architecture is **enforced**, not documented-only. Modules communicate through **application contracts**; **Integrations** wire consumers to suppliers; **IntegrationServiceProvider** owns cross-module composition. Domain stays pure; Infrastructure stays module-local. CI architecture tests exist to catch regressions before review — use the [PR checklist](./pr-review-checklist.md) to align human review with automated gates.
