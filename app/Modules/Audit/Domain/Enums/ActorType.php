<?php

declare(strict_types=1);

namespace App\Modules\Audit\Domain\Enums;

enum ActorType: string
{
    case User = 'user';
    case System = 'system';
}
