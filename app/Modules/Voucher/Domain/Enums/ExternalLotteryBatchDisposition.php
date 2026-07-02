<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Enums;

enum ExternalLotteryBatchDisposition: string
{
    case Processed = 'processed';
    case IgnoredInternalProgram = 'ignored_internal_program';
}
