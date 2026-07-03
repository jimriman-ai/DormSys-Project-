<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Adapters;

use App\Modules\Reporting\Application\Contracts\Ports\WindowAggregateQueryPort;
use App\Modules\Reporting\Application\DTOs\AuditWindowAggregateReadDto;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryQuery;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Infrastructure\Persistence\Models\AuditWindowAggregateModel;
use App\Modules\Reporting\Infrastructure\Repositories\AuditWindowAggregateRepository;

final class WindowAggregateQueryAdapter implements WindowAggregateQueryPort
{
    public function __construct(
        private readonly AuditWindowAggregateRepository $repository,
    ) {}

    public function findBuckets(AuditWindowSummaryQuery $query): array
    {
        $tier = $query->includeArchived
            ? ArchiveVisibilityTier::IncludeArchived
            : ArchiveVisibilityTier::ActiveOnly;

        return array_map(
            fn (AuditWindowAggregateModel $model): AuditWindowAggregateReadDto => new AuditWindowAggregateReadDto(
                windowStart: $model->window_start->toDateTimeImmutable(),
                windowEnd: $model->window_end->toDateTimeImmutable(),
                eventType: $model->event_type,
                sourceContext: $model->source_context,
                actorType: $model->actor_type,
                entityType: $model->entity_type,
                eventCount: $model->event_count,
                distinctEntityCount: $model->distinct_entity_count,
                distinctActorCount: $model->distinct_actor_count,
                topEventTypes: $model->top_event_types,
                projectionVersion: $model->projection_version,
                refreshedAt: $model->refreshed_at->toDateTimeImmutable(),
            ),
            $this->repository->findBuckets($query, $tier),
        );
    }
}
