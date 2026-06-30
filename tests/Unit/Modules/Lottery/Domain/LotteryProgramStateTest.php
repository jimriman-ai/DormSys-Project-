<?php

declare(strict_types=1);

use App\Modules\Lottery\Domain\States\ApprovedState;
use App\Modules\Lottery\Domain\States\CancelledState;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Modules\Lottery\Domain\States\DraftState;
use App\Modules\Lottery\Domain\States\DrawnState;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\States\LotteryProgramState;
use App\Modules\Lottery\Domain\States\RegistrationClosedState;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\States\WaitingApprovalState;
use Spatie\ModelStates\StateConfig;

it('registers lottery program lifecycle transitions on state config', function (): void {
    $config = LotteryProgramState::config();

    expect($config)->toBeInstanceOf(StateConfig::class);
    expect(DraftState::$name)->toBe('draft');
    expect(WaitingApprovalState::$name)->toBe('waiting_approval');
    expect(ApprovedState::$name)->toBe('approved');
    expect(RegistrationOpenState::$name)->toBe('registration_open');
    expect(RegistrationClosedState::$name)->toBe('registration_closed');
    expect(LockedState::$name)->toBe('locked');
    expect(DrawnState::$name)->toBe('drawn');
    expect(CompletedState::$name)->toBe('completed');
    expect(CancelledState::$name)->toBe('cancelled');
});

it('marks terminal lottery program states', function (string $stateClass, bool $terminal): void {
    $state = new $stateClass(new stdClass);

    expect($state->isTerminal())->toBe($terminal);
})->with([
    'draft' => [DraftState::class, false],
    'waiting approval' => [WaitingApprovalState::class, false],
    'approved' => [ApprovedState::class, false],
    'registration open' => [RegistrationOpenState::class, false],
    'locked' => [LockedState::class, false],
    'drawn' => [DrawnState::class, false],
    'completed' => [CompletedState::class, true],
    'cancelled' => [CancelledState::class, true],
]);
