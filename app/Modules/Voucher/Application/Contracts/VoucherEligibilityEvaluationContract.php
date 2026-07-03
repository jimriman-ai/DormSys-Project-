<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Domain\Models\VoucherEligibilityOutcome;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;

interface VoucherEligibilityEvaluationContract
{
    public function evaluateForTrigger(TriggerId $triggerId): VoucherEligibilityOutcome;
}
