<?php

declare(strict_types=1);

use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryUnitState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\States\RequestState;
use App\Modules\Request\Domain\States\SubmittedState;
use Spatie\ModelStates\StateConfig;

it('registers r-07 approval-phase transitions on request state', function (): void {
    $config = RequestState::config();

    expect($config)->toBeInstanceOf(StateConfig::class);
    expect(DraftState::$name)->toBe('draft');
    expect(SubmittedState::$name)->toBe('submitted');
    expect(PendingDepartmentManagerState::$name)->toBe('pending_department_manager');
    expect(PendingHRState::$name)->toBe('pending_hr');
    expect(PendingDormitoryManagerState::$name)->toBe('pending_dormitory_manager');
    expect(PendingDormitoryUnitState::$name)->toBe('pending_dormitory_unit');
    expect(ApprovedState::$name)->toBe('approved');
    expect(RejectedState::$name)->toBe('rejected');
    expect(CancelledState::$name)->toBe('cancelled');
});

it('marks terminal request states', function (string $stateClass, bool $terminal): void {
    $state = new $stateClass(new stdClass);

    expect($state->isTerminal())->toBe($terminal);
})->with([
    'draft' => [DraftState::class, false],
    'submitted' => [SubmittedState::class, false],
    'pending department manager' => [PendingDepartmentManagerState::class, false],
    'approved' => [ApprovedState::class, true],
    'rejected' => [RejectedState::class, true],
    'cancelled' => [CancelledState::class, true],
]);
