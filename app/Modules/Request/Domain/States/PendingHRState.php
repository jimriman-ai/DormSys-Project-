<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\States;

final class PendingHRState extends RequestState
{
    public static string $name = 'pending_hr';
}
