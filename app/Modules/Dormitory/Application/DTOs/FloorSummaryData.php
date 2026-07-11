<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Read-only floor list projection for Phase 3B.
 */
final readonly class FloorSummaryData
{
    public function __construct(
        public string $id,
        public string $buildingId,
        public string $label,
        public string $status,
    ) {}
}
