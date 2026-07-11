<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Read-only bed list projection for Phase 3B.
 */
final readonly class BedSummaryData
{
    public function __construct(
        public string $id,
        public string $roomId,
        public string $label,
        public string $status,
        public string $physicalOccupancyState,
    ) {}
}
