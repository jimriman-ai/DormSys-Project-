<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Application;

use App\Modules\Lottery\Application\Contracts\LotteryEligibleSnapshotRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultRepositoryContract;
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Domain\Events\LotteryProgramStateChanged;
use App\Modules\Lottery\Domain\Exceptions\DrawNotAllowedException;
use App\Modules\Lottery\Domain\Models\EligibleSnapshot;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\Models\LotteryResult;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryResultId;
use App\Modules\Lottery\Domain\ValueObjects\LotterySnapshotId;
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

class ExecuteDrawActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::shouldReceive('transaction')
            ->andReturnUsing(static fn (callable $callback) => $callback());
    }

    #[Test]
    public function it_rejects_draw_when_program_is_not_locked(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $program = $this->lockedProgram($programId, RegistrationOpenState::$name);

        $programs = MockeryTest::mock(LotteryProgramRepositoryContract::class);
        MockeryTest::expectOnce($programs, 'findById')->with($programId)->andReturn($program);
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);

        $results = MockeryTest::mock(LotteryResultRepositoryContract::class);
        MockeryTest::expectOnce($results, 'existsForProgram')->with($programId)->andReturn(false);
        $this->app->instance(LotteryResultRepositoryContract::class, $results);

        $this->expectException(DrawNotAllowedException::class);

        app(ExecuteDrawAction::class)->execute($programId);
    }

    #[Test]
    public function it_is_idempotent_when_results_already_exist(): void
    {
        Event::fake();

        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $completed = $this->lockedProgram($programId, CompletedState::$name);

        $programs = MockeryTest::mock(LotteryProgramRepositoryContract::class);
        MockeryTest::expect($programs, 'findById')->twice()->with($programId)->andReturn($completed);
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);

        $results = MockeryTest::mock(LotteryResultRepositoryContract::class);
        MockeryTest::expectOnce($results, 'existsForProgram')->with($programId)->andReturn(true);
        $results->shouldNotReceive('save');
        $this->app->instance(LotteryResultRepositoryContract::class, $results);

        $snapshots = MockeryTest::mock(LotteryEligibleSnapshotRepositoryContract::class);
        $snapshots->shouldNotReceive('findByProgramId');
        $this->app->instance(LotteryEligibleSnapshotRepositoryContract::class, $snapshots);

        $allocations = MockeryTest::mock(ProposedAllocationPort::class);
        $allocations->shouldNotReceive('emitProposedAllocations');
        $this->app->instance(ProposedAllocationPort::class, $allocations);

        $returned = app(ExecuteDrawAction::class)->execute($programId);

        self::assertSame(CompletedState::$name, $returned->status);
        Event::assertNothingDispatched();
    }

    #[Test]
    public function it_persists_results_and_completes_program_on_first_draw(): void
    {
        Event::fake([LotteryProgramStateChanged::class]);

        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $registrationId = UuidGenerator::uuid7();
        $program = $this->lockedProgram($programId, LockedState::$name, capacity: 1);

        $snapshot = new EligibleSnapshot(
            id: LotterySnapshotId::fromString(UuidGenerator::uuid7()),
            programId: $programId,
            payload: [
                'eligible' => [
                    [
                        'registration_id' => $registrationId,
                        'request_id' => UuidGenerator::uuid7(),
                        'employee_id' => UuidGenerator::uuid7(),
                        'weighted_score' => 3.5,
                    ],
                    [
                        'registration_id' => UuidGenerator::uuid7(),
                        'request_id' => UuidGenerator::uuid7(),
                        'employee_id' => UuidGenerator::uuid7(),
                        'weighted_score' => 1.0,
                    ],
                ],
            ],
            randomSeed: UuidGenerator::uuid7(),
            scoringConfig: new ScoringConfig('1.0.0', 1.0, 0.05, 100.0, 1.0),
        );

        $programs = MockeryTest::mock(LotteryProgramRepositoryContract::class);
        MockeryTest::expectOnce($programs, 'findById')->with($programId)->andReturn($program);
        MockeryTest::expect($programs, 'save')->twice()->andReturnUsing(
            static fn (LotteryProgram $saved): LotteryProgram => $saved,
        );
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);

        $results = MockeryTest::mock(LotteryResultRepositoryContract::class);
        MockeryTest::expect($results, 'existsForProgram')->twice()->with($programId)->andReturn(false);
        MockeryTest::expect($results, 'save')->twice()->andReturnUsing(
            static fn (LotteryResult $result): LotteryResult => new LotteryResult(
                id: LotteryResultId::fromString(UuidGenerator::uuid7()),
                programId: $result->programId,
                registrationId: $result->registrationId,
                rank: $result->rank,
                outcome: $result->outcome,
            ),
        );
        $this->app->instance(LotteryResultRepositoryContract::class, $results);

        $snapshots = MockeryTest::mock(LotteryEligibleSnapshotRepositoryContract::class);
        MockeryTest::expectOnce($snapshots, 'findByProgramId')->with($programId)->andReturn($snapshot);
        $this->app->instance(LotteryEligibleSnapshotRepositoryContract::class, $snapshots);

        $allocations = MockeryTest::mock(ProposedAllocationPort::class);
        MockeryTest::expectOnce($allocations, 'emitProposedAllocations')->with(Mockery::on(
            static fn (array $payload): bool => count($payload) === 1
                && $payload[0]['registration_id'] === $registrationId
                && $payload[0]['rank'] === 1,
        ));
        $this->app->instance(ProposedAllocationPort::class, $allocations);

        $returned = app(ExecuteDrawAction::class)->execute($programId);

        self::assertSame(CompletedState::$name, $returned->status);
        self::assertNotNull($returned->drawnAt);
        Event::assertDispatchedTimes(LotteryProgramStateChanged::class, 2);
    }

    #[Test]
    public function it_does_not_duplicate_results_on_retry_after_success(): void
    {
        Event::fake();

        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $program = $this->lockedProgram($programId, CompletedState::$name);

        $programs = MockeryTest::mock(LotteryProgramRepositoryContract::class);
        MockeryTest::expect($programs, 'findById')->times(4)->with($programId)->andReturn($program);
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);

        $results = MockeryTest::mock(LotteryResultRepositoryContract::class);
        MockeryTest::expect($results, 'existsForProgram')->twice()->with($programId)->andReturn(true);
        $results->shouldNotReceive('save');
        $this->app->instance(LotteryResultRepositoryContract::class, $results);

        $snapshots = MockeryTest::mock(LotteryEligibleSnapshotRepositoryContract::class);
        $snapshots->shouldNotReceive('findByProgramId');
        $this->app->instance(LotteryEligibleSnapshotRepositoryContract::class, $snapshots);

        $allocations = MockeryTest::mock(ProposedAllocationPort::class);
        $allocations->shouldNotReceive('emitProposedAllocations');
        $this->app->instance(ProposedAllocationPort::class, $allocations);

        $action = app(ExecuteDrawAction::class);
        $first = $action->execute($programId);
        $second = $action->execute($programId);

        self::assertSame(CompletedState::$name, $first->status);
        self::assertSame(CompletedState::$name, $second->status);
        Event::assertNothingDispatched();
    }

    private function lockedProgram(
        LotteryProgramId $programId,
        string $status,
        int $capacity = 20,
    ): LotteryProgram {
        return new LotteryProgram(
            id: $programId,
            title: 'Draw Program',
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            capacity: $capacity,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-31', new DateTimeZone('UTC')),
            status: $status,
            randomSeed: UuidGenerator::uuid7(),
            scoringConfigVersion: '1.0.0',
            lockedAt: new DateTimeImmutable('2026-06-30', new DateTimeZone('UTC')),
            drawnAt: $status === CompletedState::$name
                ? new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC'))
                : null,
        );
    }
}
