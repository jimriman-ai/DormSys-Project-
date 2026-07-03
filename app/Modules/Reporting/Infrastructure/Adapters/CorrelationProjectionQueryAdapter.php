<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Adapters;

use App\Modules\Reporting\Application\Contracts\Ports\CorrelationProjectionQueryPort;
use App\Modules\Reporting\Application\DTOs\CorrelationBundleQuery;
use App\Modules\Reporting\Application\DTOs\CorrelationProjectionEntryReadDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Infrastructure\Persistence\Models\CorrelationProjectionEntryModel;
use App\Modules\Reporting\Infrastructure\Repositories\CorrelationProjectionEntryRepository;

final class CorrelationProjectionQueryAdapter implements CorrelationProjectionQueryPort
{
    public function __construct(
        private readonly CorrelationProjectionEntryRepository $repository,
    ) {}

    public function findBundleEntries(CorrelationBundleQuery $query): array
    {
        $tier = $query->includeArchived
            ? ArchiveVisibilityTier::IncludeArchived
            : ArchiveVisibilityTier::ActiveOnly;

        return array_map(
            fn (CorrelationProjectionEntryModel $model): CorrelationProjectionEntryReadDto => new CorrelationProjectionEntryReadDto(
                sourceAuditLogId: $model->source_audit_log_id,
                correlationId: $model->correlation_id,
                occurredAt: $model->occurred_at->toDateTimeImmutable(),
                entityType: $model->entity_type,
                entityId: $model->entity_id,
                actorType: $model->actor_type,
                actorId: $model->actor_id,
                eventType: $model->event_type,
                sourceContext: $model->source_context,
                ingestedAt: $model->ingested_at->toDateTimeImmutable(),
            ),
            $this->repository->findBundleEntries($query, $tier),
        );
    }
}
