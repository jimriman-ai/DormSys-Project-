<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Domain\Events\LotteryProgramStateChanged;
use App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class OpenRegistrationAction
{
    public function __construct(
        private readonly LotteryProgramRepositoryContract $programs,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly LotteryMutationAuthorizationGate $lotteryMutationAuth,
    ) {}

    public function execute(LotteryProgramId $programId): LotteryProgram
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::LOTTERY_PROGRAM_OPEN_REGISTRATION, [
            'programId' => $programId->value,
        ]);
        $this->lotteryMutationAuth->assertManageProgram();

        $program = $this->programs->findById($programId);

        if ($program === null) {
            throw new LotteryProgramNotFoundException('Lottery program not found.');
        }

        if (! $program->canOpenRegistration()) {
            throw new InvalidLotteryTransitionException(
                'Only draft or approved programs can open registration.',
            );
        }

        $previousStatus = $program->status;
        $opened = $program->markRegistrationOpen();

        return $this->persistTransition($opened, $previousStatus);
    }

    private function persistTransition(LotteryProgram $program, string $previousStatus): LotteryProgram
    {
        return DB::transaction(function () use ($program, $previousStatus): LotteryProgram {
            $persisted = $this->programs->save($program);

            Event::dispatch(LotteryProgramStateChanged::forProgram(
                programId: $persisted->requireId()->value,
                previousStatus: $previousStatus,
                newStatus: $persisted->status,
            ));

            return $persisted;
        });
    }
}
