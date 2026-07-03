<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Adapters;

use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Application\DTOs\PaginatedAuditHistoryDto;
use App\Modules\Reporting\Application\Contracts\Ports\AuditHistorySourceReadPort;
use DateTimeImmutable;

final class AuditHistorySourceReadAdapter implements AuditHistorySourceReadPort
{
    public function __construct(
        private readonly AuditHistoryReadContract $auditHistoryRead,
    ) {}

    public function queryByEntity(
        string $entityType,
        string $entityId,
        bool $includeArchived,
        ?array $eventTypes,
        ?DateTimeImmutable $occurredFrom,
        ?DateTimeImmutable $occurredTo,
        int $page,
        int $perPage,
    ): PaginatedAuditHistoryDto {
        return $this->auditHistoryRead->query(new AuditHistoryQuery(
            entityType: $entityType,
            entityId: $entityId,
            eventTypes: $eventTypes,
            occurredFrom: $occurredFrom,
            occurredTo: $occurredTo,
            includeArchived: $includeArchived,
            page: $page,
            perPage: $perPage,
        ));
    }

    public function queryByActor(
        string $actorType,
        string $actorId,
        bool $includeArchived,
        ?array $eventTypes,
        ?DateTimeImmutable $occurredFrom,
        ?DateTimeImmutable $occurredTo,
        int $page,
        int $perPage,
    ): PaginatedAuditHistoryDto {
        return $this->auditHistoryRead->query(new AuditHistoryQuery(
            actorType: $actorType,
            actorId: $actorId,
            eventTypes: $eventTypes,
            occurredFrom: $occurredFrom,
            occurredTo: $occurredTo,
            includeArchived: $includeArchived,
            page: $page,
            perPage: $perPage,
        ));
    }
}
