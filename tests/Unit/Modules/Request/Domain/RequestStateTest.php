<?php

declare(strict_types=1);

use App\Modules\Request\Domain\States\AllocatedState;
use App\Modules\Request\Domain\States\AllocationFailedState;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\CheckedInState;
use App\Modules\Request\Domain\States\CheckedOutState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryUnitState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\States\RequestState;
use App\Modules\Request\Domain\States\SubmittedState;
use App\Modules\Request\Domain\States\WaitingForAllocationState;

it('registers r-07 approval-phase and oa-05-03 post-approval states', function (): void {
    RequestState::config();

    expect(DraftState::$name)->toBe('draft');
    expect(SubmittedState::$name)->toBe('submitted');
    expect(PendingDepartmentManagerState::$name)->toBe('pending_department_manager');
    expect(PendingHRState::$name)->toBe('pending_hr');
    expect(PendingDormitoryManagerState::$name)->toBe('pending_dormitory_manager');
    expect(PendingDormitoryUnitState::$name)->toBe('pending_dormitory_unit');
    expect(ApprovedState::$name)->toBe('approved');
    expect(RejectedState::$name)->toBe('rejected');
    expect(CancelledState::$name)->toBe('cancelled');
    expect(WaitingForAllocationState::$name)->toBe('waiting_for_allocation');
    expect(AllocatedState::$name)->toBe('allocated');
    expect(AllocationFailedState::$name)->toBe('allocation_failed');
    expect(CheckedInState::$name)->toBe('checked_in');
    expect(CheckedOutState::$name)->toBe('checked_out');
});

it('marks terminal request states', function (string $stateClass, bool $terminal): void {
    $state = new $stateClass(new stdClass);
    if (! $state instanceof RequestState) {
        throw new UnexpectedValueException('Expected request state.');
    }

    expect($state->isTerminal())->toBe($terminal);
})->with([
    'draft' => [DraftState::class, false],
    'submitted' => [SubmittedState::class, false],
    'pending department manager' => [PendingDepartmentManagerState::class, false],
    'approved' => [ApprovedState::class, false],
    'waiting_for_allocation' => [WaitingForAllocationState::class, false],
    'allocated' => [AllocatedState::class, false],
    'checked_in' => [CheckedInState::class, false],
    'allocation_failed' => [AllocationFailedState::class, true],
    'checked_out' => [CheckedOutState::class, true],
    'rejected' => [RejectedState::class, true],
    'cancelled' => [CancelledState::class, true],
]);
