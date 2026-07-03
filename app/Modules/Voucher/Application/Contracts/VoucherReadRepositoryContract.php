<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\ValueObjects\VoucherCode;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;

interface VoucherReadRepositoryContract
{
    public function findById(VoucherId $id): ?Voucher;

    public function findByCode(VoucherCode $code): ?Voucher;

    /**
     * @return list<Voucher>
     */
    public function findByEmployeeId(string $employeeId, ?VoucherLifecycleState $lifecycleState = null): array;
}
