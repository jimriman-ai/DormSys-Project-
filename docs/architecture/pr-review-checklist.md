# Architecture PR Review Checklist

**Status:** Approved  
**Use when:** PR touches `app/Modules/`, `app/Integrations/`, `app/Providers/`, or `bootstrap/providers.php`  
**CI gate:** `php artisan test tests/Architecture/` (751 tests)

Legend: **ENFORCED** = CI fails on violation. **POLICY** = reviewer must catch manually.

---

## Immediate reject criteria

Reject or request changes if the PR:

- [ ] Adds foreign **Domain** or **Infrastructure** imports to another module's Application layer (**ENFORCED** for matrix modules)
- [ ] Adds Eloquent, Facades, or Infrastructure imports to Domain (**ENFORCED**)
- [ ] Injects concrete Infrastructure classes into Application services (**ENFORCED**)
- [ ] Adds cross-module repository or Eloquent access (**POLICY** + usually **ENFORCED** via Infrastructure isolation)
- [ ] Binds a cross-module port in a module `*ServiceProvider` instead of `IntegrationServiceProvider` (**POLICY**)
- [ ] Duplicates a singleton binding already in `IntegrationServiceProvider` (**POLICY**)
- [ ] Adds business logic to `app/Integrations/*` (**POLICY**)
- [ ] Fails `tests/Architecture/` or PHPStan level 8

---

## 1. Layer purity questions

**Domain** (`app/Modules/*/Domain/**/*.php`)

| Question | ENFORCED by |
|----------|-------------|
| Any `Illuminate\Database\Eloquent\*` import? | `LayerDependencyTest` — `domain layer does not depend on eloquent` |
| Any `App\Modules\*\Infrastructure\*` import? | `LayerDependencyTest` — `domain layer does not depend on infrastructure` |
| Any `Illuminate\Support\Facades\*` import? | `LayerDependencyTest` — `domain layer does not depend on laravel facades` |
| Any foreign module import? | `ModuleBoundaryTest` — `{module} domain is isolated from {foreign}` |
| State classes reference Eloquent models in PHPDoc? | **POLICY** — post-repair: no `@extends State<Model>` |

**Application** (`app/Modules/*/Application/**/*.php`)

| Question | ENFORCED by |
|----------|-------------|
| Constructor injects `*Contract` / `*Port`, not concrete repos? | `LayerDependencyTest` — `application layer does not depend on infrastructure` |
| Any own-module Infrastructure import? | Same |
| Foreign access only via Application contracts/DTOs? | `ModuleBoundaryTest` — `application does not access {foreign} domain/infrastructure/presentation` |
| Copying `OperatorRoleGate` → `UserId` pattern? | **POLICY** — known gap; reject new copies |

**Infrastructure** (`app/Modules/*/Infrastructure/**/*.php`)

| Question | ENFORCED by |
|----------|-------------|
| Repositories implement Application contracts? | **POLICY** |
| Foreign module imports (any layer)? | `ModuleBoundaryTest` — `{module} infrastructure is isolated from {foreign}` |
| New cross-module adapter outside Integrations? | **POLICY** — reject unless legacy exception documented |

---

## 2. Cross-module edge questions

For every new `use App\Modules\{Other}\...`:

| Import path | Verdict |
|-------------|---------|
| `{Other}\Application\Contracts\*` | Usually OK — verify port ownership |
| `{Other}\Application\DTOs\*` | OK for read/projection flows |
| `{Other}\Domain\*` | **Reject** (except Integrations edge cases tied to existing contracts) |
| `{Other}\Infrastructure\*` | **Reject** |

**New cross-module feature checklist:**

- [ ] Consumer owns the port interface
- [ ] Bridge in `app/Integrations/{Consumer}/` (not legacy pattern)
- [ ] Binding only in `IntegrationServiceProvider::register()`
- [ ] Module-specific arch test added/updated if edge is stable (see table below)

