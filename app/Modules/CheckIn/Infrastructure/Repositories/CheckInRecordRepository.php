<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Infrastructure\Repositories;

use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Domain\Models\CheckInRecord;
use App\Modules\CheckIn\Domain\ValueObjects\CheckInRecordId;
use App\Modules\CheckIn\Infrastructure\Persistence\Models\CheckInRecordModel;
use DateTimeImmutable;

class CheckInRecordRepository implements CheckInRecordRepositoryContract
{
    public function save(CheckInRecord $record): CheckInRecord
    {
        if ($record->id === null) {
            return $this->insert($record);
        }

        return $this->update($record);
    }

    public function findById(CheckInRecordId $id): ?CheckInRecord
    {
        $model = CheckInRecordModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findOpenByAllocationId(string $allocationId): ?CheckInRecord
    {
        $model = CheckInRecordModel::query()
            ->where('allocation_id', $allocationId)
            ->whereNull('checked_out_at')
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    private function insert(CheckInRecord $record): CheckInRecord
    {
        $model = new CheckInRecordModel([
            'allocation_id' => $record->allocationId,
            'checked_in_at' => $record->checkedInAt,
            'checked_out_at' => $record->checkedOutAt,
            'operator_id' => $record->operatorId,
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    private function update(CheckInRecord $record): CheckInRecord
    {
        $model = CheckInRecordModel::query()->find($record->requireId()->value);

        if ($model === null) {
            return $this->insert($record);
        }

        $model->fill([
            'allocation_id' => $record->allocationId,
            'checked_in_at' => $record->checkedInAt,
            'checked_out_at' => $record->checkedOutAt,
            'operator_id' => $record->operatorId,
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    private function toDomain(CheckInRecordModel $model): CheckInRecord
    {
        return new CheckInRecord(
            id: CheckInRecordId::fromString($model->getId()),
            allocationId: $model->allocation_id,
            checkedInAt: DateTimeImmutable::createFromInterface($model->checked_in_at),
            checkedOutAt: $model->checked_out_at !== null
                ? DateTimeImmutable::createFromInterface($model->checked_out_at)
                : null,
            operatorId: $model->operator_id,
        );
    }
}
