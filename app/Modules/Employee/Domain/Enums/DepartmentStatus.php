<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Enums;

enum DepartmentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
