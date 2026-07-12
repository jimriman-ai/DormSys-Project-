<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\Enums;

/**
 * Allocation-time inventory markers for a bed (Spec04-owned).
 *
 * VACANT / RESERVED / OCCUPIED. Spec07 owns check-in / resident-presence truth separately.
 */
enum PhysicalOccupancyState: string
{
    case Vacant = 'vacant';
    case Reserved = 'reserved';
    case Occupied = 'occupied';

    public function isVacant(): bool
    {
        return $this === self::Vacant;
    }

    public function isReserved(): bool
    {
        return $this === self::Reserved;
    }

    public function isOccupied(): bool
    {
        return $this === self::Occupied;
    }
}
