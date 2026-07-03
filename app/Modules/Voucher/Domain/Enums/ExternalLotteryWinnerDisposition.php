<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Enums;

enum ExternalLotteryWinnerDisposition: string
{
    case Issued = 'issued';
    case NotEligible = 'not_eligible';
    case Deferred = 'deferred';
    case SkippedCapacity = 'skipped_capacity';
    case DuplicateRejected = 'duplicate_rejected';
}
