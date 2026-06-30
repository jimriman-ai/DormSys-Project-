<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Application;

use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRequestReadPort;
use App\Modules\Lottery\Application\DTOs\ApprovedLotteryRequestDTO;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Domain\Events\LotteryRegistrationCreated;
use App\Modules\Lottery\Domain\Exceptions\DuplicateEnrollmentException;
use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Modules\Lottery\Domain\Exceptions\RegistrationClosedException;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\Models\LotteryRegistration;
use App\Modules\Lottery\Domain\States\DraftState;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryRegistrationId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnrollRegistrationActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::shouldReceive('transaction')
            ->andReturnUsing(static fn (callable $callback) => $callback());
    }

    #[Test]
    public function it_rejects_enrollment_when_program_is_not_open(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $requestId = RequestReferenceId::fromString(UuidGenerator::uuid7());
        $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

        $program = new LotteryProgram(
            id: $programId,
            title: 'Draft Program',
            dormitoryId: $dormitoryId,
            capacity: 10,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
            status: DraftState::$name,
        );

        $this->mockProgramRepository($program);

        $this->expectException(RegistrationClosedException::class);

        app(EnrollRegistrationAction::class)->execute($programId, $requestId);
    }

    #[Test]
    public function it_rejects_duplicate_enrollment_for_same_request(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $requestId = RequestReferenceId::fromString(UuidGenerator::uuid7());
        $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

        $program = $this->openProgram($programId, $dormitoryId);
        $existing = LotteryRegistration::enroll(
            programId: $programId,
            requestId: $requestId,
            employeeId: EmployeeReferenceId::fromString(UuidGenerator::uuid7()),
            enrolledAt: new DateTimeImmutable('2026-06-30', new DateTimeZone('UTC')),
        );

        $programs = Mockery::mock(LotteryProgramRepositoryContract::class);
        $programs->shouldReceive('findById')->once()->with($programId)->andReturn($program);
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);

        $registrations = Mockery::mock(LotteryRegistrationRepositoryContract::class);
        $registrations->shouldReceive('findByProgramAndRequest')
            ->once()
            ->with($programId, $requestId)
            ->andReturn($existing);
        $this->app->instance(LotteryRegistrationRepositoryContract::class, $registrations);

        $this->expectException(DuplicateEnrollmentException::class);

        app(EnrollRegistrationAction::class)->execute($programId, $requestId);
    }

    #[Test]
    public function it_rejects_when_request_is_not_approved_lottery_registration(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $requestId = RequestReferenceId::fromString(UuidGenerator::uuid7());
        $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

        $program = $this->openProgram($programId, $dormitoryId);
        $this->mockProgramRepository($program);
        $this->mockRegistrationRepository(null);

        $requests = Mockery::mock(LotteryRequestReadPort::class);
        $requests->shouldReceive('findApprovedLotteryRegistration')
            ->once()
            ->with($requestId)
            ->andReturn(null);
        $this->app->instance(LotteryRequestReadPort::class, $requests);

        $this->expectException(LotteryValidationException::class);

        app(EnrollRegistrationAction::class)->execute($programId, $requestId);
    }

    #[Test]
    public function it_rejects_when_request_dormitory_does_not_match_program(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $requestId = RequestReferenceId::fromString(UuidGenerator::uuid7());
        $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

        $program = $this->openProgram($programId, $dormitoryId);
        $this->mockProgramRepository($program);
        $this->mockRegistrationRepository(null);

        $requests = Mockery::mock(LotteryRequestReadPort::class);
        $requests->shouldReceive('findApprovedLotteryRegistration')
            ->once()
            ->with($requestId)
            ->andReturn(new ApprovedLotteryRequestDTO(
                requestId: $requestId->value,
                employeeId: UuidGenerator::uuid7(),
                dormitoryId: UuidGenerator::uuid7(),
            ));
        $this->app->instance(LotteryRequestReadPort::class, $requests);

        $this->expectException(LotteryValidationException::class);

        app(EnrollRegistrationAction::class)->execute($programId, $requestId);
    }

    #[Test]
    public function it_persists_registration_and_dispatches_event_on_success(): void
    {
        Event::fake([LotteryRegistrationCreated::class]);

        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $requestId = RequestReferenceId::fromString(UuidGenerator::uuid7());
        $employeeId = UuidGenerator::uuid7();
        $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

        $program = $this->openProgram($programId, $dormitoryId);
        $this->mockProgramRepository($program);
        $this->mockRegistrationRepository(null);

        $requests = Mockery::mock(LotteryRequestReadPort::class);
        $requests->shouldReceive('findApprovedLotteryRegistration')
            ->once()
            ->with($requestId)
            ->andReturn(new ApprovedLotteryRequestDTO(
                requestId: $requestId->value,
                employeeId: $employeeId,
                dormitoryId: $dormitoryId->value,
            ));
        $this->app->instance(LotteryRequestReadPort::class, $requests);

        $registrationId = LotteryRegistrationId::fromString(UuidGenerator::uuid7());
        $persisted = new LotteryRegistration(
            id: $registrationId,
            programId: $programId,
            requestId: $requestId,
            employeeId: EmployeeReferenceId::fromString($employeeId),
            enrolledAt: new DateTimeImmutable('2026-06-30', new DateTimeZone('UTC')),
        );

        $registrations = Mockery::mock(LotteryRegistrationRepositoryContract::class);
        $registrations->shouldReceive('findByProgramAndRequest')
            ->once()
            ->with($programId, $requestId)
            ->andReturn(null);
        $registrations->shouldReceive('save')->once()->andReturn($persisted);
        $this->app->instance(LotteryRegistrationRepositoryContract::class, $registrations);

        $result = app(EnrollRegistrationAction::class)->execute($programId, $requestId);

        expect($result->requireId()->value)->toBe($registrationId->value);
        expect($result->employeeId->value)->toBe($employeeId);

        Event::assertDispatched(LotteryRegistrationCreated::class, function (LotteryRegistrationCreated $event) use ($registrationId, $programId, $requestId, $employeeId): bool {
            return $event->aggregateId === $registrationId->value
                && ($event->payload['program_id'] ?? null) === $programId->value
                && ($event->payload['request_id'] ?? null) === $requestId->value
                && ($event->payload['employee_id'] ?? null) === $employeeId;
        });
    }

    private function openProgram(LotteryProgramId $programId, DormitorySiteId $dormitoryId): LotteryProgram
    {
        return new LotteryProgram(
            id: $programId,
            title: 'Open Program',
            dormitoryId: $dormitoryId,
            capacity: 10,
            registrationStartsAt: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            registrationEndsAt: new DateTimeImmutable('2026-07-15', new DateTimeZone('UTC')),
            status: RegistrationOpenState::$name,
        );
    }

    private function mockProgramRepository(LotteryProgram $program): void
    {
        $programs = Mockery::mock(LotteryProgramRepositoryContract::class);
        $programs->shouldReceive('findById')
            ->once()
            ->with($program->requireId())
            ->andReturn($program);
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);
    }

    private function mockRegistrationRepository(?LotteryRegistration $existing): void
    {
        $registrations = Mockery::mock(LotteryRegistrationRepositoryContract::class);
        $registrations->shouldReceive('findByProgramAndRequest')->andReturn($existing);
        $this->app->instance(LotteryRegistrationRepositoryContract::class, $registrations);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
