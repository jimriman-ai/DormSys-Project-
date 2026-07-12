<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Contracts;

use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Application\DTOs\PaginatedAuditHistoryDto;
use App\Modules\Audit\Domain\Models\AuditLog;

interface AuditLogRepositoryContract
{
    public function insert(AuditLog $auditLog): AuditLog;

    public function findByCorrelationId(string $correlationId): ?AuditLog;

    public function queryHistory(AuditHistoryQuery $query): PaginatedAuditHistoryDto;

    public function archiveExpiredBefore(\DateTimeImmutable $cutoff): int;
}
