<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Enums;

enum ReservePromotionDisposition: string
{
    case Issued = 'issued';
    case NoEligibleReserves = 'no_eligible_reserves';
    case ReserveIneligible = 'reserve_ineligible';
    case ReserveDeferred = 'reserve_deferred';
    case IgnoredInternalProgram = 'ignored_internal_program';
    case DuplicateRejected = 'duplicate_rejected';
}
