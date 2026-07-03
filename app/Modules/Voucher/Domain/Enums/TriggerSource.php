<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Enums;

enum TriggerSource: string
{
    case Lottery = 'lottery';
    case Allocation = 'allocation';
}
