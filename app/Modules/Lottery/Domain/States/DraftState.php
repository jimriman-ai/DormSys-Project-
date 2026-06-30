<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\States;

final class DraftState extends LotteryProgramState
{
    public static string $name = 'draft';
}
