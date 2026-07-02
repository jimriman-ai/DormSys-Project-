<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\Lottery\LotteryTestFactory;

uses(RefreshDatabase::class);

require_once __DIR__.'/LotteryRegistrationEnrollmentTest.php';
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
    $dormitoryId = UuidGenerator::uuid7();
    $requestOne = createApprovedLotteryRegistrationRequest($employeeOne, $dormitoryId);
    $requestTwo = createApprovedLotteryRegistrationRequest($employeeTwo, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Contract Read Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 1,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = app(OpenRegistrationAction::class)->execute($draft->requireId());
    $registrationOne = app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestOne),
    );
    $registrationTwo = app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestTwo),
    );
    $closed = app(CloseRegistrationAction::class)->execute($opened->requireId());
    $locked = app(LockLotteryProgramAction::class)->execute($closed->requireId());
    $completed = app(ExecuteDrawAction::class)->execute($locked->requireId());

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
    $programId = \App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId::fromString(UuidGenerator::uuid7());

    $payload = app(LotteryResultReadContract::class)->resultsForProgram($programId);

    assertLotteryResultReadContractShape($payload);
    expect($payload['program_id'])->toBe($programId->value);
    expect($payload['winners'])->toBe([]);
    expect($payload['reserves'])->toBe([]);
    expect($payload['ranks'])->toBe([]);
});
