<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Domain\Enums;

enum WindowGranularity: string
{
    case Hour = 'hour';
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
}
