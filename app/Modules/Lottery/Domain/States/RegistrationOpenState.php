<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\States;

final class RegistrationOpenState extends LotteryProgramState
{
    public static string $name = 'registration_open';
}
