<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Repositories;

use App\Modules\Lottery\Application\Contracts\LotteryResultRepositoryContract;
use App\Modules\Lottery\Domain\Enums\LotteryResultOutcome;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Models\LotteryResult;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryRegistrationId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryResultId;
use App\Modules\Lottery\Infrastructure\Persistence\Models\LotteryResultModel;

class LotteryResultRepository implements LotteryResultRepositoryContract
{
    public function save(LotteryResult $result): LotteryResult
    {
        if ($result->id === null) {
            $model = new LotteryResultModel([
                'program_id' => $result->programId->value,
                'registration_id' => $result->registrationId->value,
                'rank' => $result->rank,
                'outcome' => $result->outcome,
            ]);
            $model->save();

            return $this->toDomain($model);
        }

        $model = LotteryResultModel::query()->find($result->requireId()->value);

        if ($model === null) {
            throw new LotteryProgramNotFoundException('Lottery result not found.');
        }

        $model->fill([
            'program_id' => $result->programId->value,
            'registration_id' => $result->registrationId->value,
            'rank' => $result->rank,
            'outcome' => $result->outcome,
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function findById(LotteryResultId $id): ?LotteryResult
    {
        $model = LotteryResultModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByProgramId(LotteryProgramId $programId): array
    {
        return LotteryResultModel::query()
            ->where('program_id', $programId->value)
            ->orderBy('rank')
            ->get()
            ->map(fn (LotteryResultModel $model): LotteryResult => $this->toDomain($model))
            ->all();
    }

    private function toDomain(LotteryResultModel $model): LotteryResult
    {
        return new LotteryResult(
            id: LotteryResultId::fromString($model->id),
            programId: LotteryProgramId::fromString($model->program_id),
            registrationId: LotteryRegistrationId::fromString($model->registration_id),
            rank: (int) $model->rank,
            outcome: $model->outcome instanceof LotteryResultOutcome
                ? $model->outcome
                : LotteryResultOutcome::from((string) $model->outcome),
        );
    }
}
