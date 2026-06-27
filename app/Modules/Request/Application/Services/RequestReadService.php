<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\Internal\RequestReadQueryPort;
use App\Modules\Request\Application\Contracts\RequestApprovalRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\DTOs\RequestApprovalHistoryDTO;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Domain\ValueObjects\RequestId;

final class RequestReadService implements RequestReadContract
{
    public function __construct(
        private readonly RequestReadQueryPort $queries,
        private readonly RequestApprovalRepositoryContract $approvals,
    ) {}

    public function requestExists(RequestId $id): bool
    {
        return $this->queries->exists($id);
    }

    public function getRequestSummary(RequestId $id): ?RequestSummaryDTO
    {
        return $this->queries->findSummaryById($id);
    }

    public function listApprovedByEmployee(string $employeeId): array
    {
        return $this->queries->listApprovedByEmployee($employeeId);
    }

    public function listApprovedByType(string $requestType): array
    {
        return $this->queries->listApprovedByType($requestType);
    }

    public function getApprovalHistory(RequestId $id): array
    {
        if (! $this->queries->exists($id)) {
            return [];
        }

        return array_map(
            static fn ($approval): RequestApprovalHistoryDTO => new RequestApprovalHistoryDTO(
                stage: $approval->stage->value,
                decision: $approval->decision->value,
                approverId: $approval->approverId->value,
                reason: $approval->reason,
                decidedAt: $approval->decidedAt->format(DATE_ATOM),
            ),
            $this->approvals->listForRequest($id),
        );
    }
}
