<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Application\Services;

use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Domain\Events\LotteryProgramStateChanged;
use App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CancelLotteryProgramAction
{
    public function __construct(
        private readonly LotteryProgramRepositoryContract $programs,
    ) {}

    public function execute(LotteryProgramId $programId, string $reason): LotteryProgram
    {
        if (trim($reason) === '') {
            throw new LotteryValidationException('Cancellation reason is required.');
        }

        $program = $this->programs->findById($programId);

        if ($program === null) {
            throw new LotteryProgramNotFoundException('Lottery program not found.');
        }

        if (! $program->isCancellable()) {
            throw new InvalidLotteryTransitionException(
                'Program cannot be cancelled in its current state.',
            );
        }

        $previousStatus = $program->status;
        $cancelled = $program->markCancelled($reason);

        return DB::transaction(function () use ($cancelled, $previousStatus): LotteryProgram {
            $persisted = $this->programs->save($cancelled);

            Event::dispatch(LotteryProgramStateChanged::forProgram(
                programId: $persisted->requireId()->value,
                previousStatus: $previousStatus,
                newStatus: $persisted->status,
            ));

            return $persisted;
        });
    }
}
