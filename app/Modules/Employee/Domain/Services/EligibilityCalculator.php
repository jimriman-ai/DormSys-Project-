<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Services;

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\Enums\EligibilityReasonCode;

final class EligibilityCalculator
{
    /**
     * @return list<EligibilityReasonCode>
     */
    public function evaluate(
        Employee $employee,
        bool $hasActiveAllocation,
        bool $hasPendingRequest,
    ): array {
        if (! $employee->isActive()) {
            return [EligibilityReasonCode::EmployeeInactive];
        }

        $reasonCodes = [];

        if ($hasActiveAllocation) {
            $reasonCodes[] = EligibilityReasonCode::ActiveAllocationExists;
        }

        if ($hasPendingRequest) {
            $reasonCodes[] = EligibilityReasonCode::PendingRequestExists;
        }

        return $reasonCodes;
    }
}
