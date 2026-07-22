<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Domain\Events\LotteryRegistrationCreated;
use App\Modules\Lottery\Domain\Exceptions\DuplicateEnrollmentException;
use App\Modules\Lottery\Domain\Exceptions\RegistrationClosedException;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Modules\Lottery\Domain\ValueObjects\RequestReferenceId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

require_once __DIR__.'/support/enrollment.php';

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-30 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

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
