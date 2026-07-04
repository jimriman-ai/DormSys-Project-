<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;

interface CorrelationProjectionWritePort
{
    public function upsertFromAuditItem(
        AuditHistoryItemDto $item,
        ArchiveVisibilityTier $archiveVisibilityTier,
    ): bool;
}
