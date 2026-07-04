<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Repositories;

use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;
use App\Modules\Reporting\Application\Contracts\Ports\ActorActivitySummaryWritePort;
use App\Modules\Reporting\Application\DTOs\SecurityActorActivityQuery;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use App\Modules\Reporting\Infrastructure\Persistence\Models\ActorActivitySummaryModel;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Carbon;

final class ActorActivitySummaryRepository implements ActorActivitySummaryWritePort
{
    public function incrementForItem(
        AuditHistoryItemDto $item,
        DateTimeImmutable $windowStart,
        DateTimeImmutable $windowEnd,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
    ): void {
        $refreshedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $model = ActorActivitySummaryModel::query()
            ->where('actor_type', $item->actorType)
            ->where('actor_id', $item->actorId)
            ->where('window_start', Carbon::instance($windowStart))
            ->where('window_end', Carbon::instance($windowEnd))
            ->where('granularity', WindowGranularity::Day->value)
            ->where('archive_visibility_tier', $archiveVisibilityTier->value)
            ->first();

        if ($model === null) {
            ActorActivitySummaryModel::query()->create([
                'actor_type' => $item->actorType,
                'actor_id' => $item->actorId,
                'window_start' => Carbon::instance($windowStart),
                'window_end' => Carbon::instance($windowEnd),
                'granularity' => WindowGranularity::Day,
                'event_count' => 1,
                'distinct_event_types' => [$item->eventType],
                'distinct_entities_touched' => 1,
                'archive_visibility_tier' => $archiveVisibilityTier,
                'refreshed_at' => Carbon::instance($refreshedAt),
                'projection_version' => $projectionVersion,
            ]);

            return;
        }

        $model->event_count++;
        $eventTypes = $model->distinct_event_types;
        if (! in_array($item->eventType, $eventTypes, true)) {
            $eventTypes[] = $item->eventType;
            $model->distinct_event_types = $eventTypes;
        }

        $model->distinct_entities_touched = max($model->distinct_entities_touched, 1);
        $model->refreshed_at = Carbon::instance($refreshedAt);
        $model->projection_version = $projectionVersion;
        $model->save();
    }

    /**
     * @return list<ActorActivitySummaryModel>
     */
    public function findSummaries(SecurityActorActivityQuery $query, ArchiveVisibilityTier $tier): array
    {
        return array_values(
            ActorActivitySummaryModel::query()
                ->where('actor_type', $query->actorType)
                ->where('actor_id', $query->actorId)
                ->where('window_start', '>=', $query->windowStart)
                ->where('window_end', '<=', $query->windowEnd)
                ->where('granularity', $query->granularity->value)
                ->where('archive_visibility_tier', $tier->value)
                ->orderBy('window_start')
                ->get()
                ->all(),
        );
    }
}
