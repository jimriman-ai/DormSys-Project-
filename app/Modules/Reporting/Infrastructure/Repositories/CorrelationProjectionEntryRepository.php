<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Repositories;

use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Infrastructure\Persistence\Models\CorrelationProjectionEntryModel;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Carbon;

final class CorrelationProjectionEntryRepository
{
    public function upsertFromAuditItem(
        AuditHistoryItemDto $item,
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
}
