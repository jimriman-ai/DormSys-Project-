<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Services;

use App\Modules\Voucher\Application\Contracts\VoucherReadContract;
use App\Modules\Voucher\Application\Contracts\VoucherReadRepositoryContract;
use App\Modules\Voucher\Application\DTOs\VoucherReadProjection;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\ValueObjects\VoucherCode;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use App\Support\Exceptions\ValidationException;

final class VoucherReadService implements VoucherReadContract
{
    public function __construct(
        private readonly VoucherReadRepositoryContract $vouchers,
    ) {}

    public function getById(string $voucherId): ?VoucherReadProjection
    {
        $voucher = $this->vouchers->findById(VoucherId::fromString($voucherId));

        return $voucher === null ? null : VoucherReadProjection::fromDomain($voucher);
    }

    public function findByCode(string $code): ?VoucherReadProjection
    {
        $voucher = $this->vouchers->findByCode(VoucherCode::fromString($code));

        return $voucher === null ? null : VoucherReadProjection::fromDomain($voucher);
    }

    public function listForEmployee(string $employeeId, ?string $lifecycleState = null): array
    {
        $state = $this->parseLifecycleState($lifecycleState);

        return array_map(
            fn (Voucher $voucher): VoucherReadProjection => VoucherReadProjection::fromDomain($voucher),
            $this->vouchers->findByEmployeeId($employeeId, $state),
        );
    }

    private function parseLifecycleState(?string $lifecycleState): ?VoucherLifecycleState
    {
        if ($lifecycleState === null || trim($lifecycleState) === '') {
            return null;
        }

        return VoucherLifecycleState::tryFrom(strtolower(trim($lifecycleState)))
            ?? throw new ValidationException('Invalid voucher lifecycle state filter.');
    }
}
