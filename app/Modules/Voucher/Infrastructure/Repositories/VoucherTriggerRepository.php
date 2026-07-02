<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Repositories;

use App\Modules\Voucher\Application\Contracts\VoucherTriggerRepositoryContract;
use App\Modules\Voucher\Domain\Enums\TriggerIntakeStatus;
use App\Modules\Voucher\Domain\Exceptions\DuplicateTriggerCorrelationException;
use App\Modules\Voucher\Domain\Models\VoucherIssuanceTrigger;
use App\Modules\Voucher\Domain\ValueObjects\CorrelationId;
use App\Modules\Voucher\Domain\ValueObjects\StayPeriod;
use App\Modules\Voucher\Domain\ValueObjects\TriggerId;
use App\Modules\Voucher\Infrastructure\Persistence\Models\VoucherIssuanceTriggerModel;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Database\QueryException;

class VoucherTriggerRepository implements VoucherTriggerRepositoryContract
{
    public function save(VoucherIssuanceTrigger $trigger): VoucherIssuanceTrigger
    {
        try {
            if ($trigger->id === null) {
                return $this->insert($trigger);
            }

            return $this->update($trigger);
        } catch (QueryException $exception) {
            if ($this->isCorrelationViolation($exception)) {
                throw new DuplicateTriggerCorrelationException(
                    'Duplicate upstream trigger correlation identifier rejected.',
                    previous: $exception,
                );
            }

            throw $exception;
        }
    }

    public function findByCorrelationId(CorrelationId $correlationId): ?VoucherIssuanceTrigger
    {
        $model = VoucherIssuanceTriggerModel::query()
            ->where('correlation_id', $correlationId->value)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function findById(TriggerId $triggerId): ?VoucherIssuanceTrigger
    {
        $model = VoucherIssuanceTriggerModel::query()->find($triggerId->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findActiveOverlappingForEmployee(string $employeeId, StayPeriod $stayPeriod): array
    {
        $models = VoucherIssuanceTriggerModel::query()
            ->where('employee_id', $employeeId)
            ->where('status', TriggerIntakeStatus::Accepted)
            ->whereNull('issuance_path_completed_at')
            ->whereRaw('stay_period && ?::daterange', [$stayPeriod->toPostgresDaterange()])
            ->get();

        $triggers = [];

        foreach ($models as $model) {
            $triggers[] = $this->toDomain($model);
        }

        return $triggers;
    }

    public function markIssuancePathCompleted(TriggerId $triggerId): VoucherIssuanceTrigger
    {
        $model = VoucherIssuanceTriggerModel::query()->find($triggerId->value);

        if ($model === null) {
            throw new \RuntimeException('Trigger not found.');
        }

        $domain = $this->toDomain($model)->markIssuancePathCompleted(
            new DateTimeImmutable('now', new DateTimeZone('UTC')),
        );

        return $this->update($domain);
    }

    private function insert(VoucherIssuanceTrigger $trigger): VoucherIssuanceTrigger
    {
        $model = new VoucherIssuanceTriggerModel([
            'correlation_id' => $trigger->correlationId->value,
            'employee_id' => $trigger->employeeId,
            'dormitory_id' => $trigger->dormitoryId,
            'request_id' => $trigger->requestId,
            'stay_period' => $trigger->stayPeriod->toPostgresDaterange(),
            'source' => $trigger->source,
            'status' => $trigger->status,
            'issuance_path_completed_at' => $trigger->issuancePathCompletedAt,
            'superseded_by_trigger_id' => $trigger->supersededByTriggerId?->value,
            'upstream_facts' => $trigger->upstreamFacts,
        ]);
        $model->save();

        return $this->toDomain($model->refresh());
    }

    private function update(VoucherIssuanceTrigger $trigger): VoucherIssuanceTrigger
    {
        $model = VoucherIssuanceTriggerModel::query()->findOrFail($trigger->requireId()->value);
        $model->fill([
            'status' => $trigger->status,
            'issuance_path_completed_at' => $trigger->issuancePathCompletedAt,
            'superseded_by_trigger_id' => $trigger->supersededByTriggerId?->value,
        ]);
        $model->save();

        return $this->toDomain($model->refresh());
    }

    private function toDomain(VoucherIssuanceTriggerModel $model): VoucherIssuanceTrigger
    {
        return new VoucherIssuanceTrigger(
            id: TriggerId::fromString($model->getId()),
            correlationId: CorrelationId::fromString($model->correlation_id),
            employeeId: $model->employee_id,
            source: $model->source,
            stayPeriod: StayPeriod::fromPostgresDaterange($model->stay_period),
            status: $model->status,
            dormitoryId: $model->dormitory_id,
            requestId: $model->request_id,
            upstreamFacts: $model->upstream_facts,
            issuancePathCompletedAt: $model->issuance_path_completed_at?->toDateTimeImmutable(),
            supersededByTriggerId: $model->superseded_by_trigger_id !== null
                ? TriggerId::fromString($model->superseded_by_trigger_id)
                : null,
        );
    }

    private function isCorrelationViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;

        return $sqlState === '23505';
    }
}
