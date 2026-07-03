<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\DTOs;

use App\Modules\Notification\Domain\Enums\DeliveryResultStatus;

final readonly class NotificationDeliveryResultDto
{
    public function __construct(
        public ?string $notificationId,
        public DeliveryResultStatus $status,
        public ?string $skipReason = null,
    ) {}
}
