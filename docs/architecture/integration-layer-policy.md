# Integration Layer Policy

**Status:** Approved  
**Location:** `app/Integrations/`  
**Composition root:** `app/Providers/IntegrationServiceProvider.php`  
**Registration:** `IntegrationServiceProvider::register()` — provider must remain **last** in `bootstrap/providers.php` (line 46)

**ENFORCED indirectly:** cross-module ports bound here are exercised by feature tests; bridge shape for Employee↔Request is **ENFORCED** by `RequestConsumerBoundaryTest.php` (method parity with `PendingRequestReadPort`).

---

## Why `app/Integrations` exists

Modules must not wire each other's Infrastructure or Domain. When module **A** defines a port and module **B** exposes an Application contract, the **implementation of A's port** that calls B belongs outside both modules — in `app/Integrations/`.

```
Consumer module (port owner)     Integrations (bridge)        Supplier module (contract owner)
────────────────────────────     ─────────────────────        ──────────────────────────────
Employee\PendingRequestReadPort  PendingRequestReadBridge     Request\PendingRequestQueryPort
Allocation\ApprovedRequestReadPort ApprovedRequestReadBridge  Request\RequestReadContract
CheckIn\AllocationAssignmentReadPort AllocationAssignmentReadBridge Allocation\AllocationReadContract
Request\RequestEligibilityGatewayContract EmployeeEligibilityBridge Employee\EmployeeEligibilityContract
```

---

## When a bridge belongs here (**POLICY**)

Create `app/Integrations/{ConsumerContext}/{Name}Bridge.php` when **all** apply:

1. Consumer module owns the port interface in `Application/Contracts/` (or `Ports/`).
2. Supplier exposes an existing Application contract, DTO, or consumer-internal query port.
3. The edge crosses bounded contexts (see `.specify/docs/context-map.md`).
4. Binding the implementation inside either module's `*ServiceProvider` would hide a cross-module edge.

**Do not** add a bridge when:

- Dependency stays inside one module (use `Infrastructure/Adapters/` within that module).
- Consumer can inject the foreign Application contract directly with no adapter logic (see legacy Lottery case below).
- No port interface exists yet — define the port in the consumer first.

---

## What a bridge may depend on (**POLICY**)

| Allowed | Example in repo |
|---------|-----------------|
| Consumer port interface | `implements PendingRequestReadPort` |
| Supplier Application contracts | `RequestReadContract`, `AllocationReadContract` |
| Consumer-internal query ports | `PendingRequestQueryPort` (Request-internal, wired from Request module) |
| Supplier Application DTOs | `RequestSummaryDTO`, `EligibilityResultDTO` |
| Simple mapping / filtering | `ApprovedRequestReadBridge`: `status === 'approved'` |

---

## What a bridge must not contain (**POLICY**)

| Forbidden | Reason |
|-----------|--------|
| Domain business rules | Belongs in consumer/supplier Domain or Application services |
| Eloquent models / repositories | Infrastructure leak |
| `use App\Modules\{X}\Infrastructure\*` | Cross-module Infrastructure |
| Direct SQL, HTTP, queue dispatch | Infrastructure concerns |
| Command/mutation on read-only ports | OA-05-09: `PendingRequestReadPort` is query-only |

**Prefer** Application contracts with `string` IDs over foreign Domain value objects. **Known exception:** `ApprovedRequestReadBridge` uses `RequestId::fromString()` because `RequestReadContract::getRequestSummary(RequestId $id)` requires it today.

---

## Provider registration (**POLICY** — post-cleanup standard)

All approved cross-module port bindings live in **`IntegrationServiceProvider::register()`**:

```php
// app/Providers/IntegrationServiceProvider.php (current)
ApprovedRequestReadPort::class          → ApprovedRequestReadBridge::class
AllocationAssignmentReadPort::class     → AllocationAssignmentReadBridge::class
RequestEligibilityGatewayContract::class → EmployeeEligibilityBridge::class
PendingRequestReadPort::class           → PendingRequestReadBridge::class
ProposedAllocationPort::class           → ProposedAllocationConsumer::class
DormitoryReadContract::class            → DormitoryReadBridge::class
```

