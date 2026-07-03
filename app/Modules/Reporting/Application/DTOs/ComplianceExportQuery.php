<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use DateTimeImmutable;

final readonly class ComplianceExportQuery
{
    /**
     * @param  list<string>|null  $eventTypes
     */
    public function __construct(
        public DateTimeImmutable $windowStart,
        public DateTimeImmutable $windowEnd,
        public WindowGranularity $granularity = WindowGranularity::Day,
        public ?array $eventTypes = null,
        public ?string $eventType = null,
        public ?string $sourceContext = null,
        public ?string $actorType = null,
        public ?string $entityType = null,
        public bool $includeArchived = false,
        public int $page = 1,
        public int $perPage = 50,
    ) {}
}
