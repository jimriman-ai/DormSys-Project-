<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services\Materializers;

use App\Modules\Reporting\Application\Contracts\Ports\AuditWindowAggregateWritePort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionIngestReceiptRepositoryPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshMaterializerPort;
use App\Modules\Reporting\Application\DTOs\ProjectionSourceItemDto;
use App\Modules\Reporting\Application\Services\ProjectionDayWindowResolver;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;

final class AuditWindowAggregateMaterializer implements ProjectionRefreshMaterializerPort
{
    public function __construct(
        private readonly ProjectionIngestReceiptRepositoryPort $receiptRepository,
        private readonly AuditWindowAggregateWritePort $aggregateRepository,
        private readonly ProjectionDayWindowResolver $windowResolver,
    ) {}

    public function supports(ProjectionFamily $projectionFamily): bool
    {
        return $projectionFamily === ProjectionFamily::WindowAggregate;
    }

    public function materialize(
        array $items,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
    ): int {
        $written = 0;

        foreach ($items as $item) {
            if (! $this->receiptRepository->claim(
                ProjectionFamily::WindowAggregate,
                $item->auditLogId,
                $archiveVisibilityTier,
            )) {
                continue;
            }

            $window = $this->windowResolver->resolve($item->occurredAt);
            $this->aggregateRepository->incrementForItem(
                ProjectionSourceItemDto::fromAuditHistoryItem($item),
                $window['windowStart'],
                $window['windowEnd'],
                $archiveVisibilityTier,
                $projectionVersion,
            );
            $written++;
        }

        return $written;
    }
}
