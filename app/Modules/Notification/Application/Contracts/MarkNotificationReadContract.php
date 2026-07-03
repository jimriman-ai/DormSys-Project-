<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Contracts;

use DateTimeImmutable;

interface MarkNotificationReadContract
{
    public function markRead(string $notificationId, string $recipientEmployeeId, DateTimeImmutable $readAt): void;
}
