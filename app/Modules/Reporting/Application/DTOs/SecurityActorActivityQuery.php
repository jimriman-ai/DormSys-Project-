<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use DateTimeImmutable;

final readonly class SecurityActorActivityQuery
{
    public function __construct(
        public string $actorType,
        public string $actorId,
        public DateTimeImmutable $windowStart,
        public DateTimeImmutable $windowEnd,
        public WindowGranularity $granularity = WindowGranularity::Day,
        public bool $includeArchived = false,
    ) {}
}
