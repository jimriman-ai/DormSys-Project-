<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Repositories;

use App\Modules\Voucher\Application\Contracts\VoucherReadRepositoryContract;
use App\Modules\Voucher\Domain\Enums\VoucherLifecycleState;
use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\EligibilityOutcomeId;
use App\Modules\Voucher\Domain\ValueObjects\StayPeriod;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;
use App\Modules\Voucher\Domain\ValueObjects\VoucherCode;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherModel;

final class VoucherReadRepository implements VoucherReadRepositoryContract
{
    public function findById(VoucherId $id): ?Voucher
    {
        $model = VoucherModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByCode(VoucherCode $code): ?Voucher
    {
        $model = VoucherModel::query()
            ->where('code', $code->value)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByEmployeeId(string $employeeId, ?VoucherLifecycleState $lifecycleState = null): array
    {
        $query = VoucherModel::query()
            ->where('employee_id', $employeeId)
            ->orderByDesc('issued_at')
            ->orderByDesc('id');

        if ($lifecycleState !== null) {
            $query->where('lifecycle_state', $lifecycleState->value);
        }

        return array_values($query
            ->get()
            ->map(fn (VoucherModel $model): Voucher => $this->toDomain($model))
            ->all());
    }

    private function toDomain(VoucherModel $model): Voucher
    {
        return new Voucher(
            id: VoucherId::fromString($model->getId()),
            eligibilityOutcomeId: EligibilityOutcomeId::fromString($model->eligibility_outcome_id),
            triggerId: TriggerId::fromString($model->trigger_id),
            correlationId: CorrelationId::fromString($model->correlation_id),
            employeeId: $model->employee_id,
            dormitoryId: $model->dormitory_id,
            requestId: $model->request_id,
            upstreamSource: $model->upstream_source,
            code: VoucherCode::fromString($model->code),
            lifecycleState: $model->lifecycle_state,
            stayPeriod: StayPeriod::fromPostgresDaterange($model->stay_period),
            validityStart: $model->validity_start->toDateTimeImmutable(),
            validityEnd: $model->validity_end->toDateTimeImmutable(),
            issuedAt: $model->issued_at->toDateTimeImmutable(),
            archivedAt: $model->archived_at?->toDateTimeImmutable(),
        );
    }
}
