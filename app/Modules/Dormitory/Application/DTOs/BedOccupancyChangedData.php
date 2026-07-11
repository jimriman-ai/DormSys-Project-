<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Result of a bed physical occupancy mutation.
 */
final readonly class BedOccupancyChangedData
{
    public function __construct(
        public string $id,
        public string $physicalOccupancyState,
    ) {}
}
