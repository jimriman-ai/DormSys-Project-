<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Input for CreateDormitory.
 */
final readonly class CreateDormitoryData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $status = null,
    ) {}
}
