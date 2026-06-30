<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Application;

use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Services\CancelLotteryProgramAction;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\Events\LotteryProgramCreated;
use App\Modules\Lottery\Domain\Events\LotteryProgramStateChanged;
use App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException;
use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\States\DraftState;
use App\Modules\Lottery\Domain\States\RegistrationClosedState;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryProgramActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::shouldReceive('transaction')
            ->andReturnUsing(static fn (callable $callback) => $callback());
    }

    #[Test]
    public function it_rejects_zero_capacity_on_create(): void
    {
        $this->expectException(LotteryValidationException::class);

        app(CreateLotteryProgramAction::class)->execute(
            title: 'Invalid Program',
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            capacity: 0,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
        );
    }

    #[Test]
    public function it_rejects_open_registration_from_locked_program(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $locked = new LotteryProgram(
            id: $programId,
            title: 'Locked',
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            capacity: 10,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
            status: 'locked',
        );

        $repository = Mockery::mock(LotteryProgramRepositoryContract::class);
        $repository->shouldReceive('findById')->once()->with($programId)->andReturn($locked);
        $this->app->instance(LotteryProgramRepositoryContract::class, $repository);

        $this->expectException(InvalidLotteryTransitionException::class);

        app(OpenRegistrationAction::class)->execute($programId);
    }

    #[Test]
    public function it_dispatches_state_changed_when_closing_registration(): void
    {
        Event::fake([LotteryProgramStateChanged::class]);

        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $open = new LotteryProgram(
            id: $programId,
            title: 'Open Program',
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            capacity: 10,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
            status: RegistrationOpenState::$name,
        );
        $closed = $open->markRegistrationClosed();

        $repository = Mockery::mock(LotteryProgramRepositoryContract::class);
        $repository->shouldReceive('findById')->once()->with($programId)->andReturn($open);
        $repository->shouldReceive('save')->once()->with(Mockery::on(
            fn (LotteryProgram $program): bool => $program->status === RegistrationClosedState::$name,
        ))->andReturn($closed);
        $this->app->instance(LotteryProgramRepositoryContract::class, $repository);

        $result = app(CloseRegistrationAction::class)->execute($programId);

        expect($result->status)->toBe(RegistrationClosedState::$name);

        Event::assertDispatched(LotteryProgramStateChanged::class, function (LotteryProgramStateChanged $event) use ($programId): bool {
            return $event->aggregateId === $programId->value
                && ($event->payload['previous_status'] ?? null) === RegistrationOpenState::$name
                && ($event->payload['new_status'] ?? null) === RegistrationClosedState::$name;
        });
    }

    #[Test]
    public function it_dispatches_created_event_on_create(): void
    {
        Event::fake([LotteryProgramCreated::class]);

        $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());
        $draft = LotteryProgram::createDraft(
            title: 'New Draw',
            dormitoryId: $dormitoryId,
            capacity: 20,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
        );
        $persisted = new LotteryProgram(
            id: LotteryProgramId::fromString(UuidGenerator::uuid7()),
            title: $draft->title,
            dormitoryId: $draft->dormitoryId,
            capacity: $draft->capacity,
            registrationStartsAt: $draft->registrationStartsAt,
            registrationEndsAt: $draft->registrationEndsAt,
            status: DraftState::$name,
        );

        $repository = Mockery::mock(LotteryProgramRepositoryContract::class);
        $repository->shouldReceive('save')->once()->andReturn($persisted);
        $this->app->instance(LotteryProgramRepositoryContract::class, $repository);

        app(CreateLotteryProgramAction::class)->execute(
            title: 'New Draw',
            dormitoryId: $dormitoryId,
            capacity: 20,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
        );

        Event::assertDispatched(LotteryProgramCreated::class, function (LotteryProgramCreated $event) use ($persisted): bool {
            return $event->aggregateId === $persisted->requireId()->value
                && ($event->payload['capacity'] ?? null) === 20;
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
