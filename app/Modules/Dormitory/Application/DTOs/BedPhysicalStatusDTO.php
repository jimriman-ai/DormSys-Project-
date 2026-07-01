<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

final readonly class BedPhysicalStatusDTO
{
    public function __construct(
        public string $bedId,
        public string $dormitorySiteId,
        public string $roomId,
        public string $bedCode,
        public string $operabilityStatus,
        public string $occupancyMarker,
        public string $roomKind,
        public bool $isAssignable,
    ) {}
}
