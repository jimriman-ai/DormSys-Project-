# Contract: Request Read Service (supplier)

**Version:** 1.0.0  
**Spec:** spec05 Request Management  
**Implements:** spec.md FR-014, OA-05-06  
**Consumers:** spec06 Lottery, spec07 Allocation, spec11 Reporting  
**Status:** Phase 1 design — implementation not authorized

---

## Purpose

Defines a **read-only** cross-module API for downstream contexts to query accommodation request data without cross-module Eloquent queries (AP-04).

Request is the **supplier**; Lottery and Allocation **consume** approved request summaries — they do not own request lifecycle.

**Normative constraint:**

```text
RequestReadContract exposes read-only projections only.
Consumers cannot mutate Request lifecycle through this contract.
```

---

## Interface

**Namespace (planned):** `App\Modules\Request\Application\Contracts\RequestReadContract`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

use App\Modules\Request\Application\DTOs\RequestApprovalHistoryDTO;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Domain\ValueObjects\RequestId;

interface RequestReadContract
{
    public function requestExists(RequestId $id): bool;

    public function getRequestSummary(RequestId $id): ?RequestSummaryDTO;

    /**
     * @return list<RequestSummaryDTO>
     */
    public function listApprovedByEmployee(string $employeeId): array;

    /**
     * @return list<RequestSummaryDTO>
     */
    public function listApprovedByType(string $requestType): array;

    /**
     * @return list<RequestApprovalHistoryDTO>
     */
    public function getApprovalHistory(RequestId $id): array;
}
```

---

## DTOs

### `RequestSummaryDTO`

| Field | Type | Notes |
| ----- | ---- | ----- |
| `id` | `string` | UUID |
| `code` | `string` | `REQ-…` |
| `employeeId` | `string` | UUID — no FK exposure |
| `dormitoryId` | `string` | UUID |
| `type` | `string` | `personal`, `family_direct`, `mission`, `lottery_registration` |
| `status` | `string` | Approval-phase values only in spec05 |
| `checkInDate` | `string` | ISO date |
| `checkOutDate` | `string` | ISO date |
| `submittedAt` | `string`? | ISO-8601 UTC |
| `memberCount` | `int`? | Mission only |
| `dependentCount` | `int`? | FamilyDirect only |

### `RequestApprovalHistoryDTO`

| Field | Type | Notes |
| ----- | ---- | ----- |
| `stage` | `string` | |
| `decision` | `string` | |
| `approverId` | `string` | UUID |
| `reason` | `string`? | |
| `decidedAt` | `string` | ISO-8601 UTC |

---

## Implementation rules

| Rule | Detail |
| ---- | ------ |
| Implementation | `RequestReadService` in `Application/Services/` |
| Registration | Singleton in `RequestServiceProvider` |
| Consumer dependency | Inject `RequestReadContract` only — no `RequestModel` |
| Mutations | **None** — read only |
| Post-approval states | Not returned until spec07 adds columns/states |

---

## Error behavior

| Input | Behavior |
| ----- | -------- |
| Unknown `RequestId` | `getRequestSummary` → `null`; `getApprovalHistory` → `[]` |
| Malformed UUID | `RequestId::fromString` throws before service call |

---

## Testing requirements

| Test | Layer |
| ---- | ----- |
| Approved request → summary DTO | Feature |
| Unknown id → null | Feature |
| LotteryRegistration approved → listable by type | Feature |
| Allocation consumer depends only on interface | Architecture (BT-R05) |

---

## Related

- [data-model.md](../data-model.md)
- [employee-request-boundary.md](./employee-request-boundary.md)
- spec06 / spec07 planning (future consumers)
