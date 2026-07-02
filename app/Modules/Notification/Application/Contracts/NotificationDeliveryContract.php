<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Contracts;

use App\Modules\Notification\Application\DTOs\NotificationDeliveryResultDto;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;

interface NotificationDeliveryContract
{
    public function deliver(NotificationIntentDto $intent): NotificationDeliveryResultDto;
}
