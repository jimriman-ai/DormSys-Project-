<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Reporting\Application\DTOs\ProjectionSourceItemDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use DateTimeImmutable;

interface AuditWindowAggregateWritePort
{
    public function incrementForItem(
        ProjectionSourceItemDto $item,
        DateTimeImmutable $windowStart,
        DateTimeImmutable $windowEnd,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
    ): void;
}
