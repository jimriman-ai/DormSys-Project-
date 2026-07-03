<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Reporting\Application\DTOs\ActorActivitySummaryReadDto;
use App\Modules\Reporting\Application\DTOs\SecurityActorActivityQuery;

interface ActorActivityQueryPort
{
    /**
     * @return list<ActorActivitySummaryReadDto>
     */
    public function findSummaries(SecurityActorActivityQuery $query): array;
}
