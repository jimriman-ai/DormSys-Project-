# Contract: Employee ↔ Request Boundary

**Version:** 1.0.0  
**Spec:** spec05 Request Management  
**Implements:** CD-013, OA-05-08, OA-05-09  
**Status:** Implemented — T047–T048 per [`tasks.md`](../tasks.md)

---

## Purpose

Documents the **bidirectional but asymmetric** integration between Employee (upstream) and Request (downstream) bounded contexts.

- **Inbound to Request:** `EmployeeEligibilityContract` (compute)
- **Outbound from Request:** `PendingRequestReadPort` adapter (read-only pull)

---

## CD-013 split

| Responsibility | Owner | Mechanism |
| -------------- | ----- | --------- |
| Eligibility computation | **Employee** | `EmployeeEligibilityContract::computeRequestEligibility` |
| Eligibility enforcement at submit | **Request** | `SubmitRequestAction` calls contract + local date rules |
| `employee_id` storage | **Request** | UUID column, no FK |

```
Request (submit)  ──calls──►  EmployeeEligibilityContract
                                    │
                                    ├── ActiveAllocationReadPort (stub → spec07)
                                    └── PendingRequestReadPort ◄── adapter in Request
```

---

## PendingRequestReadPort — read-only adapter (OA-05-09)

**Interface owner:** Employee module  
`App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort`

**Implementation owner:** Request module  
`App\Modules\Request\Infrastructure\Adapters\PendingRequestReadAdapter`

### Normative constraint

```text
PendingRequestReadPort is a read-only pull contract.
It exposes Request status information required for eligibility checks only.
It MUST NOT expose Request commands or lifecycle mutation operations.
Request remains the sole owner of Request lifecycle state.

PendingRequestReadPort is a query-only port.
It must never become a command boundary.
```

| Allowed | Prohibited |
| ------- | ---------- |
| `hasPendingRequest(EmployeeId): bool` | `CancelRequest`, `ApproveRequest`, `ChangeRequestState`, or any command |
| Query non-terminal requests per [research.md R-04](../research.md#r-04--pending-request-definition-br-01--pendingrequestreadport) | Employee writing to `request_*` tables |
| Adapter registered from Request module | Exposing Request Application mutation services through port |

### Interface (defined in spec03)

```php
namespace App\Modules\Employee\Application\Contracts\Ports;

use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

interface PendingRequestReadPort
{
    public function hasPendingRequest(EmployeeId $employeeId): bool;
}
```

### Adapter implementation (planned)

```php
namespace App\Modules\Request\Infrastructure\Adapters;

use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Request\Application\Contracts\Internal\PendingRequestQueryPort;

final class PendingRequestReadAdapter implements PendingRequestReadPort
{
    public function __construct(
        private readonly PendingRequestQueryPort $queries,
    ) {}

    public function hasPendingRequest(EmployeeId $employeeId): bool
    {
        return $this->queries->hasNonTerminalRequest($employeeId);
    }
}
```

**Note:** `PendingRequestQueryPort` is **internal** to Request module — not a public cross-module API.

### Binding coordination

When spec05 implementation ships:

1. Request module registers `PendingRequestReadAdapter` binding for `PendingRequestReadPort`
2. Replaces Employee `NullPendingRequestReadAdapter` in integration environment
3. Documented in spec05 implementation handoff — **no spec03 artifact edit required at design time**

---

## Dependent snapshots (CD-009) — FamilyDirect only

Request **does not** call Employee Dependent repositories directly.

**Planned inbound (Wave 1B):** `DependentSnapshotSourceContract` (Employee supplier — when US3 live) or test fixtures.

| Rule | Detail |
| ---- | ------ |
| Capture | Immutable rows in `request_dependent_snapshots` |
| `source_dependent_id` | Optional trace UUID — **no FK** |
| Ownership | Employee owns Dependent lifecycle |

---

## Architecture tests (future)

| ID | Assertion |
| -- | --------- |
| BT-R05 | Request module does not import Employee/Dormitory Infrastructure persistence |
| BT-R09 | `PendingRequestReadPort` implementation has no methods beyond interface |
| BT-R08 | Pending query returns true when non-terminal request exists |

---

## Related

- [../003-employee-context/contracts/employee-eligibility-service.md](../../003-employee-context/contracts/employee-eligibility-service.md)
- [../003-employee-context/contracts/internal-read-ports.md](../../003-employee-context/contracts/internal-read-ports.md)
- [request-eligibility-enforcement.md](./request-eligibility-enforcement.md)
- [data-model.md](../data-model.md) § PendingRequest
