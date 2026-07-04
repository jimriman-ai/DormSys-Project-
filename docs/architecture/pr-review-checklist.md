# Architecture PR Review Checklist

**Status:** Approved — PR-level governance (2026-07-04)  
**Audience:** Reviewers (author attestation: `.github/pull_request_template.md`)  
**Mandatory CI:** `composer run arch` · **Full suite:** `php artisan test`  
**Docs:** [boundary-rules.md](./boundary-rules.md) · [ci-enforcement-matrix.md](./ci-enforcement-matrix.md) · [known-exceptions-registry.md](./known-exceptions-registry.md)

---

## Reviewer quick commands

Run from repo root after checking out the PR branch.

```bash
# Mandatory merge gate (matches CI)
composer run arch

# Static scan only (fast pre-check)
composer run arch:scan

# Non-blocking debt visibility
composer run arch:advisory

# PHPStan
composer run phpstan
```

```bash
# Integration ports must NOT bind in module providers
rg "singleton\((ApprovedRequestReadPort|AllocationAssignmentReadPort|RequestEligibilityGatewayContract|PendingRequestReadPort|ProposedAllocationPort)" app/Modules/

# New cross-module Application imports in adapter folders (expect legacy paths only)
rg "use App\\Modules\\[A-Za-z]+\\Application\\" app/Modules/*/Application/Adapters app/Modules/*/Infrastructure/Adapters

# Foreign Domain in matrix Application layers (expect no matches)
rg "use App\\Modules\\(Identity|Employee|Request|Workflow|Dormitory|Allocation|Lottery|Voucher|Notification|Audit|Reporting)\\Domain\\" app/Modules/*/Application --glob '!**/CheckIn/**'

# Domain purity spot-check (replace {Module})
rg "Infrastructure|Illuminate\\Database|Eloquent|Facades|@extends State<" app/Modules/{Module}/Domain/

# Composition root order
rg "IntegrationServiceProvider" bootstrap/providers.php
```

```bash
# Targeted architecture tests (examples)
php -d memory_limit=512M vendor/bin/pest tests/Architecture/IntegrationCompositionRootTest.php
php -d memory_limit=512M vendor/bin/pest tests/Architecture/CrossModuleAdapterLocationTest.php
php -d memory_limit=512M vendor/bin/pest tests/Architecture/ModuleInventoryParityTest.php
```

---

## Verdict tiers

| Tier | Meaning | Reviewer action |
|------|---------|-----------------|
| **Merge-blocking** | Violates enforced boundary or CI red | Request changes; do not merge |
| **Architecture approval required** | Allowed only with explicit approval + registry/doc update | Block until approver signs off |
| **Allowed under policy** | Matches approved model; CI green | Approve architecture slice |

**Default:** if unsure, treat as **architecture approval required**.

---

## Merge decision matrix

