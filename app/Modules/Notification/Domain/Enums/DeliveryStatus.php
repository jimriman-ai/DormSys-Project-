<?php

declare(strict_types=1);

namespace App\Modules\Notification\Domain\Enums;

enum DeliveryStatus: string
{
    case Delivered = 'delivered';
    case SkippedInvalidRecipient = 'skipped_invalid_recipient';
}
