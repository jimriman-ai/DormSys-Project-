<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Repositories;

use App\Modules\Lottery\Application\Contracts\LotteryEligibleSnapshotRepositoryContract;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Models\EligibleSnapshot;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotterySnapshotId;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;
use App\Modules\Lottery\Infrastructure\Persistence\Models\LotteryEligibleSnapshotModel;

class LotteryEligibleSnapshotRepository implements LotteryEligibleSnapshotRepositoryContract
{
    public function save(EligibleSnapshot $snapshot): EligibleSnapshot
    {
        if ($snapshot->id === null) {
            $model = new LotteryEligibleSnapshotModel([
                'program_id' => $snapshot->programId->value,
                'payload' => $snapshot->payload,
                'random_seed' => $snapshot->randomSeed,
                'scoring_config' => $snapshot->scoringConfig->toArray(),
                'scoring_config_version' => $snapshot->scoringConfig->version,
            ]);
            $model->save();

            return $this->toDomain($model);
        }

        $model = LotteryEligibleSnapshotModel::query()->find($snapshot->requireId()->value);

        if ($model === null) {
            throw new LotteryProgramNotFoundException('Eligible snapshot not found.');
        }

        $model->fill([
            'program_id' => $snapshot->programId->value,
            'payload' => $snapshot->payload,
            'random_seed' => $snapshot->randomSeed,
            'scoring_config' => $snapshot->scoringConfig->toArray(),
            'scoring_config_version' => $snapshot->scoringConfig->version,
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function findByProgramId(LotteryProgramId $programId): ?EligibleSnapshot
    {
        $model = LotteryEligibleSnapshotModel::query()
            ->where('program_id', $programId->value)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    private function toDomain(LotteryEligibleSnapshotModel $model): EligibleSnapshot
    {
        /** @var array<string, mixed> $scoringConfig */
        $scoringConfig = $model->scoring_config;

        return new EligibleSnapshot(
            id: LotterySnapshotId::fromString($model->id),
            programId: LotteryProgramId::fromString($model->program_id),
            payload: $model->payload,
            randomSeed: $model->random_seed,
            scoringConfig: ScoringConfig::fromArray($scoringConfig),
        );
    }
}
