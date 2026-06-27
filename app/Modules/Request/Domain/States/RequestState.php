<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\States;

use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * @extends State<RequestModel>
 */
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
            ->allowTransition(PendingDormitoryUnitState::class, RejectedState::class);
    }

    public function isTerminal(): bool
    {
        return $this instanceof ApprovedState
            || $this instanceof RejectedState
            || $this instanceof CancelledState;
    }
}
