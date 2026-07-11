<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Read-only room list projection for Phase 3B.
 */
final readonly class RoomSummaryData
{
    public function __construct(
        public string $id,
        public string $floorId,
        public string $code,
        public string $name,
        public int $capacityTotal,
        public string $status,
    ) {}
}
