<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Application\Services;

use App\Modules\Voucher\Application\Contracts\VoucherLifecycleContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleTransitionRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherRepositoryContract;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\Models\VoucherLifecycleTransition;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use DateTimeImmutable;
use DateTimeZone;

final class VoucherLifecycleAction implements VoucherLifecycleContract
{
    public function __construct(
        private readonly VoucherRepositoryContract $vouchers,
        private readonly VoucherLifecycleTransitionRepositoryContract $transitions,
    ) {}

    public function expire(VoucherId $voucherId, DateTimeImmutable $asOf): Voucher
    {
        $voucher = $this->requireVoucher($voucherId);
        $fromState = $voucher->lifecycleState;
        $expired = $voucher->expire($asOf);
        $saved = $this->vouchers->save($expired);
        $this->recordTransition($saved, $fromState, VoucherLifecycleState::Expired, $asOf);

        return $saved;
    }

    public function archive(VoucherId $voucherId, DateTimeImmutable $archivedAt): Voucher
    {
        $voucher = $this->requireVoucher($voucherId);
        $archived = $voucher->archive($archivedAt);

        return $this->vouchers->save($archived);
    }

    private function requireVoucher(VoucherId $voucherId): Voucher
    {
        $voucher = $this->vouchers->findById($voucherId);

        if ($voucher === null) {
            throw new \RuntimeException('Voucher not found.');
        }

        return $voucher;
    }

    private function recordTransition(
        Voucher $voucher,
        VoucherLifecycleState $fromState,
        VoucherLifecycleState $toState,
        DateTimeImmutable $occurredAt,
    ): void {
        $this->transitions->save(VoucherLifecycleTransition::record(
            voucherId: $voucher->requireId(),
            fromState: $fromState,
            toState: $toState,
            correlationId: $voucher->correlationId,
            occurredAt: $occurredAt,
            payload: [
                'voucher_id' => $voucher->requireId()->value,
                'employee_id' => $voucher->employeeId,
                'dormitory_id' => $voucher->dormitoryId,
                'request_id' => $voucher->requestId,
                'correlation_id' => $voucher->correlationId->value,
                'code' => $voucher->code->value,
                'upstream_source' => $voucher->upstreamSource->value,
                'from_state' => $fromState->value,
                'to_state' => $toState->value,
            ],
        ));
    }
}
