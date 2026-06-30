<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\States;

final class WaitingApprovalState extends LotteryProgramState
{
    public static string $name = 'waiting_approval';
}
