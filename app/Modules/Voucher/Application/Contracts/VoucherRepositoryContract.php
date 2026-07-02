<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\ValueObjects\VoucherCode;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use App\Modules\Voucher\Domain\ValueObjects\EligibilityOutcomeId;

interface VoucherRepositoryContract
{
    public function save(Voucher $voucher): Voucher;

    public function findById(VoucherId $id): ?Voucher;

    public function findByEligibilityOutcomeId(EligibilityOutcomeId $eligibilityOutcomeId): ?Voucher;

    public function codeExists(VoucherCode $code): bool;
}
