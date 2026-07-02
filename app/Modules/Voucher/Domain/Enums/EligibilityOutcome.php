<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Enums;

enum EligibilityOutcome: string
{
    case Eligible = 'eligible';
    case Ineligible = 'ineligible';
    case Deferred = 'deferred';
}
