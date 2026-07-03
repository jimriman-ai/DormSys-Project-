<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Audit\Application\DTOs\PaginatedAuditHistoryDto;
use DateTimeImmutable;

interface AuditHistorySourceReadPort
{
    /**
     * @param  list<string>|null  $eventTypes
     */
    public function queryByEntity(
        string $entityType,
        string $entityId,
        bool $includeArchived,
        ?array $eventTypes,
        ?DateTimeImmutable $occurredFrom,
        ?DateTimeImmutable $occurredTo,
        int $page,
        int $perPage,
    ): PaginatedAuditHistoryDto;
}
