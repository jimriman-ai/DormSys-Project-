<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Domain\Models\VoucherLifecycleTransition;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;

interface VoucherLifecycleTransitionRepositoryContract
{
    public function save(VoucherLifecycleTransition $transition): VoucherLifecycleTransition;

    /**
     * @return list<VoucherLifecycleTransition>
     */
    public function findByVoucherId(VoucherId $voucherId): array;
}
