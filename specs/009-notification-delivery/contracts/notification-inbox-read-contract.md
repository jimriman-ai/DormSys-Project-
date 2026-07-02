# Contract: Notification Inbox Read

**Version:** 1.0.0  
**Spec:** spec09 Notification Delivery  
**Direction:** Presentation / Application consumers → Notification  
**Status:** Planning — design baseline

---

## Purpose

Read-only inbox access for authenticated recipients. No lifecycle mutation of upstream entities.

---

## Interface

**Namespace:** `App\Modules\Notification\Application\Contracts\NotificationInboxReadContract`

```php
<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Contracts;

use App\Modules\Notification\Application\DTOs\NotificationProjectionDto;

interface NotificationInboxReadContract
{
    /**
     * @return list<NotificationProjectionDto>
     */
    public function listForRecipient(string $recipientEmployeeId, ?bool $unreadOnly = null, int $limit = 50): array;

    public function findByIdForRecipient(string $notificationId, string $recipientEmployeeId): ?NotificationProjectionDto;

    public function countUnread(string $recipientEmployeeId): int;
}
```

**Namespace:** `App\Modules\Notification\Application\Contracts\MarkNotificationReadContract`

```php
interface MarkNotificationReadContract
{
    public function markRead(string $notificationId, string $recipientEmployeeId, \DateTimeImmutable $readAt): void;
}
```

---

## Projection DTO

| Field | Type |
| ----- | ---- |
| id | UUID |
| notificationType | string |
| title | string |
| message | string |
| entityType | ?string |
| entityId | ?string |
| deepLinkRoute | ?string |
| isRead | bool |
| readAt | ?DateTimeImmutable |
| createdAt | DateTimeImmutable |
| priority | string |

---

## Rules

| Rule | Detail |
| ---- | ------ |
| FR-014 | All queries scoped to `recipientEmployeeId` |
| CD-017 | Reporting may consume projections read-only — separate spec11 |
| Default list | Excludes `archived_at IS NOT NULL` |
| Jalali | Presentation layer converts `createdAt` / `readAt` |

---

## Presentation (deferred)

Livewire inbox components consume `NotificationInboxReadContract` only — out of spec09 implementation MVP scope per OA-09-05.
