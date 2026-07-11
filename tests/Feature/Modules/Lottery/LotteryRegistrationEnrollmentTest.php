<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Domain\Events\LotteryRegistrationCreated;
use App\Modules\Lottery\Domain\Exceptions\DuplicateEnrollmentException;
use App\Modules\Lottery\Domain\Exceptions\RegistrationClosedException;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Request\Application\Services\CreateLotteryRegistrationRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId as RequestDormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
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
    $user = createIdentityUserThroughMutation(
        'Lottery Enrollment User',
        'lottery.enroll.'.uniqid('', true).'@example.com',
    );

    return createEmployeeThroughMutation(
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

    $request = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));

    foreach (range(1, 4) as $_) {
        $request = approveRequestStageForTest($request);
    }

    expect($request->status)->toBe(ApprovedState::$name);

    return $request->requireId()->value;
}

it('enrolls an approved lottery registration request into an open program', function (): void {
    Event::fake([LotteryRegistrationCreated::class]);

    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = createLotteryProgramForTest(
        title: 'Enrollment Test Program',
        dormitoryId: $dormitoryId,
        capacity: 20,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $program = openLotteryProgramForTest($draft->requireId());
    expect($program->status)->toBe(RegistrationOpenState::$name);

    $registration = asRequestOwner($employee, fn () => app(EnrollRegistrationAction::class)->execute(
        $program->requireId(),
        RequestReferenceId::fromString($requestId),
    ));

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
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = createLotteryProgramForTest(
        title: 'Duplicate Enrollment Program',
        dormitoryId: $dormitoryId,
        capacity: 20,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    $program = openLotteryProgramForTest($draft->requireId());
    $reference = RequestReferenceId::fromString($requestId);

    asRequestOwner($employee, fn () => app(EnrollRegistrationAction::class)->execute($program->requireId(), $reference));

    asRequestOwner($employee, fn () => app(EnrollRegistrationAction::class)->execute($program->requireId(), $reference));
})->throws(DuplicateEnrollmentException::class);

it('rejects enrollment when program registration is closed', function (): void {
    $employee = createEmployeeForLotteryEnrollmentTest();
    $dormitoryId = createDormitorySiteForRequestTests();
    $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

    $draft = createLotteryProgramForTest(
        title: 'Closed Enrollment Program',
        dormitoryId: $dormitoryId,
        capacity: 20,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-31 23:59:59', new DateTimeZone('UTC')),
    );

    asRequestOwner($employee, fn () => app(EnrollRegistrationAction::class)->execute(
        $draft->requireId(),
        RequestReferenceId::fromString($requestId),
    ));
})->throws(RegistrationClosedException::class);
