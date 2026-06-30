<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Repositories;

use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Models\LotteryRegistration;
use App\Modules\Lottery\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryRegistrationId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Lottery\Infrastructure\Persistence\Models\LotteryRegistrationModel;
use DateTimeImmutable;
use DateTimeZone;

class LotteryRegistrationRepository implements LotteryRegistrationRepositoryContract
{
    public function save(LotteryRegistration $registration): LotteryRegistration
    {
        if ($registration->id === null) {
            $model = new LotteryRegistrationModel([
                'program_id' => $registration->programId->value,
                'request_id' => $registration->requestId->value,
                'employee_id' => $registration->employeeId->value,
                'weighted_score' => $registration->weightedScore,
                'enrolled_at' => $registration->enrolledAt->format('Y-m-d H:i:s'),
            ]);
            $model->save();

            return $this->toDomain($model);
        }

        $model = LotteryRegistrationModel::query()->find($registration->requireId()->value);

        if ($model === null) {
            throw new LotteryProgramNotFoundException('Lottery registration not found.');
        }

        $model->fill([
            'program_id' => $registration->programId->value,
            'request_id' => $registration->requestId->value,
            'employee_id' => $registration->employeeId->value,
            'weighted_score' => $registration->weightedScore,
            'enrolled_at' => $registration->enrolledAt->format('Y-m-d H:i:s'),
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function findById(LotteryRegistrationId $id): ?LotteryRegistration
    {
        $model = LotteryRegistrationModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByProgramAndRequest(
        LotteryProgramId $programId,
        RequestReferenceId $requestId,
    ): ?LotteryRegistration {
        $model = LotteryRegistrationModel::query()
            ->where('program_id', $programId->value)
            ->where('request_id', $requestId->value)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByProgramId(LotteryProgramId $programId): array
    {
        $registrations = LotteryRegistrationModel::query()
            ->where('program_id', $programId->value)
            ->orderBy('enrolled_at')
            ->get()
            ->map(fn (LotteryRegistrationModel $model): LotteryRegistration => $this->toDomain($model))
            ->all();

        return array_values($registrations);
    }

    private function toDomain(LotteryRegistrationModel $model): LotteryRegistration
    {
        return new LotteryRegistration(
            id: LotteryRegistrationId::fromString($model->id),
            programId: LotteryProgramId::fromString($model->program_id),
            requestId: RequestReferenceId::fromString($model->request_id),
            employeeId: EmployeeReferenceId::fromString($model->employee_id),
            enrolledAt: $this->toImmutable($model->enrolled_at),
            weightedScore: $model->weighted_score === null ? null : (float) $model->weighted_score,
        );
    }

    private function toImmutable(\DateTimeInterface $value): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($value)
            ->setTimezone(new DateTimeZone('UTC'));
    }
}
