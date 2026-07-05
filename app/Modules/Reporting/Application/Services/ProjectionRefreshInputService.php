<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;
use App\Modules\Audit\Application\Services\AuditReadPolicyEnforcementPoint;
use App\Modules\Reporting\Application\Contracts\Ports\AuditHistorySourceReadPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionCursorControlPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshInputPort;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshBatchDto;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshRequestDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;

final class ProjectionRefreshInputService implements ProjectionRefreshInputPort
{
    public function __construct(
        private readonly AuditReadPolicyEnforcementPoint $auditReadPolicy,
        private readonly ReportingArchiveVisibilityGuard $archiveVisibility,
        private readonly ProjectionCursorControlPort $cursorControl,
        private readonly AuditHistorySourceReadPort $auditHistorySource,
    ) {}

    public function fetchNextBatch(ProjectionRefreshRequestDto $request): ProjectionRefreshBatchDto
    {
        $this->auditReadPolicy->enforce();

        $cursor = $this->cursorControl->resolveCursor(
            $request->projectionFamily,
            $request->archiveVisibilityTier,
            $request->projectionVersion,
            $request->refreshMode,
        );

        $includeArchived = $this->archiveVisibility->resolveIncludeArchived(
            $request->archiveVisibilityTier === ArchiveVisibilityTier::IncludeArchived,
        );

        $page = $this->auditHistorySource->queryForProjectionRefresh(
            includeArchived: $includeArchived,
            occurredAfter: $cursor->lastOccurredAt,
            page: $request->page,
            perPage: $request->perPage,
            occurredAfterAuditLogId: $cursor->lastSourceAuditLogId,
        );

        $items = $this->filterAlreadyProcessedItems($page->items, $cursor->lastSourceAuditLogId);

        return new ProjectionRefreshBatchDto(
            cursor: $cursor,
            items: $items,
            page: $page->page,
            lastPage: $page->lastPage,
            total: $page->total,
            hasMorePages: $page->page < $page->lastPage,
        );
    }

    /**
     * @param  list<AuditHistoryItemDto>  $items
     * @return list<AuditHistoryItemDto>
     */
    private function filterAlreadyProcessedItems(array $items, ?string $lastSourceAuditLogId): array
    {
        if ($lastSourceAuditLogId === null) {
            return $items;
        }

        return array_values(array_filter(
            $items,
            static fn (AuditHistoryItemDto $item): bool => $item->auditLogId !== $lastSourceAuditLogId,
        ));
    }
}