| Edge | Arch test file |
|------|----------------|
| Request ↔ Employee | `RequestConsumerBoundaryTest.php` |
| Allocation ↔ suppliers | `AllocationBoundaryTest.php` |
| Lottery ↔ Request | `LotterySupplierBoundaryTest.php` |
| Reporting ↔ Audit | `ReportingBoundaryTest.php` |
| CheckIn ↔ Allocation | `CheckInBoundaryTest.php` (partial) |
| All matrix modules | `ModuleBoundaryTest.php` |

---

## 3. Composition root questions

| Question | Expected state |
|----------|----------------|
| Cross-module ports bound only in `IntegrationServiceProvider`? | **POLICY** — 5 bindings today (see below) |
| `IntegrationServiceProvider` last in `bootstrap/providers.php`? | Line 46 |
| Bindings in `register()`, not `boot()`? | Post-cleanup standard |
| Module provider binds own implementations only? | e.g. `AllocationRepositoryContract → AllocationRepository` |
| New matrix module added to `architectureModuleNames()`? | **ENFORCED** via `ServiceProviderRegistrationTest` |

**Current `IntegrationServiceProvider` bindings (do not duplicate elsewhere):**

```
ApprovedRequestReadPort
AllocationAssignmentReadPort
RequestEligibilityGatewayContract
PendingRequestReadPort
ProposedAllocationPort
```

**Legacy bindings outside Integrations (do not copy):**

```
LotteryRequestReadPort          → LotteryServiceProvider (RequestReadAdapter)
AuditPermissionReadPort         → IdentityServiceProvider (SpatieAuditPermissionReadAdapter)
```

---

## 4. Provider binding drift checks

```bash
# Cross-module ports must NOT appear in module providers
rg "singleton\((ApprovedRequestReadPort|AllocationAssignmentReadPort|RequestEligibilityGatewayContract|PendingRequestReadPort|ProposedAllocationPort)" app/Modules/

# Expected: no matches
```

```bash
# Domain purity spot-check (replace {Module})
rg "Infrastructure|Illuminate\\\\Database|Eloquent|Facades" app/Modules/{Module}/Domain/

# Expected: no matches
```

```bash
# Full CI gate
php artisan test tests/Architecture/
composer run phpstan
```

---

## 5. Integration layer review

| Question | Pass criteria |
|----------|---------------|
| Bridge is `final` and implements one consumer port? | Yes |
| Dependencies are Application contracts / internal query ports? | No repositories/models |
| Only delegation + mapping? | No eligibility/scoring/approval rules |
| Registered in `IntegrationServiceProvider::register()`? | Yes |
| Read-only port exposes no extra public methods? | Match `RequestConsumerBoundaryTest` pattern |

---

## 6. When architecture approval is required (**POLICY**)

Escalate beyond normal PR review when the PR:

1. Adds a **new bounded-context relationship** not in `.specify/docs/context-map.md`
2. Introduces a **new cross-module port** without an Integrations bridge (and is not a documented legacy exception)
3. Adds a module to `architectureModuleNames()` (expect new matrix rules to apply)
4. Touches **CheckIn ↔ Identity** until `OperatorRoleGate` contract debt is resolved
5. Moves or splits **Reporting ↔ Audit** integration outside existing guarded adapters
6. Requires **foreign Domain types** in Application or Integrations (contract change needed)
7. Operates under a **governance authorization** scope (spec handoff) — verify authorization record allows the change

---

## Review outcome template

```markdown
### Architecture review

- [ ] Domain purity — pass / fail
- [ ] Application contracts — pass / fail
- [ ] Cross-module edges — pass / fail
- [ ] Composition root — pass / fail
- [ ] Integration layer — pass / fail / N/A
- [ ] tests/Architecture — pass / fail

**Notes:**
```

---

## Related documents

- [boundary-rules.md](./boundary-rules.md)
- [integration-layer-policy.md](./integration-layer-policy.md)
- [decision-record.md](./decision-record.md)
