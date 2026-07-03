<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditLogRepositoryContract;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Application\DTOs\PaginatedAuditHistoryDto;

final class QueryAuditHistoryAction
{
    public function __construct(
        private readonly AuditLogRepositoryContract $auditLogs,
    ) {}

    public function execute(AuditHistoryQuery $query): PaginatedAuditHistoryDto
    {
        return $this->auditLogs->queryHistory($query);
    }
}
