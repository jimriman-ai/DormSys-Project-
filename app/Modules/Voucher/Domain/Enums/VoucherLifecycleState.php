<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Enums;

enum VoucherLifecycleState: string
{
    case Issued = 'issued';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Superseded = 'superseded';

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Issued => false,
            self::Expired, self::Cancelled, self::Superseded => true,
        };
    }
}
