<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Services;

use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\Contracts\NotificationRepositoryContract;
use App\Modules\Notification\Application\DTOs\NotificationProjectionDto;
use App\Modules\Notification\Domain\Models\Notification;
use App\Modules\Notification\Domain\ValueObjects\NotificationId;

final class NotificationInboxReadService implements NotificationInboxReadContract
{
    public function __construct(
        private readonly NotificationRepositoryContract $notifications,
    ) {}

    public function listForRecipient(string $recipientEmployeeId, ?bool $unreadOnly = null, int $limit = 50): array
    {
        return array_map(
            fn (Notification $notification): NotificationProjectionDto => $this->toProjection($notification),
            $this->notifications->listForRecipient($recipientEmployeeId, $unreadOnly, $limit),
        );
    }

    public function findByIdForRecipient(string $notificationId, string $recipientEmployeeId): ?NotificationProjectionDto
    {
        $notification = $this->notifications->findByIdForRecipient(
            NotificationId::fromString($notificationId),
            $recipientEmployeeId,
        );

        return $notification === null ? null : $this->toProjection($notification);
    }

    public function countUnread(string $recipientEmployeeId): int
    {
        return $this->notifications->countUnread($recipientEmployeeId);
    }

    private function toProjection(Notification $notification): NotificationProjectionDto
    {
        return new NotificationProjectionDto(
            id: $notification->requireId()->value,
            notificationType: $notification->type->value,
            title: $notification->title,
            message: $notification->message,
            entityType: $notification->entityReference?->entityType,
            entityId: $notification->entityReference?->entityId,
            deepLinkRoute: $notification->deepLinkRoute,
            isRead: $notification->isRead(),
            readAt: $notification->readAt,
            createdAt: $notification->createdAt,
            priority: $notification->priority->value,
        );
    }
}
