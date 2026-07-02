<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Repositories;

use App\Modules\Voucher\Application\Contracts\VoucherRepositoryContract;
use App\Modules\Voucher\Domain\Models\Voucher;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\EligibilityOutcomeId;
use App\Modules\Voucher\Domain\ValueObjects\StayPeriod;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;
use App\Modules\Voucher\Domain\ValueObjects\VoucherCode;
use App\Modules\Voucher\Domain\ValueObjects\VoucherId;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherModel;

class VoucherRepository implements VoucherRepositoryContract
{
    public function save(Voucher $voucher): Voucher
    {
        if ($voucher->id === null) {
            return $this->insert($voucher);
        }

        return $this->update($voucher);
    }

    public function findById(VoucherId $id): ?Voucher
    {
        $model = VoucherModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByEligibilityOutcomeId(EligibilityOutcomeId $eligibilityOutcomeId): ?Voucher
    {
        $model = VoucherModel::query()
            ->where('eligibility_outcome_id', $eligibilityOutcomeId->value)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function codeExists(VoucherCode $code): bool
    {
        return VoucherModel::query()
            ->where('code', $code->value)
            ->exists();
    }

    private function insert(Voucher $voucher): Voucher
    {
        $model = new VoucherModel([
            'eligibility_outcome_id' => $voucher->eligibilityOutcomeId->value,
            'trigger_id' => $voucher->triggerId->value,
            'correlation_id' => $voucher->correlationId->value,
            'employee_id' => $voucher->employeeId,
            'dormitory_id' => $voucher->dormitoryId,
            'request_id' => $voucher->requestId,
            'upstream_source' => $voucher->upstreamSource,
            'code' => $voucher->code->value,
            'lifecycle_state' => $voucher->lifecycleState,
            'stay_period' => $voucher->stayPeriod->toPostgresDaterange(),
            'validity_start' => $voucher->validityStart,
            'validity_end' => $voucher->validityEnd,
            'issued_at' => $voucher->issuedAt,
            'archived_at' => $voucher->archivedAt,
        ]);
        $model->save();

        return $this->toDomain($model->refresh());
    }

    private function update(Voucher $voucher): Voucher
    {
        $model = VoucherModel::query()->findOrFail($voucher->requireId()->value);
        $model->fill([
            'lifecycle_state' => $voucher->lifecycleState,
            'archived_at' => $voucher->archivedAt,
        ]);
        $model->save();

        return $this->toDomain($model->refresh());
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
