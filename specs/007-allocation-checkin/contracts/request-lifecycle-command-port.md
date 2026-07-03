# Port: Request Lifecycle Command (inbound to Request)

**Version:** 1.0.0  
**Spec:** spec07 Allocation & Occupancy (producer) / spec05 Request (consumer)  
**Direction:** Allocation → Request (OA-05-03)  
**Status:** Design — payload deferred (UD-10)

---

## Purpose

Inbound command boundary for post-approval request lifecycle transitions deferred from spec05. Request retains state ownership; spec07 triggers transitions only.

---

## Interface

**Producer namespace:** `App\Modules\Allocation\Application\Contracts\RequestLifecycleCommandPort`

**Consumer:** Request module (handler deferred)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts;

interface RequestLifecycleCommandPort
{
    /**
     * Notify Request that allocation processing has started for an approved request.
     * Payload shape TBD (UD-10).
     */
    public function markWaitingForAllocation(string $requestId, array $context = []): void;

    /**
     * Notify Request that allocation completed successfully.
     */
    public function markAllocated(string $requestId, string $allocationId, array $context = []): void;

    /**
     * Notify Request that allocation failed.
     */
    public function markAllocationFailed(string $requestId, string $reason, array $context = []): void;
}
```

---

## Rules

| Rule | Detail |
| ---- | ------ |
| Owner | Request owns state; Allocation is producer |
| CD-014 | Assignment in Allocation; request lifecycle in Request |
| UD-10 | Method signatures provisional; payload fields open |
| Wave 1 | Stub/no-op adapter acceptable until Request inbound handler exists |

---

## References

- [allocation-lifecycle-command-port.md](./allocation-lifecycle-command-port.md) — producer perspective
- spec05 OA-05-03 — post-approval handoff recorded assumption
