<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Adapters;

use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Application\DTOs\PaginatedAuditHistoryDto;
use App\Modules\Reporting\Application\Contracts\Ports\AuditHistorySourceReadPort;
use App\Modules\Reporting\Application\Services\ReportingProjectionEventTypeCatalog;
use DateTimeImmutable;

final class AuditHistorySourceReadAdapter implements AuditHistorySourceReadPort
{
    public function __construct(
        private readonly AuditHistoryReadContract $auditHistoryRead,
        private readonly ReportingProjectionEventTypeCatalog $eventTypeCatalog,
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

    public function queryForProjectionRefresh(
        bool $includeArchived,
        ?DateTimeImmutable $occurredAfter,
        int $page,
        int $perPage,
    ): PaginatedAuditHistoryDto {
        return $this->auditHistoryRead->query(new AuditHistoryQuery(
            eventTypes: $this->eventTypeCatalog->allEventTypes(),
            occurredFrom: $occurredAfter,
            includeArchived: $includeArchived,
            page: $page,
            perPage: $perPage,
        ));
    }

    public function queryInWindow(
        bool $includeArchived,
        ?array $eventTypes,
        DateTimeImmutable $occurredFrom,
        DateTimeImmutable $occurredTo,
        int $page,
        int $perPage,
    ): PaginatedAuditHistoryDto {
        return $this->auditHistoryRead->query(new AuditHistoryQuery(
            eventTypes: $eventTypes ?? $this->eventTypeCatalog->allEventTypes(),
            occurredFrom: $occurredFrom,
            occurredTo: $occurredTo,
            includeArchived: $includeArchived,
            page: $page,
            perPage: $perPage,
        ));
    }
}
