<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Repositories;

use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Infrastructure\Persistence\Models\LotteryProgramModel;
use DateTimeImmutable;
use DateTimeZone;

class LotteryProgramRepository implements LotteryProgramRepositoryContract
{
    public function save(LotteryProgram $program): LotteryProgram
    {
        if ($program->id === null) {
            $model = new LotteryProgramModel([
                'title' => $program->title,
                'dormitory_id' => $program->dormitoryId->value,
                'capacity' => $program->capacity,
                'registration_starts_at' => $program->registrationStartsAt->format('Y-m-d H:i:s'),
                'registration_ends_at' => $program->registrationEndsAt->format('Y-m-d H:i:s'),
                'status' => $program->status,
                'random_seed' => $program->randomSeed,
                'scoring_config_version' => $program->scoringConfigVersion,
                'cancelled_reason' => $program->cancelledReason,
                'locked_at' => $program->lockedAt?->format('Y-m-d H:i:s'),
                'drawn_at' => $program->drawnAt?->format('Y-m-d H:i:s'),
            ]);
            $model->save();

            return $this->toDomain($model);
        }

        $model = LotteryProgramModel::query()->find($program->requireId()->value);

        if ($model === null) {
            throw new LotteryProgramNotFoundException('Lottery program not found.');
        }

        $model->fill([
            'title' => $program->title,
            'dormitory_id' => $program->dormitoryId->value,
            'capacity' => $program->capacity,
            'registration_starts_at' => $program->registrationStartsAt->format('Y-m-d H:i:s'),
            'registration_ends_at' => $program->registrationEndsAt->format('Y-m-d H:i:s'),
            'status' => $program->status,
            'random_seed' => $program->randomSeed,
            'scoring_config_version' => $program->scoringConfigVersion,
            'cancelled_reason' => $program->cancelledReason,
            'locked_at' => $program->lockedAt?->format('Y-m-d H:i:s'),
            'drawn_at' => $program->drawnAt?->format('Y-m-d H:i:s'),
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function findById(LotteryProgramId $id): ?LotteryProgram
    {
        $model = LotteryProgramModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    private function toDomain(LotteryProgramModel $model): LotteryProgram
    {
        return new LotteryProgram(
            id: LotteryProgramId::fromString($model->id),
            title: $model->title,
            dormitoryId: DormitorySiteId::fromString($model->dormitory_id),
            capacity: (int) $model->capacity,
            registrationStartsAt: $this->toImmutable($model->registration_starts_at),
            registrationEndsAt: $this->toImmutable($model->registration_ends_at),
            status: (string) $model->status,
            randomSeed: $model->random_seed,
            scoringConfigVersion: $model->scoring_config_version,
            cancelledReason: $model->cancelled_reason,
            lockedAt: $model->locked_at === null ? null : $this->toImmutable($model->locked_at),
            drawnAt: $model->drawn_at === null ? null : $this->toImmutable($model->drawn_at),
        );
    }

    private function toImmutable(\DateTimeInterface $value): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($value)
            ->setTimezone(new DateTimeZone('UTC'));
    }
}
