<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Enums;

enum TriggerIntakeStatus: string
{
    case Accepted = 'accepted';
    case Superseded = 'superseded';
}
