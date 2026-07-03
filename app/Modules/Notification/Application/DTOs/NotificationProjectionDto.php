<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\DTOs;

use DateTimeImmutable;

final readonly class NotificationProjectionDto
{
    public function __construct(
        public string $id,
        public string $notificationType,
        public string $title,
        public string $message,
        public ?string $entityType,
        public ?string $entityId,
        public ?string $deepLinkRoute,
        public bool $isRead,
        public ?DateTimeImmutable $readAt,
        public DateTimeImmutable $createdAt,
        public string $priority,
    ) {}
}
