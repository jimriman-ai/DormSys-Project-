<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

use App\Modules\Dormitory\Application\Enums\PhysicalStateSignalType;

final readonly class ApplyPhysicalStateSignalCommand
{
    public function __construct(
        public string $bedId,
        public PhysicalStateSignalType $signalType,
        public ?string $correlationId = null,
    ) {}
}
