<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;

interface ProjectionRefreshMaterializerPort
{
    public function supports(ProjectionFamily $projectionFamily): bool;

    /**
     * @param  list<AuditHistoryItemDto>  $items
     */
    public function materialize(
        array $items,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
    ): int;
}
