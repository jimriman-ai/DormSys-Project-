<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Input for CreateBed.
 */
final readonly class CreateBedData
{
    public function __construct(
        public string $roomId,
        public string $label,
        public ?string $status = null,
    ) {}
}
