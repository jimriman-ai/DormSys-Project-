<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\DTOs;

use App\Modules\Voucher\Domain\Enums\ReservePromotionDisposition;
use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\Models\VoucherEligibilityOutcome;
use App\Modules\Voucher\Domain\Models\VoucherIssuanceTrigger;

final readonly class ReservePromotionResultDto
{
    public function __construct(
        public ReservePromotionDisposition $disposition,
        public ?VoucherIssuanceTrigger $promotionTrigger = null,
        public ?Voucher $priorWinnerVoucher = null,
        public ?Voucher $reserveVoucher = null,
        public ?VoucherEligibilityOutcome $reserveEligibilityOutcome = null,
    ) {}

    public static function ignoredInternalProgram(): self
    {
        return new self(ReservePromotionDisposition::IgnoredInternalProgram);
    }

    public static function duplicateRejected(): self
    {
        return new self(ReservePromotionDisposition::DuplicateRejected);
    }
}
