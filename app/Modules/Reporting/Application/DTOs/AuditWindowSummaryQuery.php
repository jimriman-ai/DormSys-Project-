<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use DateTimeImmutable;

final readonly class AuditWindowSummaryQuery
{
    public function __construct(
        public DateTimeImmutable $windowStart,
        public DateTimeImmutable $windowEnd,
        public WindowGranularity $granularity = WindowGranularity::Day,
        public ?string $eventType = null,
        public ?string $sourceContext = null,
        public ?string $actorType = null,
        public ?string $entityType = null,
        public bool $includeArchived = false,
    ) {}
}
