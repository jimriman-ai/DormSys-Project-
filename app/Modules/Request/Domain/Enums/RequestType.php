<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Enums;

enum RequestType: string
{
    case Personal = 'personal';
    case FamilyDirect = 'family_direct';
    case Mission = 'mission';
    case LotteryRegistration = 'lottery_registration';
}
