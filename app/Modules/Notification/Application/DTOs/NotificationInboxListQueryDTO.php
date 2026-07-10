<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\DTOs;

final readonly class NotificationInboxListQueryDTO
{
    public function __construct(
        public string $recipientEmployeeId,
        public ?bool $unreadOnly = null,
        public int $page = 1,
        public int $perPage = 50,
    ) {}
}
