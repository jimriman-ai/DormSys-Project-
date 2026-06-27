<?php

declare(strict_types=1);

namespace App\Modules\Employee\Application\Contracts;

use App\Modules\Employee\Application\DTOs\EligibilityResultDTO;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;

interface EmployeeEligibilityContract
{
    public function computeRequestEligibility(EmployeeId $employeeId, ?string $excludingRequestId = null): EligibilityResultDTO;
}
