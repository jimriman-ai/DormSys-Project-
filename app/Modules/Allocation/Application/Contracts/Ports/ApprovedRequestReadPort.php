<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts\Ports;

use App\Modules\Request\Application\DTOs\RequestSummaryDTO;

interface ApprovedRequestReadPort
{
    public function getApprovedSummary(string $requestId): ?RequestSummaryDTO;
}
