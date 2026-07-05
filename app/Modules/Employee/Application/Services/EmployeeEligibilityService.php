<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Services;

use App\Modules\Employee\Application\Contracts\EmployeeEligibilityContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Employee\Application\DTOs\EligibilityResultDTO;
use App\Modules\Employee\Domain\Exceptions\EmployeeNotFoundException;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

final class EmployeeEligibilityService implements EmployeeEligibilityContract
{
    public function __construct(
        private readonly EmployeeRepositoryContract $employees,
        private readonly PendingRequestReadPort $pendingRequests,
    ) {}

    public function computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null): EligibilityResultDTO
    {
        $id = EmployeeId::fromString($employeeId);
        $employee = $this->employees->findById($id);

        if ($employee === null) {
            throw new EmployeeNotFoundException('Employee not found.');
        }

        $evaluatedAt = now('UTC')->toDateTimeImmutable();

        if (! $employee->isActive()) {
            return new EligibilityResultDTO(
                eligible: false,
                reasonCodes: ['employee_inactive'],
                evaluatedAt: $evaluatedAt,
            );
        }

        if ($this->pendingRequests->hasPendingRequest($employeeId, $excludingRequestId)) {
            return new EligibilityResultDTO(
                eligible: false,
                reasonCodes: ['pending_request_exists'],
                evaluatedAt: $evaluatedAt,
            );
        }

        return new EligibilityResultDTO(
            eligible: true,
            reasonCodes: [],
            evaluatedAt: $evaluatedAt,
        );
    }
}
