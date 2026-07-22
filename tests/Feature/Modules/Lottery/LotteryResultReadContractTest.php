<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Lottery\LotteryTestFactory;

uses(RefreshDatabase::class);

require_once __DIR__.'/support/enrollment.php';
require_once __DIR__.'/LotteryFeatureSupport.php';

beforeEach(function (): void {
    bootstrapLotteryFeatureTests();
});

afterEach(function (): void {
    teardownLotteryFeatureTests();
});

it('returns the public contract output shape for a completed draw', function (): void {
    $employeeOne = createEmployeeForLotteryEnrollmentTest();
    $employeeTwo = LotteryTestFactory::createSecondEmployee();
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestOne = createApprovedLotteryRegistrationRequest($employeeOne, $dormitoryId);
    $requestTwo = createApprovedLotteryRegistrationRequest($employeeTwo, $dormitoryId);

    $draft = createLotteryProgramForTest(
        title: 'Contract Read Program',
        dormitoryId: $dormitoryId,
        capacity: 1,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = openLotteryProgramForTest($draft->requireId());
    $registrationOne = asRequestOwner($employeeOne, fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestOne),
    ));
    $registrationTwo = asRequestOwner($employeeTwo, fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestTwo),
    ));
    $closed = runLotteryMutation(fn () => app(CloseRegistrationAction::class)->execute($opened->requireId()));
    $locked = runLotteryMutation(fn () => app(LockLotteryProgramAction::class)->execute($closed->requireId()));
    $completed = runLotteryMutation(fn () => app(ExecuteDrawAction::class)->execute($locked->requireId()));

    $payload = app(LotteryResultReadContract::class)->resultsForProgram($completed->requireId());

    assertLotteryResultReadContractShape($payload);
    expect($payload['program_id'])->toBe($completed->requireId()->value);
    expect($payload['winners'])->toHaveCount(1);
    expect($payload['reserves'])->toHaveCount(1);
    expect($payload['ranks'])->toHaveCount(2);

    $rankValues = array_column($payload['ranks'], 'rank');
    expect($rankValues)->toBe([1, 2]);

    $winnerRegistrationIds = array_column($payload['winners'], 'registration_id');
    $reserveRegistrationIds = array_column($payload['reserves'], 'registration_id');
    $allRegistrationIds = array_column($payload['ranks'], 'registration_id');

    expect($winnerRegistrationIds)->toHaveCount(1);
    expect($reserveRegistrationIds)->toHaveCount(1);
    expect($allRegistrationIds)->toContain($registrationOne->requireId()->value);
    expect($allRegistrationIds)->toContain($registrationTwo->requireId()->value);
    expect($winnerRegistrationIds[0])->not->toBe($reserveRegistrationIds[0]);
});

it('returns empty winners and reserves with stable shape when no draw results exist', function (): void {
    $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());

    $payload = app(LotteryResultReadContract::class)->resultsForProgram($programId);

    assertLotteryResultReadContractShape($payload);
    expect($payload['program_id'])->toBe($programId->value);
    expect($payload['winners'])->toBe([]);
    expect($payload['reserves'])->toBe([]);
    expect($payload['ranks'])->toBe([]);
});
