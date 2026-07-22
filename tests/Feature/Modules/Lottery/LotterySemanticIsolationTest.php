<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Contracts\LotteryEligibleSnapshotRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultRepositoryContract;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Modules\Lottery\Domain\Models\EligibleSnapshot;
use App\Modules\Lottery\Domain\Services\LockedLotterySemanticContract;
use App\Modules\Lottery\Domain\Services\LotteryScoringEngine;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

it('materializes full frozen eligibility and scoring inputs at lock', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = createLotteryProgramForTest(
        title: 'Semantic Lock Program',
        dormitoryId: $dormitoryId,
        capacity: 1,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = openLotteryProgramForTest($draft->requireId());
    $registration = asRequestOwner($employee, fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    ));
    $closed = runLotteryMutation(fn () => app(CloseRegistrationAction::class)->execute($opened->requireId()));
    $locked = runLotteryMutation(fn () => app(LockLotteryProgramAction::class)->execute($closed->requireId()));

    $snapshot = app(LotteryEligibleSnapshotRepositoryContract::class)->findByProgramId($locked->requireId());
    expect($snapshot)->toBeInstanceOf(EligibleSnapshot::class);

    if (! $snapshot instanceof EligibleSnapshot) {
        throw new UnexpectedValueException('Expected eligible snapshot.');
    }

    expect($snapshot->payload['semantic_contract_version'])->toBe(LockedLotterySemanticContract::PAYLOAD_VERSION);
    expect($snapshot->payload['lock_boundary']['random_seed'])->toBe($locked->randomSeed);
    expect($snapshot->payload['lock_boundary']['scoring_config_version'])->toBe('1.0.0');

    $row = $snapshot->payload['eligible'][0];
    expect($row)->toHaveKeys([
        'registration_id',
        'request_id',
        'employee_id',
        'dormitory_id',
        'base_score',
        'department_priority',
        'weighted_score',
    ]);
    expect($row['request_id'])->toBe($requestId);
    expect($row['dormitory_id'])->toBe($dormitoryId);

    $config = new ScoringConfig(
        version: '1.0.0',
        baseScoreCoefficient: 1.0,
        departmentPriorityCoefficient: 0.05,
        normalizationDivisor: 100.0,
        prngScale: 1.0,
    );
    $expected = app(LotteryScoringEngine::class)->computeWeightedScore(
        config: $config,
        randomSeed: (string) $locked->randomSeed,
        registrationId: $registration->requireId()->value,
        baseScore: (float) $row['base_score'],
        departmentPriority: (int) $row['department_priority'],
    );

    expectPersistedWeightedScore((float) $row['weighted_score'], $expected);
});

