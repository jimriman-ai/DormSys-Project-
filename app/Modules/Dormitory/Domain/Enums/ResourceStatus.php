<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\Enums;

/**
 * Operational / physical availability status for rooms and beds.
 *
 * Distinct from Allocation assignment — assignment is not physical occupancy.
 */
enum ResourceStatus: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function isUsable(): bool
    {
        return $this === self::Available;
    }

    public function allowsOccupancy(): bool
    {
        return $this === self::Available;
    }

    public function contributesToAvailability(): bool
    {
        return $this === self::Available;
    }
}
