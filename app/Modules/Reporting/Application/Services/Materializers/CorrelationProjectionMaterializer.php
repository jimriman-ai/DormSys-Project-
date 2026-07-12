<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services\Materializers;

use App\Modules\Reporting\Application\Contracts\Ports\CorrelationProjectionWritePort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshMaterializerPort;
use App\Modules\Reporting\Application\DTOs\ProjectionSourceItemDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;

final class CorrelationProjectionMaterializer implements ProjectionRefreshMaterializerPort
{
    public function __construct(
        private readonly CorrelationProjectionWritePort $repository,
    ) {}

    public function supports(ProjectionFamily $projectionFamily): bool
    {
        return $projectionFamily === ProjectionFamily::Correlation;
    }

    public function materialize(
        array $items,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
    ): int {
        unset($projectionVersion);

        $written = 0;

        foreach ($items as $item) {
            if ($this->repository->upsertFromAuditItem(
                ProjectionSourceItemDto::fromAuditHistoryItem($item),
                $archiveVisibilityTier,
            )) {
                $written++;
            }
        }

        return $written;
    }
}
