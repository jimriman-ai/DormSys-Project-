<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Reporting\Application\DTOs\ProjectionSourceItemDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;

interface CorrelationProjectionWritePort
{
    public function upsertFromAuditItem(
        ProjectionSourceItemDto $item,
        ArchiveVisibilityTier $archiveVisibilityTier,
    ): bool;
}
