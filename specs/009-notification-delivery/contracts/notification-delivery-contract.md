# Contract: Notification Delivery (inbound)

**Version:** 1.0.0  
**Spec:** spec09 Notification Delivery  
**Direction:** Upstream → Notification (R9)  
**Status:** Planning — design baseline

---

## Purpose

Primary cross-boundary port for delivering in-app notifications. All upstream contexts use this contract — never Notification infrastructure types.

---

## Interface

**Namespace:** `App\Modules\Notification\Application\Contracts\NotificationDeliveryContract`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Contracts;

use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Application\DTOs\NotificationDeliveryResultDto;

interface NotificationDeliveryContract
{
    /**
     * Deliver an in-app notification. Idempotent on (correlationId, recipient, type).
     */
    public function deliver(NotificationIntentDto $intent): NotificationDeliveryResultDto;
}
```

---

## Result DTO

| Field | Type | Description |
| ----- | ---- | ----------- |
| `notificationId` | UUID | Persisted id (existing on dedup replay) |
| `status` | `delivered` \| `duplicate` \| `skipped` | Delivery outcome |
| `skipReason` | ?string | When skipped |

---

## Implementation binding

| Component | Role |
| --------- | ---- |
| `DeliverNotificationAction` | Application service — validate, dedup, persist |
| `SendNotificationJob` | Queue wrapper for async `deliver()` |
| `NotificationServiceProvider` | Binds contract → action |

---

## Rules

| Rule | Detail |
| ---- | ------ |
| R9 | Upstream imports **contract + DTO only** |
| FR-011 | No upstream mutation from Notification |
| FR-012 | `priority=urgent` → `notifications-urgent` queue or sync |
| Idempotency | Unique constraint per [notification-intent-dto.md](./notification-intent-dto.md) |

---

## Async integration pattern

```
Upstream Action (transaction commit)
    → NotificationDeliveryContract::deliver()   [sync path]
    OR
    → SendNotificationJob::dispatch($intent)    [async path]
        → DeliverNotificationAction::deliver()
```

Upstream chooses sync vs async; Notification semantics identical.
