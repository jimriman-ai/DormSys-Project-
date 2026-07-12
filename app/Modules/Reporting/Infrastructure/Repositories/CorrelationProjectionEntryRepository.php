<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Repositories;

use App\Modules\Reporting\Application\Contracts\Ports\CorrelationProjectionWritePort;
use App\Modules\Reporting\Application\DTOs\ComplianceExportQuery;
use App\Modules\Reporting\Application\DTOs\CorrelationBundleQuery;
use App\Modules\Reporting\Application\DTOs\ProjectionSourceItemDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Infrastructure\Persistence\Models\CorrelationProjectionEntryModel;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Carbon;

final class CorrelationProjectionEntryRepository implements CorrelationProjectionWritePort
{
    public function upsertFromAuditItem(
        ProjectionSourceItemDto $item,
        ArchiveVisibilityTier $archiveVisibilityTier,
    ): bool {
        if ($item->correlationId === '') {
            return false;
        }

        $existing = CorrelationProjectionEntryModel::query()
            ->where('correlation_id', $item->correlationId)
            ->where('source_audit_log_id', $item->auditLogId)
            ->where('archive_visibility_tier', $archiveVisibilityTier->value)
            ->exists();

        if ($existing) {
            return false;
        }

        CorrelationProjectionEntryModel::query()->create([
            'correlation_id' => $item->correlationId,
            'source_audit_log_id' => $item->auditLogId,
            'occurred_at' => Carbon::instance($item->occurredAt),
            'entity_type' => $item->entityType,
            'entity_id' => $item->entityId,
            'actor_type' => $item->actorType,
            'actor_id' => $item->actorId,
            'event_type' => $item->eventType,
            'source_context' => $item->sourceContext,
            'archive_visibility_tier' => $archiveVisibilityTier,
            'ingested_at' => Carbon::instance(new DateTimeImmutable('now', new DateTimeZone('UTC'))),
        ]);

        return true;
    }

    /**
     * @return list<CorrelationProjectionEntryModel>
     */
    public function findBundleEntries(CorrelationBundleQuery $query, ArchiveVisibilityTier $tier): array
    {
        $builder = CorrelationProjectionEntryModel::query()
            ->where('correlation_id', $query->correlationId)
            ->where('archive_visibility_tier', $tier->value)
            ->orderByDesc('occurred_at');

        if ($query->eventTypes !== null && $query->eventTypes !== []) {
            $builder->whereIn('event_type', $query->eventTypes);
        }

        return array_values($builder->get()->all());
    }

    /**
     * @return list<CorrelationProjectionEntryModel>
     */
    public function findEntriesInWindow(ComplianceExportQuery $query, ArchiveVisibilityTier $tier): array
    {
        $builder = CorrelationProjectionEntryModel::query()
            ->where('archive_visibility_tier', $tier->value)
            ->where('occurred_at', '>=', $query->windowStart)
            ->where('occurred_at', '<', $query->windowEnd)
            ->orderBy('occurred_at');

        if ($query->eventTypes !== null && $query->eventTypes !== []) {
            $builder->whereIn('event_type', $query->eventTypes);
        }

        if ($query->eventType !== null) {
            $builder->where('event_type', $query->eventType);
        }

        if ($query->sourceContext !== null) {
            $builder->where('source_context', $query->sourceContext);
        }

        if ($query->actorType !== null) {
            $builder->where('actor_type', $query->actorType);
        }

        if ($query->entityType !== null) {
            $builder->where('entity_type', $query->entityType);
        }

        return array_values($builder->get()->all());
    }
}
