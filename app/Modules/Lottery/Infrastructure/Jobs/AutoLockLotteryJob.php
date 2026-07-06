<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Jobs;

use App\Application\Mutation\Support\MutationPrincipalContext;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class AutoLockLotteryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(
        LotteryProgramRepositoryContract $programs,
        CloseRegistrationAction $closeRegistration,
        LockLotteryProgramAction $lockProgram,
    ): void {
        MutationPrincipalContext::runJobAsSystem(function () use ($programs, $closeRegistration, $lockProgram): void {
            $asOf = now('UTC')->toDateTimeImmutable();

            foreach ($programs->findPastRegistrationEndEligibleForAutoLock($asOf) as $program) {
                $this->processProgram($program, $programs, $closeRegistration, $lockProgram);
            }
        });
    }

    private function processProgram(
        LotteryProgram $program,
        LotteryProgramRepositoryContract $programs,
        CloseRegistrationAction $closeRegistration,
        LockLotteryProgramAction $lockProgram,
    ): void {
        $programId = $program->requireId();

        try {
            $current = $programs->findById($programId) ?? $program;

            if ($current->canCloseRegistration()) {
                $current = $closeRegistration->execute($programId);
            }

            if ($current->canLock()) {
                $lockProgram->execute($programId);
            }
        } catch (InvalidLotteryTransitionException) {
            // Another worker or manual action already advanced the program.
        }
    }
}
