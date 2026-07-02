<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Services;

use App\Modules\Voucher\Domain\ValueObjects\VoucherCode;

class VoucherCodeGenerator
{
    public function generate(): VoucherCode
    {
        return VoucherCode::fromString(bin2hex(random_bytes(16)));
    }
}
