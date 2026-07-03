<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\ValueObjects\EligibilityOutcomeId;

interface VoucherIssuanceContract
{
    public function issueFromEligibility(EligibilityOutcomeId $eligibilityOutcomeId): Voucher;
}
