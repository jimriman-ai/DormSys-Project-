<?php

declare(strict_types=1);

namespace App\Modules\Audit\Infrastructure\Repositories;

use App\Modules\Audit\Application\Contracts\AuditLogRepositoryContract;
use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Application\DTOs\PaginatedAuditHistoryDto;
use App\Modules\Audit\Domain\Models\AuditLog;
use App\Modules\Audit\Domain\ValueObjects\ActorReference;
use App\Modules\Audit\Domain\ValueObjects\AuditLogId;
use App\Modules\Audit\Domain\ValueObjects\CorrelationId;
use App\Modules\Audit\Domain\ValueObjects\EntityReference;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class AuditLogRepository implements AuditLogRepositoryContract
{
    public function insert(AuditLog $auditLog): AuditLog
    {
        $model = new AuditLogModel([
            'correlation_id' => $auditLog->correlationId->value,
            'event_type' => $auditLog->eventType,
            'entity_type' => $auditLog->entityReference->entityType,
            'entity_id' => $auditLog->entityReference->entityId,
            'actor_type' => $auditLog->actorReference->actorType,
            'actor_id' => $auditLog->actorReference->actorId,
            'source_context' => $auditLog->sourceContext,
            'old_values' => $auditLog->oldValues,
            'new_values' => $auditLog->newValues,
            'metadata' => $auditLog->metadata,
            'payload_hash' => $auditLog->payloadHash,
            'occurred_at' => $auditLog->occurredAt,
            'archived_at' => $auditLog->archivedAt,
        ]);

        if ($auditLog->id !== null) {
            $model->id = $auditLog->id->value;
        }

        $model->created_at = Carbon::instance($auditLog->createdAt);
        $model->save();

        return $this->toDomain($model);
    }

    public function findByCorrelationId(string $correlationId): ?AuditLog
    {
        $model = AuditLogModel::query()
            ->where('correlation_id', $correlationId)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function queryHistory(AuditHistoryQuery $query): PaginatedAuditHistoryDto
    {
        $builder = AuditLogModel::query()->orderByDesc('occurred_at');

        if (! $query->includeArchived) {
            $builder->whereNull('archived_at');
        }

        if ($query->entityType !== null && $query->entityId !== null) {
            $builder->where('entity_type', $query->entityType)
                ->where('entity_id', $query->entityId);
        }

        if ($query->actorType !== null && $query->actorId !== null) {
            $builder->where('actor_type', $query->actorType)
                ->where('actor_id', $query->actorId);
        }

        if ($query->eventTypes !== null && $query->eventTypes !== []) {
            $builder->whereIn('event_type', $query->eventTypes);
        }

        if ($query->occurredFrom !== null) {
            $builder->where('occurred_at', '>=', $query->occurredFrom);
        }

        if ($query->occurredTo !== null) {
            $builder->where('occurred_at', '<=', $query->occurredTo);
        }

        $total = (clone $builder)->count();
        $perPage = min(max($query->perPage, 1), 200);
        $page = max($query->page, 1);
        $lastPage = max((int) ceil($total / $perPage), 1);

        $models = $builder
            ->forPage($page, $perPage)
            ->get();

        $items = [];

        foreach ($models as $model) {
            $items[] = $this->toHistoryItem($model);
        }

        return new PaginatedAuditHistoryDto(
            items: $items,
            total: $total,
            page: $page,
            perPage: $perPage,
            lastPage: $lastPage,
        );
    }

    public function archiveExpiredBefore(DateTimeImmutable $cutoff): int
    {
        $archivedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        return DB::table('audit_logs')
            ->whereNull('archived_at')
            ->where('occurred_at', '<', $cutoff)
            ->update(['archived_at' => $archivedAt]);
    }

    private function toDomain(AuditLogModel $model): AuditLog
    {
        return new AuditLog(
            id: AuditLogId::fromString((string) $model->id),
            correlationId: CorrelationId::fromString($model->correlation_id),
            eventType: $model->event_type,
            entityReference: EntityReference::fromStrings($model->entity_type, $model->entity_id),
            actorReference: new ActorReference($model->actor_type, $model->actor_id),
            sourceContext: $model->source_context,
            oldValues: $model->old_values,
            newValues: $model->new_values,
            metadata: $model->metadata,
            payloadHash: $model->payload_hash,
            occurredAt: $this->toImmutable($model->occurred_at),
            archivedAt: $model->archived_at === null ? null : $this->toImmutable($model->archived_at),
            createdAt: $this->toImmutable($model->created_at),
        );
    }

    private function toHistoryItem(AuditLogModel $model): AuditHistoryItemDto
    {
        return new AuditHistoryItemDto(
            auditLogId: (string) $model->id,
            correlationId: $model->correlation_id,
            eventType: $model->event_type->value,
            entityType: $model->entity_type,
            entityId: $model->entity_id,
            actorType: $model->actor_type->value,
            actorId: $model->actor_id,
            sourceContext: $model->source_context,
            oldValues: $model->old_values,
            newValues: $model->new_values,
            metadata: $model->metadata,
            occurredAt: $this->toImmutable($model->occurred_at),
            createdAt: $this->toImmutable($model->created_at),
        );
    }

    private function toImmutable(Carbon $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value->format('Y-m-d H:i:s.u'), new DateTimeZone('UTC'));
    }
}
