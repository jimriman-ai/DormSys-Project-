<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Repositories;

use App\Modules\Voucher\Application\Contracts\VoucherLifecycleTransitionRepositoryContract;
use App\Modules\Voucher\Domain\Models\VoucherLifecycleTransition;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherLifecycleTransitionModel;

class VoucherLifecycleTransitionRepository implements VoucherLifecycleTransitionRepositoryContract
{
    public function save(VoucherLifecycleTransition $transition): VoucherLifecycleTransition
    {
        $model = new VoucherLifecycleTransitionModel([
            'voucher_id' => $transition->voucherId->value,
            'from_state' => $transition->fromState,
            'to_state' => $transition->toState,
            'correlation_id' => $transition->correlationId->value,
            'occurred_at' => $transition->occurredAt,
            'payload' => $transition->payload,
        ]);
        $model->save();

        return $this->toDomain($model->refresh());
    }

    public function findByVoucherId(VoucherId $voucherId): array
    {
        $transitions = [];

        foreach (
            VoucherLifecycleTransitionModel::query()
                ->where('voucher_id', $voucherId->value)
                ->orderBy('occurred_at')
                ->get() as $model
        ) {
            $transitions[] = $this->toDomain($model);
        }

        return $transitions;
    }

    private function toDomain(VoucherLifecycleTransitionModel $model): VoucherLifecycleTransition
    {
        return new VoucherLifecycleTransition(
            id: $model->getId(),
            voucherId: VoucherId::fromString($model->voucher_id),
            fromState: $model->from_state,
            toState: $model->to_state,
            correlationId: CorrelationId::fromString($model->correlation_id),
            occurredAt: $model->occurred_at->toDateTimeImmutable(),
            payload: $model->payload,
        );
    }
}
