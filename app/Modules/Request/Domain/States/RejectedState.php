<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\States;

final class RejectedState extends RequestState
{
    public static string $name = 'rejected';
}
