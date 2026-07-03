<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Enums;

enum DeferredReasonCode: string
{
    case ClassificationPending = 'classification_pending';
}
