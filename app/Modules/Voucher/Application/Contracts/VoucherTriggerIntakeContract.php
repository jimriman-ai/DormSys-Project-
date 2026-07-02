<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Application\DTOs\InboundTriggerFactsDto;
use App\Modules\Voucher\Domain\Models\VoucherIssuanceTrigger;

interface VoucherTriggerIntakeContract
{
    public function accept(InboundTriggerFactsDto $facts): VoucherIssuanceTrigger;
}
