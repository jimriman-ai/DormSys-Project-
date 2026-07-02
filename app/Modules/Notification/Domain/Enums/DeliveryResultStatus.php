<?php

declare(strict_types=1);

namespace App\Modules\Notification\Domain\Enums;

enum DeliveryResultStatus: string
{
    case Delivered = 'delivered';
    case Duplicate = 'duplicate';
    case Skipped = 'skipped';
}
