<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Domain\Events\LotteryProgramCreated;
use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CreateLotteryProgramAction
{
    public function __construct(
        private readonly LotteryProgramRepositoryContract $programs,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly LotteryMutationAuthorizationGate $lotteryMutationAuth,
    ) {}

    public function execute(
        string $title,
        DormitorySiteId $dormitoryId,
        int $capacity,
        DateTimeImmutable $registrationStartsAt,
        DateTimeImmutable $registrationEndsAt,
    ): LotteryProgram {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::LOTTERY_PROGRAM_CREATE, [
            'dormitoryId' => $dormitoryId->value,
        ]);
        $this->lotteryMutationAuth->assertManageProgram();

        $this->validate($capacity, $registrationStartsAt, $registrationEndsAt);

        $program = LotteryProgram::createDraft(
            title: $title,
            dormitoryId: $dormitoryId,
            capacity: $capacity,
            registrationStartsAt: $registrationStartsAt,
            registrationEndsAt: $registrationEndsAt,
        );

        return DB::transaction(function () use ($program): LotteryProgram {
            $persisted = $this->programs->save($program);

            Event::dispatch(LotteryProgramCreated::forProgram(
                programId: $persisted->requireId()->value,
                dormitoryId: $persisted->dormitoryId->value,
                capacity: $persisted->capacity,
            ));

            return $persisted;
        });
    }

    private function validate(
        int $capacity,
        DateTimeImmutable $registrationStartsAt,
        DateTimeImmutable $registrationEndsAt,
    ): void {
        if ($capacity <= 0) {
            throw new LotteryValidationException('Program capacity must be greater than zero.');
        }

        if ($registrationEndsAt <= $registrationStartsAt) {
            throw new LotteryValidationException('Registration end must be after registration start.');
        }
    }
}
