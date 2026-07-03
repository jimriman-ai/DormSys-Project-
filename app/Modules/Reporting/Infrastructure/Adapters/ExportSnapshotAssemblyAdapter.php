<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Adapters;

use App\Modules\Reporting\Application\Contracts\Ports\ExportSnapshotAssemblyPort;
use App\Modules\Reporting\Application\Contracts\Ports\WindowAggregateQueryPort;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryQuery;
use App\Modules\Reporting\Application\DTOs\ComplianceExportManifestDto;
use App\Modules\Reporting\Application\DTOs\ComplianceExportQuery;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Infrastructure\Repositories\CorrelationProjectionEntryRepository;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

final class ExportSnapshotAssemblyAdapter implements ExportSnapshotAssemblyPort
{
    public function __construct(
        private readonly CorrelationProjectionEntryRepository $correlationEntries,
        private readonly WindowAggregateQueryPort $windowAggregates,
    ) {}

    public function assembleManifest(ComplianceExportQuery $query): ComplianceExportManifestDto
    {
        $tier = $query->includeArchived
            ? ArchiveVisibilityTier::IncludeArchived
            : ArchiveVisibilityTier::ActiveOnly;

        $entries = $this->correlationEntries->findEntriesInWindow($query, $tier);

        $lineItemSourceAuditLogIds = [];
        foreach ($entries as $entry) {
            $lineItemSourceAuditLogIds[] = $entry->source_audit_log_id;
        }

        $windowSummaryQuery = new AuditWindowSummaryQuery(
            windowStart: $query->windowStart,
            windowEnd: $query->windowEnd,
            granularity: $query->granularity,
            eventType: $query->eventType,
            sourceContext: $query->sourceContext,
            actorType: $query->actorType,
            entityType: $query->entityType,
            includeArchived: $query->includeArchived,
        );

        $aggregates = $this->windowAggregates->findBuckets($windowSummaryQuery);

        $refreshedAt = null;
        $projectionVersion = null;

        foreach ($aggregates as $aggregate) {
            if ($refreshedAt === null || $aggregate->refreshedAt > $refreshedAt) {
                $refreshedAt = $aggregate->refreshedAt;
            }

            $projectionVersion ??= $aggregate->projectionVersion;
        }

        return new ComplianceExportManifestDto(
            snapshotId: UuidGenerator::uuid7(),
            generatedAt: new DateTimeImmutable('now', new DateTimeZone('UTC')),
            filterManifest: $this->filterManifest($query),
            lineItemSourceAuditLogIds: $lineItemSourceAuditLogIds,
            refreshedAt: $refreshedAt,
            projectionVersion: $projectionVersion,
            manifestLineItemCount: count($lineItemSourceAuditLogIds),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function filterManifest(ComplianceExportQuery $query): array
    {
        return [
            'windowStart' => $query->windowStart->format(DateTimeInterface::ATOM),
            'windowEnd' => $query->windowEnd->format(DateTimeInterface::ATOM),
            'granularity' => $query->granularity->value,
            'eventTypes' => $query->eventTypes,
            'eventType' => $query->eventType,
            'sourceContext' => $query->sourceContext,
            'actorType' => $query->actorType,
            'entityType' => $query->entityType,
            'sourceTierMix' => 'T1_manifest + T0_line_items',
        ];
    }
}
