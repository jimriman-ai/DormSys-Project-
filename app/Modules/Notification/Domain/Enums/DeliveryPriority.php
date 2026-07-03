<?php

declare(strict_types=1);

namespace App\Modules\Notification\Domain\Enums;

enum DeliveryPriority: string
{
    case Standard = 'standard';
    case Urgent = 'urgent';
}