**Rules:**

- One binding per port — **no** duplicate registration in module providers.
- Module providers bind **own-module** abstractions only (+ null stubs for undeferred suppliers).
- `IntegrationServiceProvider` is last in `bootstrap/providers.php`.

**Removed (post-cleanup):** `EmployeeServiceProvider` no longer binds `PendingRequestReadPort`. `NullPendingRequestReadAdapter` deleted — production path is the bridge only.

---

## Current bridges (approved)

### `Integrations/Request/DormitoryReadBridge.php`

- **Implements:** `Request\...\DormitoryReadContract`
- **Depends on:** `Dormitory\...\DormitoryStructureReadContract`
- **Behavior:** `siteExists()` via `getDormitoryDetail() !== null`
- **Binding:** `IntegrationServiceProvider` only

### `Integrations/Request/PendingRequestReadBridge.php`

- **Implements:** `App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort`
- **Depends on:** `App\Modules\Request\Application\Contracts\Internal\PendingRequestQueryPort`
- **Behavior:** delegates `hasPendingRequest()` → `hasNonTerminalRequest()`
- **ENFORCED:** public methods must match port exactly — `RequestConsumerBoundaryTest.php`

### `Integrations/Request/EmployeeEligibilityBridge.php`

- **Implements:** `Request\...\RequestEligibilityGatewayContract`
- **Depends on:** `Employee\...\EmployeeEligibilityContract`
- **Behavior:** pure delegation

### `Integrations/Allocation/ApprovedRequestReadBridge.php`

- **Implements:** `Allocation\...\ApprovedRequestReadPort`
- **Depends on:** `Request\...\RequestReadContract`, filters approved status
- **Note:** uses `Request\Domain\ValueObjects\RequestId` (contract-required; see known debt)

### `Integrations/CheckIn/AllocationAssignmentReadBridge.php`

- **Implements:** `CheckIn\...\AllocationAssignmentReadPort`
- **Depends on:** `Allocation\...\AllocationReadContract` (string IDs via `getAllocationSummary()`)
- **Post-repair pattern:** no foreign Domain types

### `ProposedAllocationConsumer` (not under Integrations/)

- **Bound as:** `ProposedAllocationPort` → `ProposedAllocationConsumer`
- **Location:** `app/Modules/Allocation/Application/Services/ProposedAllocationConsumer.php`
- **Rationale:** consumer-side command handler, not a translation bridge; wiring still centralized in `IntegrationServiceProvider`

---

## Legacy tolerated patterns — do not copy for new work

These exist, pass CI, and are documented in boundary tests. **New cross-module edges must not follow them** without explicit architecture approval.

| Pattern | Location | Binding | Why legacy |
|---------|----------|---------|------------|
| Lottery → Request read adapter | `Lottery/Application/Adapters/RequestReadAdapter.php` | `LotteryServiceProvider:58` → `LotteryRequestReadPort` | Pre-Integrations; uses foreign Application contract only |

**Closed (2026-07-12) — now under Integrations:**

| Former pattern | Bridge |
|----------------|--------|
| Reporting → Audit history | `app/Integrations/Reporting/AuditHistorySourceReadBridge.php` |
| Reporting → Audit permissions | `app/Integrations/Reporting/ReportingArchiveVisibilityBridge.php` |
| Identity implements Audit permission port | `app/Integrations/Audit/SpatieAuditPermissionReadBridge.php` |

---

## New bridge PR checklist

- [ ] Consumer port exists in consumer `Application/Contracts/`
- [ ] Class under `app/Integrations/{Consumer}/`, `final`, single port
- [ ] Dependencies are Application contracts / DTOs only
- [ ] No business branching beyond mapping/filtering
- [ ] Binding added to `IntegrationServiceProvider::register()` only
- [ ] No duplicate binding in module providers
- [ ] `php artisan test tests/Architecture/` passes
- [ ] Read-only ports: add/update reflection test like `RequestConsumerBoundaryTest.php`

---

## Related documents

- [boundary-rules.md](./boundary-rules.md)
- [pr-review-checklist.md](./pr-review-checklist.md)
- [decision-record.md](./decision-record.md)
