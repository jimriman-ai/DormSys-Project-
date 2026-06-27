<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Adapters;

use App\Modules\Employee\Application\Contracts\EmployeeEligibilityContract;
use App\Modules\Employee\Application\DTOs\EligibilityResultDTO;
use App\Modules\Employee\Domain\ValueObjects\EmployeeId;
use App\Modules\Request\Application\Contracts\Internal\RequestEligibilityGatewayContract;

final class EmployeeEligibilityGateway implements RequestEligibilityGatewayContract
{
    public function __construct(
        private readonly EmployeeEligibilityContract $eligibility,
    ) {}

    public function computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null): EligibilityResultDTO
    {
        return $this->eligibility->computeRequestEligibility(
            EmployeeId::fromString($employeeId),
            $excludingRequestId,
        );
    }
}
