<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts\Internal;

use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Domain\ValueObjects\RequestId;

interface RequestReadQueryPort
{
    public function exists(RequestId $id): bool;

    public function findSummaryById(RequestId $id): ?RequestSummaryDTO;

    /**
     * @return list<RequestSummaryDTO>
     */
    public function listApprovedByEmployee(string $employeeId): array;

    /**
     * @return list<RequestSummaryDTO>
     */
    public function listApprovedByType(string $requestType): array;
}
