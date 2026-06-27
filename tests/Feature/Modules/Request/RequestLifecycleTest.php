<?php

declare(strict_types=1);

use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Services\CancelRequestAction;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Events\RequestCancelled;
use App\Modules\Request\Domain\Events\RequestSubmitted;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\States\SubmittedState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createActiveEmployeeForLifecycleTest(): Employee
{
    $user = app(CreateUserAction::class)->execute(
        'Lifecycle Test User',
        'lifecycle.test.'.uniqid('', true).'@example.com',
    );

    return app(CreateEmployeeAction::class)->execute(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-LC-'.substr(uniqid('', true), -6),
        firstName: 'Lifecycle',
        lastName: 'Tester',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

function createDraftPersonalRequest(): Request
{
    $employee = createActiveEmployeeForLifecycleTest();

    return app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );
}

it('cancels a draft request and dispatches request cancelled', function (): void {
    Event::fake([RequestCancelled::class]);

    $draft = createDraftPersonalRequest();
    $cancelled = app(CancelRequestAction::class)->execute($draft->requireId());

    expect($cancelled->status)->toBe(CancelledState::$name);
    expect($cancelled->cancelledAt)->not->toBeNull();

    Event::assertDispatched(RequestCancelled::class, function (RequestCancelled $event) use ($draft): bool {
        return $event->aggregateId === $draft->requireId()->value
            && ($event->payload['previous_status'] ?? null) === DraftState::$name;
    });
});

it('cancels a submitted request before approval entry', function (): void {
    $draft = createDraftPersonalRequest();
    $repository = app(RequestRepositoryContract::class);

    $submitted = $repository->save(new Request(
        id: $draft->requireId(),
        code: $draft->code,
        employeeId: $draft->employeeId,
        dormitoryId: $draft->dormitoryId,
        type: $draft->type,
        checkInDate: $draft->checkInDate,
        checkOutDate: $draft->checkOutDate,
        status: SubmittedState::$name,
        submittedAt: new DateTimeImmutable('2026-06-23 12:00:00'),
    ));

    expect($submitted->status)->toBe(SubmittedState::$name);

    $cancelled = app(CancelRequestAction::class)->execute($submitted->requireId());

    expect($cancelled->status)->toBe(CancelledState::$name);
});

it('rejects cancel from a pending approval stage', function (): void {
    $draft = createDraftPersonalRequest();
    $submitted = app(SubmitRequestAction::class)->execute($draft->requireId());

    expect($submitted->status)->toBe(PendingDepartmentManagerState::$name);

    expect(fn () => app(CancelRequestAction::class)->execute($submitted->requireId()))
        ->toThrow(InvalidRequestTransitionException::class);

    $unchanged = app(RequestRepositoryContract::class)->findById($submitted->requireId());
    expect($unchanged?->status)->toBe(PendingDepartmentManagerState::$name);
});

it('rejects cancel from a terminal rejected request', function (): void {
    $draft = createDraftPersonalRequest();
    $repository = app(RequestRepositoryContract::class);

    $rejected = $repository->save(new Request(
        id: $draft->requireId(),
        code: $draft->code,
        employeeId: $draft->employeeId,
        dormitoryId: $draft->dormitoryId,
        type: $draft->type,
        checkInDate: $draft->checkInDate,
        checkOutDate: $draft->checkOutDate,
        status: RejectedState::$name,
        submittedAt: new DateTimeImmutable('2026-06-23 12:00:00'),
        rejectionReason: 'Not eligible for housing.',
    ));

    expect($rejected->isTerminal())->toBeTrue();

    expect(fn () => app(CancelRequestAction::class)->execute($rejected->requireId()))
        ->toThrow(InvalidRequestTransitionException::class);
});

it('dispatches request submitted after successful submit', function (): void {
    Event::fake([RequestSubmitted::class]);

    $draft = createDraftPersonalRequest();
    app(SubmitRequestAction::class)->execute($draft->requireId());

    Event::assertDispatched(RequestSubmitted::class);
});
