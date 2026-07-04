<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use DateTimeImmutable;

interface AuditWindowAggregateWritePort
{
    public function incrementForItem(
        AuditHistoryItemDto $item,
        DateTimeImmutable $windowStart,
        DateTimeImmutable $windowEnd,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
    ): void;
}
