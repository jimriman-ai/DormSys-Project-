<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Domain\Enums;

enum AllocationStatus: string
{
    case Active = 'active';
    case Released = 'released';

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
