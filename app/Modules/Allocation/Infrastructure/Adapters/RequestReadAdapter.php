<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Adapters;

use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Domain\ValueObjects\RequestId;

final class RequestReadAdapter
{
    public function __construct(
        private readonly RequestReadContract $requests,
    ) {}

    public function getApprovedSummary(RequestId $requestId): ?RequestSummaryDTO
    {
        $summary = $this->requests->getRequestSummary($requestId);

        if ($summary === null || $summary->status !== 'approved') {
            return null;
        }

        return $summary;
    }
}