| Finding | Tier | CI | Reviewer check |
|---------|------|-----|----------------|
| `composer run arch` fails | **Merge-blocking** | Yes | Confirm CI log; reject until green |
| Foreign Domain import in matrix Application | **Merge-blocking** | Yes | `ModuleBoundaryTest` / static scan |
| Application → Infrastructure import | **Merge-blocking** | Yes | `LayerDependencyTest` |
| Domain → Eloquent / Infrastructure / Facades | **Merge-blocking** | Yes | `LayerDependencyTest` |
| Integration port bound in module provider | **Merge-blocking** | Yes | `IntegrationCompositionRootTest` |
| New cross-module adapter outside Integrations (not in legacy registry) | **Merge-blocking** | Yes | `CrossModuleAdapterLocationTest` |
| Undocumented active module (bootstrap without matrix/exclusion entry) | **Merge-blocking** | Yes | `ModuleInventoryParityTest` |
| New CheckIn foreign Domain import beyond allowlist | **Merge-blocking** | Yes | `ModuleInventoryParityTest` |
| Copy of Lottery `RequestReadAdapter` pattern | **Merge-blocking** | Yes / POLICY | Reject even if CI missed |
| Business logic in `app/Integrations/*` | **Merge-blocking** | POLICY | Reviewer reads bridge diff |
| Cross-module Eloquent / FK in migrations | **Merge-blocking** | POLICY | Constitution rule |
| New cross-module bridge in `app/Integrations/` + `IntegrationServiceProvider` | **Allowed** | Partial | Verify thin delegation; consumer owns port |
| New Application contract used only inside one module | **Allowed** | No | Standard module PR |
| Own-module Infrastructure binding in own provider | **Allowed** | Partial | No foreign ports |
| Touch existing legacy adapter (bugfix, no new edge) | **Allowed** | Yes | Must not expand coupling |
| Add entry to legacy exception registry | **Approval required** | Yes after merge | See exception flow below |
| Add module to `architectureModuleNames()` | **Approval required** | Yes after merge | Matrix rules apply to new module |
| Add module to `architectureMatrixExcludedActiveModules()` | **Approval required** | Yes | Must document debt + allowlist |
| New bounded-context relationship | **Approval required** | No | Update context map + decision record |
| Foreign Domain on new public contract surface | **Approval required** | No | Prefer string IDs / DTOs |
| Reporting↔Audit-style Infrastructure adapter (new file) | **Approval required** | Would fail CI | Do not approve without registry change |
| CheckIn full matrix enrollment | **Approval required** | Yes | Only after `UserId` debt closed |

---

## Decision triggers

Use when the PR attestation boxes are checked or diff touches these paths.

### 1. New cross-module bridge

**Paths:** `app/Integrations/**/*.php`, consumer `Application/Contracts/**/Port*.php`

| Check | Pass |
|-------|------|
| Consumer module owns the port interface | Required |
| Class under `app/Integrations/{Consumer}/`, `final`, single port | Required |
| Depends on supplier **Application** contracts/DTOs only | Required |
| Mapping only — no business rules | Required |
| Binding in `IntegrationServiceProvider::register()` only | Required |
| Not duplicated in module provider | Required |
| Edge test added/updated if stable | Recommended |

**Reject if:** mirrors `Lottery/Application/Adapters/RequestReadAdapter.php` or adds Infrastructure imports.

**Docs:** [integration-layer-policy.md](./integration-layer-policy.md)

---

### 2. New Application contract (cross-module)

**Paths:** `app/Modules/*/Application/Contracts/**/*.php` with new `use App\Modules\{Other}\...` consumers

| Check | Pass |
|-------|------|
| Contract lives in **supplier** or **consumer** per port-ownership rule | Required |
| Surface uses strings/DTOs — not foreign Domain VOs | Required |
| No foreign Infrastructure in interface | Required |
| Documented in context map if new relationship | If new edge |

**Approval required if:** contract exposes foreign Domain enums/VOs (same class of debt as `RequestId`, `UserId`).

**Allowed if:** contract is own-module only or consumed via Integrations bridge without Domain leakage.

---

### 3. Composition root change

**Paths:** `app/Providers/IntegrationServiceProvider.php`, `app/Modules/*/Infrastructure/Providers/*ServiceProvider.php`, `bootstrap/providers.php`

| Check | Pass |
|-------|------|
| Integration ports bind only in `IntegrationServiceProvider` | Required |
| `IntegrationServiceProvider` remains **last** in bootstrap | Required |
| Bindings in `register()`, not `boot()` | Required |
| Module providers bind own-module abstractions only | Required |
| No new binding for legacy ports outside approved files | Required |

**Legacy bindings (do not copy):**

- `LotteryRequestReadPort` → `LotteryServiceProvider` only
- `AuditPermissionReadPort` → `IdentityServiceProvider` only

**CI:** `IntegrationCompositionRootTest.php`

---

### 4. Module inventory expansion

**Paths:** `tests/Architecture/architecture.php`, new `app/Modules/{Name}/`, `bootstrap/providers.php`

| Scenario | Tier |
|----------|------|
| New module added to `architectureModuleNames()` | **Approval required** — full matrix applies |
| Active module added to `architectureMatrixExcludedActiveModules()` | **Approval required** — must include debt plan + allowlists |
| Bootstrap provider added without inventory update | **Merge-blocking** once CI runs |

