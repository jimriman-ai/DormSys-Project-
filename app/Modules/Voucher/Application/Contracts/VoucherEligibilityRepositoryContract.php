<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Domain\Models\VoucherEligibilityOutcome;
use App\Modules\Voucher\Domain\ValueObjects\EligibilityOutcomeId;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;

interface VoucherEligibilityRepositoryContract
{
    public function save(VoucherEligibilityOutcome $outcome): VoucherEligibilityOutcome;

    public function findByTriggerId(TriggerId $triggerId): ?VoucherEligibilityOutcome;

    public function findById(EligibilityOutcomeId $id): ?VoucherEligibilityOutcome;
}
