<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Services;

use App\Modules\Notification\Application\Contracts\MarkNotificationReadContract;
use App\Modules\Notification\Application\Contracts\NotificationRepositoryContract;
use App\Modules\Notification\Domain\ValueObjects\NotificationId;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;

final class MarkNotificationReadAction implements MarkNotificationReadContract
{
    public function __construct(
        private readonly NotificationRepositoryContract $notifications,
    ) {}

    public function markRead(string $notificationId, string $recipientEmployeeId, DateTimeImmutable $readAt): void
    {
        $notification = $this->notifications->findByIdForRecipient(
            NotificationId::fromString($notificationId),
            $recipientEmployeeId,
        );

        if ($notification === null) {
            throw new ValidationException('Notification not found for recipient.');
        }

        $this->notifications->save($notification->markRead($readAt));
    }
}
