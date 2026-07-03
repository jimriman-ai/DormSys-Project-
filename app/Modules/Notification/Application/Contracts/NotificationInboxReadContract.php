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
