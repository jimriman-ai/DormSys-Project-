<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Reporting\Application\DTOs\ProjectionRefreshBatchDto;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshRequestDto;

interface ProjectionRefreshInputPort
{
    public function fetchNextBatch(ProjectionRefreshRequestDto $request): ProjectionRefreshBatchDto;
}
