<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/** @phpstan-extends State<\App\Modules\Request\Infrastructure\Persistence\Models\RequestModel> */
abstract class RequestState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(DraftState::class)
            ->allowTransition(DraftState::class, SubmittedState::class)
            ->allowTransition(DraftState::class, CancelledState::class)
            ->allowTransition(SubmittedState::class, PendingDepartmentManagerState::class)
            ->allowTransition(SubmittedState::class, CancelledState::class)
            ->allowTransition(PendingDepartmentManagerState::class, PendingHRState::class)
            ->allowTransition(PendingDepartmentManagerState::class, RejectedState::class)
            ->allowTransition(PendingHRState::class, PendingDormitoryManagerState::class)
            ->allowTransition(PendingHRState::class, RejectedState::class)
            ->allowTransition(PendingDormitoryManagerState::class, PendingDormitoryUnitState::class)
            ->allowTransition(PendingDormitoryManagerState::class, RejectedState::class)
            ->allowTransition(PendingDormitoryUnitState::class, ApprovedState::class)
            ->allowTransition(PendingDormitoryUnitState::class, RejectedState::class)
            // OA-05-03 post-approval operational lifecycle (W3-B)
            ->allowTransition(ApprovedState::class, WaitingForAllocationState::class)
            ->allowTransition(WaitingForAllocationState::class, AllocatedState::class)
            ->allowTransition(WaitingForAllocationState::class, AllocationFailedState::class)
            ->allowTransition(AllocatedState::class, CheckedInState::class)
            ->allowTransition(AllocatedState::class, AllocationFailedState::class)
            ->allowTransition(CheckedInState::class, CheckedOutState::class);
    }

    public function isTerminal(): bool
    {
        return $this instanceof RejectedState
            || $this instanceof CancelledState
            || $this instanceof AllocationFailedState
            || $this instanceof CheckedOutState;
    }
}
