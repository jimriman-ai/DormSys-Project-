<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Reporting\Application\DTOs\AggregateDrillDownQuery;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownReadModel;

interface AggregateDrillDownPort
{
    public function drillDown(AggregateDrillDownQuery $query): AggregateDrillDownReadModel;
}
