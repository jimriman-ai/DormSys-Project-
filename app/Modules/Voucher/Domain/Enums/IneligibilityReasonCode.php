<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Enums;

enum IneligibilityReasonCode: string
{
    case MissingDormitoryReference = 'missing_dormitory_reference';
    case NotExternalDormitory = 'not_external_dormitory';
    case InternalAssignmentPath = 'internal_assignment_path';
}
