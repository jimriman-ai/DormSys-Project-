# Known Architecture Exceptions Registry

**Status:** Approved — first mandatory batch (2026-07-04)  
**Purpose:** Keep tolerated legacy debt **visible** while CI blocks **new** regressions.

CI reads this registry through `tests/Architecture/architecture.php`.  
**Do not add entries casually** — each path here permits a pattern that would otherwise fail mandatory checks.

---

## 1. Matrix-excluded active modules

| Module | Reason | Mandatory CI behavior | Exit criteria |
|--------|--------|----------------------|---------------|
| **CheckIn** | Active in bootstrap; `OperatorRoleGate` still imports Identity `UserId` | Included in inventory parity; **foreign Domain imports frozen** via allowlist | Change `UserRepositoryContract` to string IDs; remove allowlist entry; add `CheckIn` to `architectureModuleNames()` |

Functions: `architectureMatrixExcludedActiveModules()`, `architectureCheckInForeignDomainImportAllowlist()`

---

## 2. Legacy cross-module adapters (outside `app/Integrations/`)

| Path | Edge | Binding location | Copy for new work? |
|------|------|------------------|--------------------|
| `app/Modules/Lottery/Application/Adapters/RequestReadAdapter.php` | Lottery → Request read | `LotteryServiceProvider` | **No** |
| `app/Modules/Reporting/Infrastructure/Adapters/AuditHistorySourceReadAdapter.php` | Reporting → Audit history | `ReportingServiceProvider` | **No** |
| `app/Modules/Reporting/Infrastructure/Adapters/ReportingArchiveVisibilityAdapter.php` | Reporting → Audit permissions | `ReportingServiceProvider` | **No** |
| `app/Modules/Identity/Infrastructure/Adapters/SpatieAuditPermissionReadAdapter.php` | Identity → Audit permission port | `IdentityServiceProvider` | **No** |

Function: `architectureLegacyCrossModuleAdapterPaths()`

**Mandatory rule:** new cross-module adapters **not** in this list and **not** under `app/Integrations/` → **CI failure**.

---

## 3. Legacy port bindings outside composition root

| Port | Approved provider file |
|------|------------------------|
| `LotteryRequestReadPort` | `app/Modules/Lottery/Infrastructure/Providers/LotteryServiceProvider.php` |
| `AuditPermissionReadPort` | `app/Modules/Identity/Infrastructure/Providers/IdentityServiceProvider.php` |

Function: `architectureLegacyModuleProviderPortBindings()`

**Mandatory rule:** the five integration ports in `architectureIntegrationPortClasses()` must bind **only** in `IntegrationServiceProvider`.

---

## 4. CheckIn Application foreign Domain allowlist (open debt)

| File | Allowed import | Debt |
|------|----------------|------|
| `app/Modules/CheckIn/Application/Services/OperatorRoleGate.php` | `App\Modules\Identity\Domain\ValueObjects\UserId` | Required by `UserRepositoryContract::userHasRole(UserId, …)` |

Function: `architectureCheckInForeignDomainImportAllowlist()`

**Mandatory rule:** any **additional** CheckIn Application foreign Domain import → **CI failure**.

---

## 5. Contract-level Domain leakage (advisory — not in exception registry)

These pass CI today but remain tracked debt:

| Contract | Leaked type | Consumer impact |
|----------|-------------|-----------------|
| `RequestReadContract` | `RequestId` | `ApprovedRequestReadBridge` imports Request Domain |
| `UserRepositoryContract` | `UserId` | Forces CheckIn debt above |

**Enforcement:** advisory review + future contract refactor batch. **Not** silently normalized.

---

## 6. Patterns intentionally **not** in the exception registry

CI **will** fail if these appear:

- New `Application/Adapters/*` cross-module files (Lottery directory is closed)
- Duplicate bindings for integration ports in module providers
- Undocumented active module without matrix or exclusion entry
- New CheckIn foreign Domain imports beyond the single allowlisted `UserId`
- Domain Eloquent / Infrastructure / Facade imports
- Matrix Application → foreign Domain imports

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
