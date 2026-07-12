<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Repositories;

use App\Modules\Reporting\Application\Contracts\Ports\AuditWindowAggregateWritePort;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryQuery;
use App\Modules\Reporting\Application\DTOs\ProjectionSourceItemDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use App\Modules\Reporting\Infrastructure\Persistence\Models\AuditWindowAggregateModel;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class AuditWindowAggregateRepository implements AuditWindowAggregateWritePort
{
    public function incrementForItem(
        ProjectionSourceItemDto $item,
        DateTimeImmutable $windowStart,
        DateTimeImmutable $windowEnd,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
    ): void {
        $refreshedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $this->incrementRow(
            windowStart: $windowStart,
            windowEnd: $windowEnd,
            eventType: $item->eventType,
            sourceContext: $item->sourceContext,
            actorType: $item->actorType,
            entityType: $item->entityType,
            archiveVisibilityTier: $archiveVisibilityTier,
            projectionVersion: $projectionVersion,
            refreshedAt: $refreshedAt,
            entityId: $item->entityId,
            actorId: $item->actorId,
            trackedEntityType: $item->entityType,
            trackedActorType: $item->actorType,
        );

        $this->incrementRow(
            windowStart: $windowStart,
            windowEnd: $windowEnd,
            eventType: null,
            sourceContext: null,
            actorType: null,
            entityType: null,
            archiveVisibilityTier: $archiveVisibilityTier,
            projectionVersion: $projectionVersion,
            refreshedAt: $refreshedAt,
            entityId: $item->entityId,
            actorId: $item->actorId,
            trackedEntityType: $item->entityType,
            trackedActorType: $item->actorType,
        );
    }

    private function incrementRow(
        DateTimeImmutable $windowStart,
        DateTimeImmutable $windowEnd,
        ?string $eventType,
        ?string $sourceContext,
        ?string $actorType,
        ?string $entityType,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
        DateTimeImmutable $refreshedAt,
        string $entityId,
        string $actorId,
        string $trackedEntityType,
        string $trackedActorType,
    ): void {
        $builder = AuditWindowAggregateModel::query()
            ->where('window_start', Carbon::instance($windowStart))
            ->where('window_end', Carbon::instance($windowEnd))
            ->where('granularity', WindowGranularity::Day->value)
            ->where('archive_visibility_tier', $archiveVisibilityTier->value);

        $this->whereNullableDimension($builder, 'event_type', $eventType);
        $this->whereNullableDimension($builder, 'source_context', $sourceContext);
        $this->whereNullableDimension($builder, 'actor_type', $actorType);
        $this->whereNullableDimension($builder, 'entity_type', $entityType);

        $model = $builder->first();

        if ($model === null) {
            AuditWindowAggregateModel::query()->create([
                'window_start' => Carbon::instance($windowStart),
                'window_end' => Carbon::instance($windowEnd),
                'granularity' => WindowGranularity::Day,
                'event_type' => $eventType,
                'source_context' => $sourceContext,
                'actor_type' => $actorType,
                'entity_type' => $entityType,
                'archive_visibility_tier' => $archiveVisibilityTier,
                'event_count' => 1,
                'distinct_entity_refs' => [$this->entityRef($trackedEntityType, $entityId)],
                'distinct_actor_refs' => [$this->actorRef($trackedActorType, $actorId)],
                'distinct_entity_count' => 1,
                'distinct_actor_count' => 1,
                'top_event_types' => $eventType === null ? null : [$eventType => 1],
                'refreshed_at' => Carbon::instance($refreshedAt),
                'projection_version' => $projectionVersion,
            ]);

            return;
        }

        $model->event_count++;

        $entityRefs = $model->distinct_entity_refs;
        $entityRef = $this->entityRef($trackedEntityType, $entityId);
        if (! in_array($entityRef, $entityRefs, true)) {
            $entityRefs[] = $entityRef;
            $model->distinct_entity_refs = $entityRefs;
        }

        $actorRefs = $model->distinct_actor_refs;
        $actorRef = $this->actorRef($trackedActorType, $actorId);
        if (! in_array($actorRef, $actorRefs, true)) {
            $actorRefs[] = $actorRef;
            $model->distinct_actor_refs = $actorRefs;
        }

        $model->distinct_entity_count = count($entityRefs);
        $model->distinct_actor_count = count($actorRefs);

        if ($eventType !== null) {
            $histogram = $model->top_event_types ?? [];
            $histogram[$eventType] = ($histogram[$eventType] ?? 0) + 1;
            $model->top_event_types = $histogram;
        }

        $model->refreshed_at = Carbon::instance($refreshedAt);
        $model->projection_version = $projectionVersion;
        $model->save();
    }

    /**
     * @return list<AuditWindowAggregateModel>
     */
    public function findBuckets(AuditWindowSummaryQuery $query, ArchiveVisibilityTier $tier): array
    {
        $builder = AuditWindowAggregateModel::query()
            ->where('window_start', '>=', $query->windowStart)
            ->where('window_end', '<=', $query->windowEnd)
            ->where('granularity', $query->granularity->value)
            ->where('archive_visibility_tier', $tier->value);

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

        return array_values($builder->orderBy('window_start')->get()->all());
    }

    /**
     * @param  Builder<AuditWindowAggregateModel>  $builder
     */
    private function whereNullableDimension(Builder $builder, string $column, ?string $value): void
    {
        if ($value === null) {
            $builder->whereNull($column);

            return;
        }

        $builder->where($column, $value);
    }

    private function entityRef(string $entityType, string $entityId): string
    {
        return $entityType.':'.$entityId;
    }

    private function actorRef(string $actorType, string $actorId): string
    {
        return $actorType.':'.$actorId;
    }
}
