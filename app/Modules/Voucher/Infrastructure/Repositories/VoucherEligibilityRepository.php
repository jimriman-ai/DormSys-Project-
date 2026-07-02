<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Repositories;

use App\Modules\Voucher\Application\Contracts\VoucherEligibilityRepositoryContract;
use App\Modules\Voucher\Domain\Models\VoucherEligibilityOutcome;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\EligibilityOutcomeId;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherEligibilityOutcomeModel;

class VoucherEligibilityRepository implements VoucherEligibilityRepositoryContract
{
    public function save(VoucherEligibilityOutcome $outcome): VoucherEligibilityOutcome
    {
        if ($outcome->id === null) {
            return $this->insert($outcome);
        }

        return $this->update($outcome);
    }

    public function findByTriggerId(TriggerId $triggerId): ?VoucherEligibilityOutcome
    {
        $model = VoucherEligibilityOutcomeModel::query()
            ->where('trigger_id', $triggerId->value)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function findById(EligibilityOutcomeId $id): ?VoucherEligibilityOutcome
    {
        $model = VoucherEligibilityOutcomeModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    private function insert(VoucherEligibilityOutcome $outcome): VoucherEligibilityOutcome
    {
        $model = new VoucherEligibilityOutcomeModel([
            'trigger_id' => $outcome->triggerId->value,
            'correlation_id' => $outcome->correlationId->value,
            'employee_id' => $outcome->employeeId,
            'dormitory_id' => $outcome->dormitoryId,
            'request_id' => $outcome->requestId,
            'outcome' => $outcome->outcome,
            'reason_codes' => $outcome->reasonCodes,
            'rationale' => $outcome->rationale,
            'evaluated_at' => $outcome->evaluatedAt,
        ]);
        $model->save();

        return $this->toDomain($model->refresh());
    }

    private function update(VoucherEligibilityOutcome $outcome): VoucherEligibilityOutcome
    {
        $model = VoucherEligibilityOutcomeModel::query()->findOrFail($outcome->requireId()->value);
        $model->fill([
            'outcome' => $outcome->outcome,
            'reason_codes' => $outcome->reasonCodes,
            'rationale' => $outcome->rationale,
            'evaluated_at' => $outcome->evaluatedAt,
        ]);
        $model->save();

        return $this->toDomain($model->refresh());
    }

    private function toDomain(VoucherEligibilityOutcomeModel $model): VoucherEligibilityOutcome
    {
        return new VoucherEligibilityOutcome(
            id: EligibilityOutcomeId::fromString($model->getId()),
            triggerId: TriggerId::fromString($model->trigger_id),
            correlationId: CorrelationId::fromString($model->correlation_id),
            employeeId: $model->employee_id,
            dormitoryId: $model->dormitory_id,
            requestId: $model->request_id,
            outcome: $model->outcome,
            reasonCodes: $model->reason_codes,
            rationale: $model->rationale,
            evaluatedAt: $model->evaluated_at->toDateTimeImmutable(),
        );
    }
}
