<?php

declare(strict_types=1);

namespace App\Integrations\Allocation;

use App\Modules\Allocation\Application\Contracts\Ports\ApprovedRequestReadPort;
use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Domain\ValueObjects\RequestId;

final class ApprovedRequestReadBridge implements ApprovedRequestReadPort
{
    public function __construct(
        private readonly RequestReadContract $requests,
    ) {}

    public function getApprovedSummary(string $requestId): ?RequestSummaryDTO
    {
        $summary = $this->requests->getRequestSummary(RequestId::fromString($requestId));

        if ($summary === null || $summary->status !== 'approved') {
            return null;
        }

        return $summary;
    }
}
