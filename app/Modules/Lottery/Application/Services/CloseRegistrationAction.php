<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Domain\Events\LotteryProgramStateChanged;
use App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CloseRegistrationAction
{
    public function __construct(
        private readonly LotteryProgramRepositoryContract $programs,
    ) {}

    public function execute(LotteryProgramId $programId): LotteryProgram
    {
        $program = $this->programs->findById($programId);

        if ($program === null) {
            throw new LotteryProgramNotFoundException('Lottery program not found.');
        }

        if (! $program->canCloseRegistration()) {
            throw new InvalidLotteryTransitionException(
                'Only programs with open registration can be closed.',
            );
        }

        $previousStatus = $program->status;
        $closed = $program->markRegistrationClosed();

        return DB::transaction(function () use ($closed, $previousStatus): LotteryProgram {
            $persisted = $this->programs->save($closed);

            Event::dispatch(LotteryProgramStateChanged::forProgram(
                programId: $persisted->requireId()->value,
                previousStatus: $previousStatus,
                newStatus: $persisted->status,
            ));

            return $persisted;
        });
    }
}
