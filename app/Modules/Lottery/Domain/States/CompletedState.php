<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\States;

final class CompletedState extends LotteryProgramState
{
    public static string $name = 'completed';
}
