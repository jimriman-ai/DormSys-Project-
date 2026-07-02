<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Contracts;

use App\Modules\Voucher\Application\DTOs\VoucherReadProjection;

interface VoucherReadContract
{
    public function getById(string $voucherId): ?VoucherReadProjection;

    public function findByCode(string $code): ?VoucherReadProjection;

    /**
     * @return list<VoucherReadProjection>
     */
    public function listForEmployee(string $employeeId, ?string $lifecycleState = null): array;
}
