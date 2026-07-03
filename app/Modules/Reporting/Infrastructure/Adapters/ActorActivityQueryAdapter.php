<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Adapters;

use App\Modules\Reporting\Application\Contracts\Ports\ActorActivityQueryPort;
use App\Modules\Reporting\Application\DTOs\ActorActivitySummaryReadDto;
use App\Modules\Reporting\Application\DTOs\SecurityActorActivityQuery;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Infrastructure\Persistence\Models\ActorActivitySummaryModel;
use App\Modules\Reporting\Infrastructure\Repositories\ActorActivitySummaryRepository;

final class ActorActivityQueryAdapter implements ActorActivityQueryPort
{
    public function __construct(
        private readonly ActorActivitySummaryRepository $repository,
    ) {}

    public function findSummaries(SecurityActorActivityQuery $query): array
    {
        $tier = $query->includeArchived
            ? ArchiveVisibilityTier::IncludeArchived
            : ArchiveVisibilityTier::ActiveOnly;

        return array_map(
            fn (ActorActivitySummaryModel $model): ActorActivitySummaryReadDto => new ActorActivitySummaryReadDto(
                actorType: $model->actor_type,
                actorId: $model->actor_id,
                windowStart: $model->window_start->toDateTimeImmutable(),
                windowEnd: $model->window_end->toDateTimeImmutable(),
                eventCount: $model->event_count,
                distinctEventTypes: $model->distinct_event_types,
                distinctEntitiesTouched: $model->distinct_entities_touched,
                projectionVersion: $model->projection_version,
                refreshedAt: $model->refreshed_at->toDateTimeImmutable(),
            ),
            $this->repository->findSummaries($query, $tier),
        );
    }
}
