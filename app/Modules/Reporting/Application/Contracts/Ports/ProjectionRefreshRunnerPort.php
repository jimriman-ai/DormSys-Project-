<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Reporting\Application\DTOs\ProjectionRefreshRequestDto;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshResultDto;

interface ProjectionRefreshRunnerPort
{
    public function runBatch(ProjectionRefreshRequestDto $request): ProjectionRefreshResultDto;
}
