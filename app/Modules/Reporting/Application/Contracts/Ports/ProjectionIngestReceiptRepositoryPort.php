<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;

interface ProjectionIngestReceiptRepositoryPort
{
    public function claim(
        ProjectionFamily $projectionFamily,
        string $sourceAuditLogId,
        ArchiveVisibilityTier $archiveVisibilityTier,
    ): bool;
}
