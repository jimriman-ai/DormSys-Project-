<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\States;

final class CheckedOutState extends RequestState
{
    public static string $name = 'checked_out';
}
