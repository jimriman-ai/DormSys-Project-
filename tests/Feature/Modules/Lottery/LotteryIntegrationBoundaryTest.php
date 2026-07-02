<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Contracts\LotteryEligibleSnapshotRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultRepositoryContract;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\Services\LotteryScoringEngine;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;
use App\Modules\Lottery\Infrastructure\Jobs\AutoLockLotteryJob;
use App\Modules\Lottery\Infrastructure\Jobs\ExecuteLotteryDrawJob;
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

it('validates the full request to read contract integration boundary', function (): void {
    $employeeOne = createEmployeeForLotteryEnrollmentTest();
    $employeeTwo = LotteryTestFactory::createSecondEmployee();
    $dormitoryId = UuidGenerator::uuid7();
    $requestOne = createApprovedLotteryRegistrationRequest($employeeOne, $dormitoryId);
    $requestTwo = createApprovedLotteryRegistrationRequest($employeeTwo, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Integration Boundary Program',
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

    expect($locked->status)->toBe(LockedState::$name);

    $snapshotAfterLock = app(LotteryEligibleSnapshotRepositoryContract::class)->findByProgramId($locked->requireId());
    expect($snapshotAfterLock)->not->toBeNull();
    expect($snapshotAfterLock?->payload['eligible'] ?? [])->toHaveCount(2);
    $frozenPayload = $snapshotAfterLock?->payload;

    $completed = app(ExecuteDrawAction::class)->execute($locked->requireId());
    $drawRetry = app(ExecuteDrawAction::class)->execute($locked->requireId());

    expect($completed->status)->toBe(CompletedState::$name);
    expect($drawRetry->status)->toBe(CompletedState::$name);

    $snapshotAfterDraw = app(LotteryEligibleSnapshotRepositoryContract::class)->findByProgramId($locked->requireId());
    expect($snapshotAfterDraw?->payload)->toBe($frozenPayload);

    $persistedResults = app(LotteryResultRepositoryContract::class)->findByProgramId($locked->requireId());
    expect($persistedResults)->toHaveCount(2);
    expect(array_map(static fn ($result): int => $result->rank, $persistedResults))->toBe([1, 2]);

    $firstRead = app(LotteryResultReadContract::class)->resultsForProgram($locked->requireId());
    $secondRead = app(LotteryResultReadContract::class)->resultsForProgram($locked->requireId());

    assertLotteryResultReadContractShape($firstRead);
    assertLotteryResultReadContractShape($secondRead);
    expect($secondRead)->toBe($firstRead);
    expect($firstRead['program_id'])->toBe($locked->requireId()->value);
    expect($firstRead['winners'])->toHaveCount(1);
    expect($firstRead['reserves'])->toHaveCount(1);
    expect($firstRead['ranks'])->toHaveCount(2);
    expect(array_column($firstRead['ranks'], 'rank'))->toBe([1, 2]);

    $winnerRegistrationId = $firstRead['winners'][0]['registration_id'];
    $reserveRegistrationId = $firstRead['reserves'][0]['registration_id'];
    expect([$registrationOne->requireId()->value, $registrationTwo->requireId()->value])
        ->toContain($winnerRegistrationId)
        ->toContain($reserveRegistrationId);

    $config = new ScoringConfig(
        version: '1.0.0',
        baseScoreCoefficient: 1.0,
        departmentPriorityCoefficient: 0.05,
        normalizationDivisor: 100.0,
        prngScale: 1.0,
    );
    $engine = app(LotteryScoringEngine::class);

    foreach ($persistedResults as $result) {
        $recomputed = $engine->computeWeightedScore(
            config: $config,
            randomSeed: (string) $locked->randomSeed,
            registrationId: $result->registrationId->value,
            baseScore: 0.0,
            departmentPriority: 0,
        );

        $registration = app(LotteryRegistrationRepositoryContract::class)->findById($result->registrationId);

        expect($registration?->weightedScore)->toBe($recomputed);
    }

    $drawJob = new ExecuteLotteryDrawJob($locked->requireId()->value);
    app()->call([$drawJob, 'handle']);
    expect(app(LotteryResultRepositoryContract::class)->findByProgramId($locked->requireId()))->toHaveCount(2);
});

it('preserves idempotency when auto lock and draw jobs retry after manual completion', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Job Idempotency Integration Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 1,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-10 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = app(OpenRegistrationAction::class)->execute($draft->requireId());
    app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    );

    \Illuminate\Support\Carbon::setTestNow('2026-07-15 12:00:00');

    $autoLockJob = app(AutoLockLotteryJob::class);
    app()->call([$autoLockJob, 'handle']);
    app()->call([$autoLockJob, 'handle']);

    $locked = app(\App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract::class)
        ->findById($opened->requireId());

    expect($locked?->status)->toBe(LockedState::$name);

    $drawJob = new ExecuteLotteryDrawJob($opened->requireId()->value);
    app()->call([$drawJob, 'handle']);
    app()->call([$drawJob, 'handle']);

    $payload = app(LotteryResultReadContract::class)->resultsForProgram($opened->requireId());
    assertLotteryResultReadContractShape($payload);
    expect($payload['winners'])->toHaveCount(1);
    expect(app(LotteryResultRepositoryContract::class)->findByProgramId($opened->requireId()))->toHaveCount(1);
});
