<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Services;

use App\Modules\Employee\Application\Contracts\EmployeeEligibilityContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Application\Contracts\Ports\ActiveAllocationReadPort;
use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Employee\Application\DTOs\EligibilityResultDTO;
use App\Modules\Employee\Domain\Exceptions\EmployeeNotFoundException;
use App\Modules\Employee\Domain\Services\EligibilityCalculator;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

final class EmployeeEligibilityService implements EmployeeEligibilityContract
{
    public function __construct(
        private readonly EmployeeRepositoryContract $employees,
        private readonly ActiveAllocationReadPort $activeAllocations,
        private readonly PendingRequestReadPort $pendingRequests,
        private readonly EligibilityCalculator $calculator,
    ) {}

    public function computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null): EligibilityResultDTO
    {
        $id = EmployeeId::fromString($employeeId);
        $employee = $this->employees->findById($id);

        if ($employee === null) {
            throw new EmployeeNotFoundException('Employee not found.');
        }

        $reasonCodes = $this->calculator->evaluate(
            $employee,
            $this->activeAllocations->hasActiveAllocation($id),
            $this->pendingRequests->hasPendingRequest($employeeId, $excludingRequestId),
        );

        return new EligibilityResultDTO(
            eligible: $reasonCodes === [],
            reasonCodes: array_map(
                static fn ($code): string => $code->value,
                $reasonCodes,
            ),
            evaluatedAt: now('UTC')->toDateTimeImmutable(),
        );
    }
}
