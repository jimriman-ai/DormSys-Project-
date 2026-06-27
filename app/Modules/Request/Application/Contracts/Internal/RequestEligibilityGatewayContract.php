<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts\Internal;

use App\Modules\Employee\Application\DTOs\EligibilityResultDTO;

interface RequestEligibilityGatewayContract
{
    public function computeRequestEligibility(string $employeeId, ?string $excludingRequestId = null): EligibilityResultDTO;
}
