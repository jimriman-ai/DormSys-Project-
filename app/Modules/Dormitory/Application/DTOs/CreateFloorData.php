<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Input for CreateFloor.
 */
final readonly class CreateFloorData
{
    public function __construct(
        public string $buildingId,
        public string $label,
        public ?string $status = null,
    ) {}
}
