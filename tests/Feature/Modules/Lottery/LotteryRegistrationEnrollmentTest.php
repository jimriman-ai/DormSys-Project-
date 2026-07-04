<?php

declare(strict_types=1);

use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Domain\Events\LotteryRegistrationCreated;
use App\Modules\Lottery\Domain\Exceptions\DuplicateEnrollmentException;
use App\Modules\Lottery\Domain\Exceptions\RegistrationClosedException;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Request\Application\Services\ApproveRequestStageAction;
use App\Modules\Request\Application\Services\CreateLotteryRegistrationRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId as RequestDormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-30 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createEmployeeForLotteryEnrollmentTest(): Employee
{
    $user = app(CreateUserAction::class)->execute(
        'Lottery Enrollment User',
        'lottery.enroll.'.uniqid('', true).'@example.com',
    );

    return app(CreateEmployeeAction::class)->execute(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-LE-'.substr(uniqid('', true), -6),
        firstName: 'Lottery',
        lastName: 'Enrollee',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

function createApprovedLotteryRegistrationRequest(Employee $employee, string $dormitoryId): string
{
    $draft = app(CreateLotteryRegistrationRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: RequestDormitorySiteId::fromString($dormitoryId),
        checkInDate: now('UTC')->addDay()->startOfDay()->toDateTimeImmutable(),
        checkOutDate: now('UTC')->addMonths(6)->startOfDay()->toDateTimeImmutable(),
    );

    $request = app(SubmitRequestAction::class)->execute($draft->requireId());

    foreach (range(1, 4) as $_) {
        $request = app(ApproveRequestStageAction::class)->execute(
            $request->requireId(),
            ApproverReferenceId::fromString(UuidGenerator::uuid7()),
        );
    }

    expect($request->status)->toBe(ApprovedState::$name);

    return $request->requireId()->value;
}

it('enrolls an approved lottery registration request into an open program', function (): void {
    Event::fake([LotteryRegistrationCreated::class]);

    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Enrollment Test Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 20,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $program = app(OpenRegistrationAction::class)->execute($draft->requireId());
    expect($program->status)->toBe(RegistrationOpenState::$name);

    $registration = app(EnrollRegistrationAction::class)->execute(
        $program->requireId(),
        RequestReferenceId::fromString($requestId),
    );

    expect($registration->programId->value)->toBe($program->requireId()->value);
    expect($registration->requestId->value)->toBe($requestId);
    expect($registration->employeeId->value)->toBe($employee->requireId()->value);

    $reloaded = app(LotteryRegistrationRepositoryContract::class)->findByProgramAndRequest(
        $program->requireId(),
        RequestReferenceId::fromString($requestId),
    );

    expect($reloaded)->not->toBeNull();

    Event::assertDispatched(LotteryRegistrationCreated::class);
});

it('rejects duplicate enrollment for the same request', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Duplicate Enrollment Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 20,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $program = app(OpenRegistrationAction::class)->execute($draft->requireId());
    $reference = RequestReferenceId::fromString($requestId);

    app(EnrollRegistrationAction::class)->execute($program->requireId(), $reference);

    app(EnrollRegistrationAction::class)->execute($program->requireId(), $reference);
})->throws(DuplicateEnrollmentException::class);

it('rejects enrollment when program registration is closed', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = UuidGenerator::uuid7();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = app(CreateLotteryProgramAction::class)->execute(
        title: 'Closed Enrollment Program',
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        capacity: 20,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    app(EnrollRegistrationAction::class)->execute(
        $draft->requireId(),
        RequestReferenceId::fromString($requestId),
    );
})->throws(RegistrationClosedException::class);
