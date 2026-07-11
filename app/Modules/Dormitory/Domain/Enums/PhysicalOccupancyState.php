<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\Enums;

/**
 * Physical occupancy of a bed.
 *
 * Vacant/Occupied only — Allocation assignment/reservation is not physical occupancy.
 */
enum PhysicalOccupancyState: string
{
    case Vacant = 'vacant';
    case Occupied = 'occupied';

    public function isVacant(): bool
    {
        return $this === self::Vacant;
    }

    public function isOccupied(): bool
    {
        return $this === self::Occupied;
    }
}
