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
use App\Modules\Lottery\Domain\States\RegistrationClosedState;
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
use Tests\Support\MockeryTest;
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

        $employeeId = UuidGenerator::uuid7();

        $requests = MockeryTest::mock(LotteryRequestReadPort::class);
        MockeryTest::expectOnce($requests, 'findApprovedLotteryRegistration')
            ->with($requestId)
            ->andReturn(new ApprovedLotteryRequestDTO(
                requestId: $requestId->value,
                employeeId: $employeeId,
                dormitoryId: $dormitoryId->value,
            ));
        $this->app->instance(LotteryRequestReadPort::class, $requests);

        $this->mockProgramRepository($program);

        $this->expectException(RegistrationClosedException::class);

        $this->executeEnroll($programId, $requestId, $employeeId);
    }

    #[Test]
    public function it_rejects_enrollment_when_program_closes_before_transaction_persists(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $requestId = RequestReferenceId::fromString(UuidGenerator::uuid7());
        $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());
        $employeeId = UuidGenerator::uuid7();

        $openProgram = $this->openProgram($programId, $dormitoryId);
        $closedProgram = new LotteryProgram(
            id: $programId,
            title: $openProgram->title,
            dormitoryId: $dormitoryId,
            capacity: $openProgram->capacity,
            registrationStartsAt: $openProgram->registrationStartsAt,
            registrationEndsAt: $openProgram->registrationEndsAt,
            status: RegistrationClosedState::$name,
        );

        $programs = MockeryTest::mock(LotteryProgramRepositoryContract::class);
        MockeryTest::expectOnce($programs, 'findById')->with($programId)->andReturn($openProgram);
        MockeryTest::expectOnce($programs, 'findByIdForUpdate')->with($programId)->andReturn($closedProgram);
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);

        $requests = MockeryTest::mock(LotteryRequestReadPort::class);
        MockeryTest::expectOnce($requests, 'findApprovedLotteryRegistration')
            ->with($requestId)
            ->andReturn(new ApprovedLotteryRequestDTO(
                requestId: $requestId->value,
                employeeId: $employeeId,
                dormitoryId: $dormitoryId->value,
            ));
        $this->app->instance(LotteryRequestReadPort::class, $requests);

        $registrations = MockeryTest::mock(LotteryRegistrationRepositoryContract::class);
        $registrations->shouldNotReceive('save');
        $this->app->instance(LotteryRegistrationRepositoryContract::class, $registrations);

        $this->expectException(RegistrationClosedException::class);

        $this->executeEnroll($programId, $requestId, $employeeId);
    }

    #[Test]
    public function it_rejects_duplicate_enrollment_for_same_request_inside_transaction(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $requestId = RequestReferenceId::fromString(UuidGenerator::uuid7());
        $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());
        $employeeId = UuidGenerator::uuid7();

        $program = $this->openProgram($programId, $dormitoryId);
        $existing = LotteryRegistration::enroll(
            programId: $programId,
            requestId: $requestId,
            employeeId: EmployeeReferenceId::fromString(UuidGenerator::uuid7()),
            enrolledAt: new DateTimeImmutable('2026-06-30', new DateTimeZone('UTC')),
        );

        $programs = MockeryTest::mock(LotteryProgramRepositoryContract::class);
        MockeryTest::expectOnce($programs, 'findById')->with($programId)->andReturn($program);
        MockeryTest::expectOnce($programs, 'findByIdForUpdate')->with($programId)->andReturn($program);
        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);

        $requests = MockeryTest::mock(LotteryRequestReadPort::class);
        MockeryTest::expectOnce($requests, 'findApprovedLotteryRegistration')
            ->with($requestId)
            ->andReturn(new ApprovedLotteryRequestDTO(
                requestId: $requestId->value,
                employeeId: $employeeId,
                dormitoryId: $dormitoryId->value,
            ));
        $this->app->instance(LotteryRequestReadPort::class, $requests);

        $registrations = MockeryTest::mock(LotteryRegistrationRepositoryContract::class);
        MockeryTest::expectOnce($registrations, 'findByProgramAndRequest')
            ->with($programId, $requestId)
            ->andReturn($existing);
        $registrations->shouldNotReceive('save');
        $this->app->instance(LotteryRegistrationRepositoryContract::class, $registrations);

        $this->expectException(DuplicateEnrollmentException::class);

        $this->executeEnroll($programId, $requestId, $employeeId);
    }

    #[Test]
    public function it_rejects_when_request_is_not_approved_lottery_registration(): void
    {
        $programId = LotteryProgramId::fromString(UuidGenerator::uuid7());
        $requestId = RequestReferenceId::fromString(UuidGenerator::uuid7());

        $requests = MockeryTest::mock(LotteryRequestReadPort::class);
        MockeryTest::expectOnce($requests, 'findApprovedLotteryRegistration')
            ->with($requestId)
            ->andReturn(null);
        $this->app->instance(LotteryRequestReadPort::class, $requests);

        bypassLotteryMutationAuthorization();

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

        $employeeId = UuidGenerator::uuid7();

        $requests = MockeryTest::mock(LotteryRequestReadPort::class);
        MockeryTest::expectOnce($requests, 'findApprovedLotteryRegistration')
            ->with($requestId)
            ->andReturn(new ApprovedLotteryRequestDTO(
                requestId: $requestId->value,
                employeeId: $employeeId,
                dormitoryId: UuidGenerator::uuid7(),
            ));
        $this->app->instance(LotteryRequestReadPort::class, $requests);

        $this->expectException(LotteryValidationException::class);

        $this->executeEnroll($programId, $requestId, $employeeId);
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
        $this->mockProgramRepository($program, forUpdate: true);

        $requests = MockeryTest::mock(LotteryRequestReadPort::class);
        MockeryTest::expectOnce($requests, 'findApprovedLotteryRegistration')
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

        $registrations = MockeryTest::mock(LotteryRegistrationRepositoryContract::class);
        MockeryTest::expectOnce($registrations, 'findByProgramAndRequest')
            ->with($programId, $requestId)
            ->andReturn(null);
        MockeryTest::expectOnce($registrations, 'save')->andReturn($persisted);
        $this->app->instance(LotteryRegistrationRepositoryContract::class, $registrations);

        $result = $this->executeEnroll($programId, $requestId, $employeeId);

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

    private function mockProgramRepository(LotteryProgram $program, bool $forUpdate = false): void
    {
        $programs = MockeryTest::mock(LotteryProgramRepositoryContract::class);
        MockeryTest::expectOnce($programs, 'findById')
            ->with($program->requireId())
            ->andReturn($program);

        if ($forUpdate) {
            MockeryTest::expectOnce($programs, 'findByIdForUpdate')
                ->with($program->requireId())
                ->andReturn($program);
        }

        $this->app->instance(LotteryProgramRepositoryContract::class, $programs);
    }

    private function executeEnroll(
        LotteryProgramId $programId,
        RequestReferenceId $requestId,
        string $employeeId,
    ): LotteryRegistration {
        $principalId = UuidGenerator::uuid7();
        configureLotteryEnrollMutationAuthorization($principalId, $employeeId);

        return app(EnrollRegistrationAction::class)->execute($programId, $requestId);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
