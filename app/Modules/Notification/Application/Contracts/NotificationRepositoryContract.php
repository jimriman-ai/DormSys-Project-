<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Contracts;

use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Domain\Models\Notification;
use App\Modules\Notification\Domain\ValueObjects\CorrelationId;
use App\Modules\Notification\Domain\ValueObjects\NotificationId;
use DateTimeImmutable;

interface NotificationRepositoryContract
{
    public function save(Notification $notification): Notification;

    public function findByDedupKey(
        CorrelationId $correlationId,
        string $recipientEmployeeId,
        NotificationType $notificationType,
    ): ?Notification;

    public function findByIdForRecipient(NotificationId $notificationId, string $recipientEmployeeId): ?Notification;

    /**
     * @return list<Notification>
     */
    public function listForRecipient(string $recipientEmployeeId, ?bool $unreadOnly = null, int $limit = 50): array;

    public function countUnread(string $recipientEmployeeId): int;

    public function archiveExpiredBefore(DateTimeImmutable $cutoff): int;
}
