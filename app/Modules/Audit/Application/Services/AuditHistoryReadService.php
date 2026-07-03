<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditAuthorizationPort;
use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Application\DTOs\PaginatedAuditHistoryDto;

final class AuditHistoryReadService implements AuditHistoryReadContract
{
    public function __construct(
        private readonly AuditAuthorizationPort $authorization,
        private readonly QueryAuditHistoryAction $queryAuditHistory,
    ) {}

    public function query(AuditHistoryQuery $query): PaginatedAuditHistoryDto
    {
        $this->authorization->authorizeRead();

        return $this->queryAuditHistory->execute($query);
    }
}
