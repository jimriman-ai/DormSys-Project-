<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/** @phpstan-extends State<\App\Modules\Lottery\Infrastructure\Persistence\Models\LotteryProgramModel> */
abstract class LotteryProgramState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(DraftState::class)
            ->allowTransition(DraftState::class, WaitingApprovalState::class)
            ->allowTransition(DraftState::class, RegistrationOpenState::class)
            ->allowTransition(DraftState::class, CancelledState::class)
            ->allowTransition(WaitingApprovalState::class, ApprovedState::class)
            ->allowTransition(WaitingApprovalState::class, CancelledState::class)
            ->allowTransition(ApprovedState::class, RegistrationOpenState::class)
            ->allowTransition(ApprovedState::class, CancelledState::class)
            ->allowTransition(RegistrationOpenState::class, RegistrationClosedState::class)
            ->allowTransition(RegistrationOpenState::class, CancelledState::class)
            ->allowTransition(RegistrationClosedState::class, LockedState::class)
            ->allowTransition(RegistrationClosedState::class, CancelledState::class)
            ->allowTransition(LockedState::class, DrawnState::class)
            ->allowTransition(DrawnState::class, CompletedState::class);
    }

    public function isTerminal(): bool
    {
        return $this instanceof CompletedState || $this instanceof CancelledState;
    }
}
