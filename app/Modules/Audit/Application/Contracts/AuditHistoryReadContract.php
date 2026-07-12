<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Contracts;

use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Application\DTOs\PaginatedAuditHistoryDto;

interface AuditHistoryReadContract
{
    public function query(AuditHistoryQuery $query): PaginatedAuditHistoryDto;
}
