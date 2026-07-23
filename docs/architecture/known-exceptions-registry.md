# Known Architecture Exceptions Registry

**Status:** Approved — updated after Audit/Reporting Integration debt closure (2026-07-12)  
**Purpose:** Keep tolerated legacy debt **visible** while CI blocks **new** regressions.

CI reads this registry through `tests/Architecture/architecture.php`.  
**Do not add entries casually** — each path here permits a pattern that would otherwise fail mandatory checks.

---

## 1. Matrix-excluded active modules

None. `architectureMatrixExcludedActiveModules()` returns an empty list.

CheckIn was enrolled in the full matrix after `OperatorRoleGate` migrated to `IdentityUserReadContract::userHasRole()`.

---

## 2. Legacy cross-module adapters (outside `app/Integrations/`)

| Path | Edge | Binding location | Copy for new work? |
|------|------|------------------|--------------------|
| `app/Modules/Lottery/Application/Adapters/RequestReadAdapter.php` | Lottery → Request read | `LotteryServiceProvider` | **No** |

Function: `architectureLegacyCrossModuleAdapterPaths()`

**Closed (moved to Integrations, 2026-07-12):**

| Former path | Now |
|-------------|-----|
| `Reporting/.../AuditHistorySourceReadAdapter.php` | `app/Integrations/Reporting/AuditHistorySourceReadBridge.php` |
| `Reporting/.../ReportingArchiveVisibilityAdapter.php` | `app/Integrations/Reporting/ReportingArchiveVisibilityBridge.php` |
| `Identity/.../SpatieAuditPermissionReadAdapter.php` | `app/Integrations/Audit/SpatieAuditPermissionReadBridge.php` |

**Mandatory rule:** new cross-module adapters **not** in this list and **not** under `app/Integrations/` → **CI failure**.

---

## 3. Legacy port bindings outside composition root

| Port | Approved provider file |
|------|------------------------|
| `LotteryRequestReadPort` | `app/Modules/Lottery/Infrastructure/Providers/LotteryServiceProvider.php` |

Function: `architectureLegacyModuleProviderPortBindings()`

**Closed:** `AuditPermissionReadPort` now binds only in `IntegrationServiceProvider`.

**Mandatory rule:** the integration ports in `architectureIntegrationPortClasses()` must bind **only** in `IntegrationServiceProvider`.

---

## 4. Contract-level Domain leakage (advisory — not in exception registry)

These pass CI today but remain tracked debt:

| Contract | Leaked type | Consumer impact |
|----------|-------------|-----------------|
| `RequestReadContract` | `RequestId` | `ApprovedRequestReadBridge` imports Request Domain |

**Enforcement:** advisory review + future contract refactor batch. **Not** silently normalized.

---

## 5. DP-XMOD-BELONGS Option C — Persistence Model allowlist (guard)

**Status:** CLOSED decision encoded in Architecture Guard (ARCH-GUARD-ALLOWLIST-01).

Persistence-level read `belongsTo` across modules is **allowed**. Pest arch rules use
`architectureOptionCForeignPersistenceModelAllowlist()` via `->ignoring(...)` so foreign
`…\Infrastructure\Persistence\Models` imports do not fail Infrastructure isolation rules.

**Not allowed by this entry:** using those relations (or other cross-module Eloquent) inside
workflow / authorization / mutation paths; non-Persistence Infrastructure importing foreign
Domain/Application (e.g. unregistered adapters — separate cluster).

Function: `architectureOptionCForeignPersistenceModelAllowlist()` in `tests/Architecture/architecture.php`.

---

## 6. Patterns intentionally **not** in the exception registry

CI **will** fail if these appear:

- New `Application/Adapters/*` cross-module files (Lottery directory is closed)
- Duplicate bindings for integration ports in module providers
- Undocumented active module without matrix entry
- Foreign Domain imports in any matrix module Application layer
- Domain Eloquent / Infrastructure / Facade imports

---

## Updating this registry

1. Get architecture approval for the debt or legacy retention.
2. Add the entry to the matching function in `tests/Architecture/architecture.php`.
3. Document rationale in [decision-record.md](./decision-record.md).
4. Prefer **debt closure** over registry expansion.

---

## Related documents

- [ci-enforcement-matrix.md](./ci-enforcement-matrix.md)
- [integration-layer-policy.md](./integration-layer-policy.md)
- [decision-record.md](./decision-record.md)
