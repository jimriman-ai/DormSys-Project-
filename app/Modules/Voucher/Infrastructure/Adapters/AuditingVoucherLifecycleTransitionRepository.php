<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Adapters;

use App\Modules\Voucher\Application\Contracts\VoucherLifecycleTransitionRepositoryContract;
use App\Modules\Voucher\Application\Services\VoucherAuditRecordingAdapter;
use App\Modules\Voucher\Domain\Models\VoucherLifecycleTransition;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use App\Modules\Voucher\Infrastructure\Repositories\VoucherLifecycleTransitionRepository;

final class AuditingVoucherLifecycleTransitionRepository implements VoucherLifecycleTransitionRepositoryContract
{
    public function __construct(
        private readonly VoucherLifecycleTransitionRepository $inner,
        private readonly VoucherAuditRecordingAdapter $auditRecording,
    ) {}

    public function save(VoucherLifecycleTransition $transition): VoucherLifecycleTransition
    {
        $saved = $this->inner->save($transition);
        $this->auditRecording->recordTransition($saved);

        return $saved;
    }

    public function findByVoucherId(VoucherId $voucherId): array
    {
        return $this->inner->findByVoucherId($voucherId);
    }
}
