<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Enums;

enum EligibilityReasonCode: string
{
    case EmployeeInactive = 'employee_inactive';
    case ActiveAllocationExists = 'active_allocation_exists';
    case PendingRequestExists = 'pending_request_exists';
}
