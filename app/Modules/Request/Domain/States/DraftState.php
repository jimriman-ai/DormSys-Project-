<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\States;

final class DraftState extends RequestState
{
    public static string $name = 'draft';
}
