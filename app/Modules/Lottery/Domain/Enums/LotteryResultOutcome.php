<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Enums;

enum LotteryResultOutcome: string
{
    case Winner = 'winner';
    case Reserve = 'reserve';
}
