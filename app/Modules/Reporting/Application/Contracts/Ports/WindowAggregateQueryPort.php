<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Reporting\Application\DTOs\AuditWindowAggregateReadDto;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryQuery;

interface WindowAggregateQueryPort
{
    /**
     * @return list<AuditWindowAggregateReadDto>
     */
    public function findBuckets(AuditWindowSummaryQuery $query): array;
}
