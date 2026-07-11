<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Input for CreateBuilding.
 */
final readonly class CreateBuildingData
{
    public function __construct(
        public string $dormitoryId,
        public string $code,
        public string $name,
        public ?string $status = null,
    ) {}
}
