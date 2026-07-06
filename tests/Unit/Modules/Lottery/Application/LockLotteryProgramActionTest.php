<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Application;

use App\Modules\Lottery\Application\Contracts\LotteryEligibleSnapshotRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRequestReadPort;
use App\Modules\Lottery\Application\DTOs\ApprovedLotteryRequestDTO;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\LotteryScoringConfigReader;
use App\Modules\Lottery\Domain\Events\LotteryProgramStateChanged;
use App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException;
use App\Modules\Lottery\Domain\Exceptions\ScoringConfigNotFoundException;
use App\Modules\Lottery\Domain\Models\EligibleSnapshot;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\Models\LotteryRegistration;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\States\RegistrationClosedState;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryRegistrationId;
use App\Modules\Lottery\Domain\ValueObjects\LotterySnapshotId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Lottery\Domain\ValueObjects\ScoringConfig;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\MockeryTest;
use Tests\TestCase;

class LockLotteryProgramActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        bypassLotteryMutationAuthorization();

        DB::shouldReceive('transaction')
            ->andReturnUsing(static fn (callable $callback) => $callback());
    }

    #[Test]
    public function it_rejects_lock_when_program_is_not_registration_closed(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $program = $this->programInState($programId, RegistrationOpenState::$name);

        $programs = MockeryTest::mock(LotteryProgramRepositoryContract::class);
        MockeryTest::expectOnce($programs, 'findById')->with($programId)->andReturn($program);
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);

        $this->expectException(InvalidLotteryTransitionException::class);

        app(LockLotteryProgramAction::class)->execute($programId);
    }

    #[Test]
    public function it_fails_when_scoring_config_is_missing(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $program = $this->programInState($programId, RegistrationClosedState::$name);

        $programs = MockeryTest::mock(LotteryProgramRepositoryContract::class);
        MockeryTest::expectOnce($programs, 'findById')->with($programId)->andReturn($program);
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);

        $reader = MockeryTest::mock(LotteryScoringConfigReader::class);
        MockeryTest::expectOnce($reader, 'load')->andThrow(new ScoringConfigNotFoundException('missing'));
        $this->app->instance(LotteryScoringConfigReader::class, $reader);

        $this->expectException(ScoringConfigNotFoundException::class);

        app(LockLotteryProgramAction::class)->execute($programId);
    }

    #[Test]
    public function it_excludes_registrations_whose_requests_are_no_longer_approved(): void
    {
        Event::fake([LotteryProgramStateChanged::class]);

        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());
        $program = $this->programInState($programId, RegistrationClosedState::$name, $dormitoryId);
        $config = $this->sampleConfig();

        $validRequestId = RequestReferenceId::fromString(UuidGenerator::uuid7());
        $invalidRequestId = RequestReferenceId::fromString(UuidGenerator::uuid7());
        $employeeId = EmployeeReferenceId::fromString(UuidGenerator::uuid7());

        $validRegistration = new LotteryRegistration(
            id: LotteryRegistrationId::fromString(UuidGenerator::uuid7()),
            programId: $programId,
            requestId: $validRequestId,
            employeeId: $employeeId,
            enrolledAt: new DateTimeImmutable('2026-06-30', new DateTimeZone('UTC')),
        );
        $invalidRegistration = new LotteryRegistration(
            id: LotteryRegistrationId::fromString(UuidGenerator::uuid7()),
            programId: $programId,
            requestId: $invalidRequestId,
            employeeId: EmployeeReferenceId::fromString(UuidGenerator::uuid7()),
            enrolledAt: new DateTimeImmutable('2026-06-30', new DateTimeZone('UTC')),
        );

        $this->mockConfigReader($config);

        $programs = MockeryTest::mock(LotteryProgramRepositoryContract::class);
        MockeryTest::expectOnce($programs, 'findById')->with($programId)->andReturn($program);
        MockeryTest::expectOnce($programs, 'save')->with(Mockery::on(
            fn (LotteryProgram $locked): bool => $locked->status === LockedState::$name
                && $locked->randomSeed !== null
                && $locked->scoringConfigVersion === $config->version,
        ))->andReturnUsing(fn (LotteryProgram $locked): LotteryProgram => $locked);
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);

        $registrations = MockeryTest::mock(LotteryRegistrationRepositoryContract::class);
        MockeryTest::expectOnce($registrations, 'findByProgramId')->with($programId)->andReturn([
            $validRegistration,
            $invalidRegistration,
        ]);
        MockeryTest::expectOnce($registrations, 'save')->andReturnUsing(
            fn (LotteryRegistration $registration): LotteryRegistration => $registration,
        );
        $this->app->instance(LotteryRegistrationRepositoryContract::class, $registrations);

        $requests = MockeryTest::mock(LotteryRequestReadPort::class);
        MockeryTest::expect($requests, 'findApprovedLotteryRegistration')
            ->with($validRequestId)
            ->andReturn(new ApprovedLotteryRequestDTO(
                requestId: $validRequestId->value,
                employeeId: $employeeId->value,
                dormitoryId: $dormitoryId->value,
            ));
        MockeryTest::expect($requests, 'findApprovedLotteryRegistration')
            ->with($invalidRequestId)
            ->andReturn(null);
        $this->app->instance(LotteryRequestReadPort::class, $requests);

        $snapshots = MockeryTest::mock(LotteryEligibleSnapshotRepositoryContract::class);
        MockeryTest::expectOnce($snapshots, 'save')->with(Mockery::on(
            function (EligibleSnapshot $snapshot) use ($invalidRegistration): bool {
                $excluded = $snapshot->payload['excluded'] ?? [];

                return count($snapshot->payload['eligible'] ?? []) === 1
                    && count($excluded) === 1
                    && ($excluded[0]['registration_id'] ?? null) === $invalidRegistration->requireId()->value;
            },
        ))->andReturnUsing(fn (EligibleSnapshot $snapshot): EligibleSnapshot => new EligibleSnapshot(
            id: LotterySnapshotId::fromString(UuidGenerator::uuid7()),
            programId: $snapshot->programId,
            payload: $snapshot->payload,
            randomSeed: $snapshot->randomSeed,
            scoringConfig: $snapshot->scoringConfig,
        ));
        $this->app->instance(LotteryEligibleSnapshotRepositoryContract::class, $snapshots);

        $result = app(LockLotteryProgramAction::class)->execute($programId);

        expect($result->isLocked())->toBeTrue();
        Event::assertDispatched(LotteryProgramStateChanged::class);
    }

    private function programInState(
        LotteryProgramId $programId,
        string $status,
        ?DormitorySiteId $dormitoryId = null,
    ): LotteryProgram {
        return new LotteryProgram(
            id: $programId,
            title: 'Lock Test Program',
            dormitoryId: $dormitoryId ?? DormitorySiteId::fromString(UuidGenerator::uuid7()),
            capacity: 10,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
            status: $status,
        );
    }

    private function sampleConfig(): ScoringConfig
    {
        return new ScoringConfig(
            version: '1.0.0',
            baseScoreCoefficient: 1.0,
            departmentPriorityCoefficient: 0.01,
            normalizationDivisor: 1000.0,
            prngScale: 1.0,
        );
    }

    private function mockConfigReader(ScoringConfig $config): void
    {
        $reader = MockeryTest::mock(LotteryScoringConfigReader::class);
        MockeryTest::expectOnce($reader, 'load')->andReturn($config);
        $this->app->instance(LotteryScoringConfigReader::class, $reader);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
