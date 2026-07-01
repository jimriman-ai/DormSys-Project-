<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

final readonly class AssignableCapacityDTO
{
    public function __construct(
        public string $scopeId,
        public string $scopeType,
        public int $totalBeds,
        public int $assignableBeds,
        public int $inServiceBeds,
        public int $occupiedBeds,
        public int $reservedBeds,
    ) {}
}
