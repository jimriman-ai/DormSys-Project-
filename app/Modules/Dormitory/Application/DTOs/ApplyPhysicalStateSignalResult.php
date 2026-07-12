<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;

final readonly class ApplyPhysicalStateSignalResult
{
    public function __construct(
        public bool $accepted,
        public PhysicalOccupancyState $resultingState,
        public ?string $rejectionCode = null,
    ) {}
}
