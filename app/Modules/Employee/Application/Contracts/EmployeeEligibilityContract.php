<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Contracts;

use App\Modules\Employee\Application\DTOs\EligibilityResultDTO;

interface EmployeeEligibilityContract
{
    public function computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null): EligibilityResultDTO;
}
