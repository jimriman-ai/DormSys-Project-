<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

use App\Modules\Request\Application\DTOs\EmployeeRequestListQueryDTO;
use App\Modules\Request\Application\DTOs\PaginatedRequestSummaryListDTO;
use App\Modules\Request\Application\DTOs\RequestApprovalHistoryDTO;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Domain\ValueObjects\RequestId;

interface RequestReadContract
{
    public function requestExists(RequestId $id): bool;

    public function getRequestSummary(RequestId $id): ?RequestSummaryDTO;

    /**
     * @return list<RequestSummaryDTO>
     */
    public function listByEmployee(string $employeeId): array;

    public function listByEmployeePaginated(EmployeeRequestListQueryDTO $query): PaginatedRequestSummaryListDTO;

    /**
     * @return list<RequestSummaryDTO>
     */
    public function listApprovedByEmployee(string $employeeId): array;

    /**
     * @return list<RequestSummaryDTO>
     */
    public function listApprovedByType(string $requestType): array;

    /**
     * @return list<RequestApprovalHistoryDTO>
     */
    public function getApprovalHistory(RequestId $id): array;
}
