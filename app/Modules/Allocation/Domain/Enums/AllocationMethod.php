<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Domain\Enums;

enum AllocationMethod: string
{
    case Manual = 'manual';
    case RequestSourced = 'request_sourced';
    case LotterySourced = 'lottery_sourced';
}
