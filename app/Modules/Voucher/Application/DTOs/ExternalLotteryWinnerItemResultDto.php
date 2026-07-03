<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\DTOs;

use App\Modules\Voucher\Domain\Enums\ExternalLotteryWinnerDisposition;
use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\Models\VoucherEligibilityOutcome;

final readonly class ExternalLotteryWinnerItemResultDto
{
    public function __construct(
        public string $correlationId,
        public ExternalLotteryWinnerDisposition $disposition,
        public ?Voucher $voucher = null,
        public ?VoucherEligibilityOutcome $eligibilityOutcome = null,
    ) {}
}