it('does not reinterpret live request state after lock when drawing', function (): void {
    $employeeOne = createEmployeeForLotteryEnrollmentTest();
    $employeeTwo = LotteryTestFactory::createSecondEmployee();
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestOne = createApprovedLotteryRegistrationRequest($employeeOne, $dormitoryId);
    $requestTwo = createApprovedLotteryRegistrationRequest($employeeTwo, $dormitoryId);

    $draft = createLotteryProgramForTest(
        title: 'Post-Lock Drift Program',
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
    expect($locked->status)->toBe(LockedState::$name);

    $frozenSnapshot = app(LotteryEligibleSnapshotRepositoryContract::class)->findByProgramId($locked->requireId());
    $frozenEligibleIds = array_column($frozenSnapshot?->payload['eligible'] ?? [], 'registration_id');

    RequestModel::query()->whereIn('id', [$requestOne, $requestTwo])->update([
        'status' => RejectedState::$name,
        'rejection_reason' => 'Simulated post-lock request drift',
    ]);

    $completed = runLotteryMutation(fn () => app(ExecuteDrawAction::class)->execute($locked->requireId()));
    expect($completed->status)->toBe(CompletedState::$name);

    $results = app(LotteryResultReadContract::class)->resultsForProgram($locked->requireId());
    assertLotteryResultReadContractShape($results);
    expect($results['winners'])->toHaveCount(1);
    expect($results['reserves'])->toHaveCount(1);

    $resultRegistrationIds = array_merge(
        array_column($results['winners'], 'registration_id'),
        array_column($results['reserves'], 'registration_id'),
    );

    expect($resultRegistrationIds)->toEqualCanonicalizing($frozenEligibleIds);
    expect($resultRegistrationIds)->toContain($registrationOne->requireId()->value);
    expect($resultRegistrationIds)->toContain($registrationTwo->requireId()->value);

    $snapshotAfterDraw = app(LotteryEligibleSnapshotRepositoryContract::class)->findByProgramId($locked->requireId());
    expect($snapshotAfterDraw?->payload)->toBe($frozenSnapshot?->payload);
});

it('rejects eligible snapshot mutation after capture', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = createLotteryProgramForTest(
        title: 'Immutable Snapshot Program',
        dormitoryId: $dormitoryId,
        capacity: 1,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = openLotteryProgramForTest($draft->requireId());
    asRequestOwner($employee, fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestId),
    ));
    $closed = runLotteryMutation(fn () => app(CloseRegistrationAction::class)->execute($opened->requireId()));
    $locked = runLotteryMutation(fn () => app(LockLotteryProgramAction::class)->execute($closed->requireId()));

    $snapshot = app(LotteryEligibleSnapshotRepositoryContract::class)->findByProgramId($locked->requireId());
    expect($snapshot)->toBeInstanceOf(EligibleSnapshot::class);

    if (! $snapshot instanceof EligibleSnapshot) {
        throw new UnexpectedValueException('Expected eligible snapshot.');
    }

    expect(fn () => app(LotteryEligibleSnapshotRepositoryContract::class)->save($snapshot))
        ->toThrow(LotteryValidationException::class, 'immutable after capture');
});

it('produces identical draw rankings from the same frozen snapshot inputs', function (): void {
    Carbon::setTestNow('2026-06-30 12:00:00');

    $employeeOne = createEmployeeForLotteryEnrollmentTest();
    $employeeTwo = LotteryTestFactory::createSecondEmployee();
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestOne = createApprovedLotteryRegistrationRequest($employeeOne, $dormitoryId);
    $requestTwo = createApprovedLotteryRegistrationRequest($employeeTwo, $dormitoryId);

    $draft = createLotteryProgramForTest(
        title: 'Deterministic Draw Program',
        dormitoryId: $dormitoryId,
        capacity: 1,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $opened = openLotteryProgramForTest($draft->requireId());
    asRequestOwner($employeeOne, fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestOne),
    ));
    asRequestOwner($employeeTwo, fn () => app(EnrollRegistrationAction::class)->execute(
        $opened->requireId(),
        RequestReferenceId::fromString($requestTwo),
    ));

    $closed = runLotteryMutation(fn () => app(CloseRegistrationAction::class)->execute($opened->requireId()));
    $locked = runLotteryMutation(fn () => app(LockLotteryProgramAction::class)->execute($closed->requireId()));
    $snapshot = app(LotteryEligibleSnapshotRepositoryContract::class)->findByProgramId($locked->requireId());

    if (! $snapshot instanceof EligibleSnapshot) {
        throw new UnexpectedValueException('Expected eligible snapshot.');
    }

    $selectorRows = LockedLotterySemanticContract::drawEligibleRows($snapshot);
    $firstSelection = app(App\Modules\Lottery\Domain\Services\LotteryDrawSelector::class)->select(1, $selectorRows);
    $secondSelection = app(App\Modules\Lottery\Domain\Services\LotteryDrawSelector::class)->select(1, $selectorRows);

    expect($secondSelection)->toBe($firstSelection);

    runLotteryMutation(fn () => app(ExecuteDrawAction::class)->execute($locked->requireId()));
    $persisted = app(LotteryResultRepositoryContract::class)->findByProgramId($locked->requireId());

    expect(array_map(static fn ($result): int => $result->rank, $persisted))->toBe([1, 2]);
    expect($persisted[0]->registrationId->value)->toBe($firstSelection[0]['registration_id']);
});
