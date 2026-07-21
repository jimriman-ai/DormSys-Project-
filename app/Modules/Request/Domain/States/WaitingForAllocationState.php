<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\States;

final class WaitingForAllocationState extends RequestState
{
    public static string $name = 'waiting_for_allocation';
}
