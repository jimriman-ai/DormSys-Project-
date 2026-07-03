# Port: Employee Existence Read (supplier)

**Version:** 1.0.0  
**Spec:** spec09 Notification Delivery (consumer) / spec03 Employee (supplier)  
**Direction:** Employee → Notification  
**Status:** Planning — design baseline

---

## Purpose

Minimal read-only validation that `recipientEmployeeId` refers to an **active** employee before persisting a notification. Prevents orphan inbox entries (FR-010).

---

## Interface

**Namespace:** `App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Contracts;

interface EmployeeExistenceReadPort
{
    public function existsActiveEmployee(string $employeeId): bool;
}
```

---

## Supplier

| Implementation | When |
| -------------- | ---- |
| `EmployeeExistenceReadAdapter` | Calls `EmployeeReadContract` from spec03 when available |
| `StubEmployeeExistenceReadAdapter` | Tests; optional Wave 1 if Employee read not wired |

---

## Rules

| Rule | Detail |
| ---- | ------ |
| R9 | Adapter in Notification **Infrastructure**; imports Employee **Application** contracts only |
| On false | `delivery_status = skipped_invalid_recipient` |
