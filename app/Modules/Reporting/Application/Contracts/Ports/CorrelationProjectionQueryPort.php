<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Reporting\Application\DTOs\CorrelationBundleQuery;
use App\Modules\Reporting\Application\DTOs\CorrelationProjectionEntryReadDto;

interface CorrelationProjectionQueryPort
{
    /**
     * @return list<CorrelationProjectionEntryReadDto>
     */
    public function findBundleEntries(CorrelationBundleQuery $query): array;
}
