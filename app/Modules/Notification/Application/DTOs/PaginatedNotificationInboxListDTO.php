<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\DTOs;

final readonly class PaginatedNotificationInboxListDTO
{
    /**
     * @param  list<NotificationProjectionDto>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $currentPage,
        public int $perPage,
        public int $lastPage,
    ) {}
}