**CI:** `ModuleInventoryParityTest.php`, `ServiceProviderRegistrationTest.php`

---

### 5. Tolerated legacy exception request

**Paths:** `architectureLegacyCrossModuleAdapterPaths()`, `architectureLegacyModuleProviderPortBindings()`, `architectureCheckInForeignDomainImportAllowlist()`

**Do not merge without all of:**

1. Written architecture approval (linked in PR)
2. Entry added to [known-exceptions-registry.md](./known-exceptions-registry.md)
3. Matching function update in `tests/Architecture/architecture.php`
4. Note in [decision-record.md](./decision-record.md)
5. `composer run arch` green **with** the new registry entry

**Default answer:** **Reject** — migrate to Integrations instead of expanding exceptions.

---

## Exception handling flow

```
PR adds cross-module coupling
        │
        ▼
  Uses app/Integrations/ + IntegrationServiceProvider?
        │
   YES ─┴─ NO
   │        │
   │        ▼
   │   Matches existing legacy file (bugfix only)?
   │        │
   │   YES ─┴─ NO
   │   │        │
   │   │        ▼
   │   │   CI green without registry change?
   │   │        │
   │   │   YES ─┴─ NO → MERGE-BLOCKING (fix or Integrations)
   │   │   │
   │   │   └──► Still copies legacy pattern for NEW edge?
   │   │              YES → REJECT (approval required)
   │   │              NO  → Allowed (existing debt touch)
   │   │
   │   └──► Allowed under policy
   │
   └──► Thin bridge + CI green?
              YES → Allowed
              NO  → Request changes
```

**Expanding the exception registry** always requires architecture approval — never rubber-stamp because CI was made to pass.

---

## Layer spot-checks (when CI is green but diff is suspicious)

### Domain

- No `Illuminate\Database\Eloquent`, `Infrastructure`, `Facades`
- No foreign `App\Modules\{Other}\*`
- No `@extends State<Model>`

### Application

- Injects ports/contracts, not concrete repos
- Foreign access: `{Other}\Application\Contracts` or DTOs only
- **Never approve** new `OperatorRoleGate` → `UserId` copies

### Infrastructure

- No new cross-module adapters outside Integrations (unless legacy registry)
- Reporting→Audit `Application\Contracts` in Infrastructure is **legacy only** — no new files

---

## Legacy patterns — never copy for new work

| Pattern | Location | Why blocked |
|---------|----------|-------------|
| Lottery read adapter | `Lottery/Application/Adapters/RequestReadAdapter.php` | Pre-Integrations; CI closes this folder |
| Reporting audit adapters | `Reporting/Infrastructure/Adapters/AuditHistory*.php` | Legacy projection wiring |
| Identity audit permission | `Identity/Infrastructure/Adapters/SpatieAuditPermissionReadAdapter.php` | Inverted supplier placement |
| CheckIn UserId import | `CheckIn/.../OperatorRoleGate.php` | Frozen debt — one import only |

Full list: [known-exceptions-registry.md](./known-exceptions-registry.md)

---

## Review outcome template

Copy into PR comment:

```markdown
### Architecture review

**Verdict:** Approve / Request changes / Architecture approval required

**CI:** `composer run arch` — pass / fail

| Area | Result |
|------|--------|
| Domain purity | pass / fail / n/a |
| Application contracts | pass / fail / n/a |
| Cross-module edges | pass / fail / n/a |
| Composition root | pass / fail / n/a |
| Integration layer | pass / fail / n/a |
| Exception registry | none / valid / rejected |

**Triggers:** <!-- bridge / contract / composition root / inventory / exception -->

**Notes:**
```

---

## Related documents

- [boundary-rules.md](./boundary-rules.md)
- [integration-layer-policy.md](./integration-layer-policy.md)
- [ci-enforcement-matrix.md](./ci-enforcement-matrix.md)
- [known-exceptions-registry.md](./known-exceptions-registry.md)
- [decision-record.md](./decision-record.md)
