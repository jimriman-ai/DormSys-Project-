<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Jobs;

use App\Application\Mutation\Support\MutationPrincipalContext;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Domain\Exceptions\DrawNotAllowedException;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ExecuteLotteryDrawJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly ?string $programId = null,
    ) {}

    /**
     * System principal is established via {@see MutationPrincipalContext::runJobAsSystem()}.
     * Authorization denial propagates; only draw race conflicts are swallowed.
     */
    public function handle(
        LotteryProgramRepositoryContract $programs,
        ExecuteDrawAction $draw,
    ): void {
        MutationPrincipalContext::runJobAsSystem(function () use ($programs, $draw): void {
            if ($this->programId !== null) {
                $this->executeDraw(LotteryProgramId::fromString($this->programId), $draw);

                return;
            }

            foreach ($programs->findLockedReadyForDraw() as $program) {
                $this->executeDraw($program->requireId(), $draw);
            }
        });
    }

    private function executeDraw(LotteryProgramId $programId, ExecuteDrawAction $draw): void
    {
        try {
            $draw->execute($programId);
        } catch (DrawNotAllowedException) {
            // Program left locked state before draw ran (race or manual intervention).
        }
    }
}
