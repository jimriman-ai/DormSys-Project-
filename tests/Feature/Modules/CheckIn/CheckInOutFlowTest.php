<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\CheckIn\Application\Contracts\CheckInCommandPort;
use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Domain\CheckInOperationRoles;
use App\Modules\CheckIn\Domain\Events\CheckedIn;
use App\Modules\CheckIn\Domain\Events\CheckedOut;
use App\Modules\CheckIn\Infrastructure\Persistence\Models\CheckInRecordModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;

function createCheckInOperator(): string
{
    Role::findOrCreate(CheckInOperationRoles::OPERATOR, config('auth.defaults.guard', 'web'));

    $user = createIdentityUserThroughMutation(
        'Check-In Operator',
        'operator-'.uniqid('', true).'@example.com',
    );

    assignRoleThroughMutation(
        $user->requireId(),
        CheckInOperationRoles::OPERATOR,
    );

    return $user->requireId()->value;
}

it('checks in and out on an active allocation', function (): void {
    Event::fake([CheckedIn::class, CheckedOut::class]);

    $allocation = runAllocationMutation(fn () => app(CreateAllocationAction::class)->execute(
        personId: UuidGenerator::uuid7(),
        bedId: createAssignableBedForAllocationTests(),
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ));

    $operatorId = createCheckInOperator();
    $allocationId = $allocation->requireId()->value;

    asCheckInOperator($operatorId, fn () => app(CheckInCommandPort::class)->checkIn($allocationId, $operatorId));

    $openRecord = app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId);

    expect($openRecord)->not->toBeNull();
    expect($openRecord?->operatorId)->toBe($operatorId);
    expect($openRecord?->isCheckedOut())->toBeFalse();

    Event::assertDispatched(CheckedIn::class);

    asCheckInOperator($operatorId, fn () => app(CheckInCommandPort::class)->checkOut($allocationId, $operatorId));

    $closedRecord = CheckInRecordModel::query()
        ->where('allocation_id', $allocationId)
        ->first();

    expect($closedRecord)->not->toBeNull();
    expect($closedRecord?->checked_out_at)->not->toBeNull();
    expect(app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId))->toBeNull();

    Event::assertDispatched(CheckedOut::class);
});

it('advances request-sourced allocation through checked_in and checked_out (DEBT-W3-01)', function (): void {
    $user = createIdentityUserThroughMutation(
        'Stay Lifecycle User',
        'stay.lifecycle.'.uniqid('', true).'@example.com',
    );

    $employee = createEmployeeThroughMutation(
        identityId: App\Modules\Employee\Domain\ValueObjects\IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-SL-'.substr(uniqid('', true), -6),
        firstName: 'Stay',
        lastName: 'Lifecycle',
        nationalCode: App\Support\ValueObjects\Identity\NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );

    $dormitoryId = createDormitorySiteForRequestTests();
    $bedId = createAssignableBedForAllocationTests();

    $draft = app(App\Modules\Request\Application\Services\CreatePersonalRequestAction::class)->execute(
        employeeId: App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: App\Modules\Request\Domain\ValueObjects\DormitorySiteId::fromString($dormitoryId),
        checkInDate: new DateTimeImmutable('2026-08-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $request = asRequestOwner($employee, fn () => app(App\Modules\Request\Application\Services\SubmitRequestAction::class)->execute($draft->requireId()));

    foreach (range(1, 4) as $_) {
        $request = approveRequestStageForTest($request);
    }

    $allocation = runAllocationMutation(fn () => app(App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction::class)->execute(
        requestId: $request->requireId()->value,
        bedId: $bedId,
    ));

    $reloadedAfterAlloc = app(App\Modules\Request\Application\Contracts\RequestRepositoryContract::class)
        ->findById($request->requireId());
    expect($reloadedAfterAlloc?->status)->toBe(App\Modules\Request\Domain\States\AllocatedState::$name);

    $operatorId = createCheckInOperator();
    $allocationId = $allocation->requireId()->value;

    asCheckInOperator($operatorId, fn () => app(CheckInCommandPort::class)->checkIn($allocationId, $operatorId));

    $afterCheckIn = app(App\Modules\Request\Application\Contracts\RequestRepositoryContract::class)
        ->findById($request->requireId());
    expect($afterCheckIn?->status)->toBe(App\Modules\Request\Domain\States\CheckedInState::$name);

    asCheckInOperator($operatorId, fn () => app(CheckInCommandPort::class)->checkOut($allocationId, $operatorId));

    $afterCheckOut = app(App\Modules\Request\Application\Contracts\RequestRepositoryContract::class)
        ->findById($request->requireId());
    expect($afterCheckOut?->status)->toBe(App\Modules\Request\Domain\States\CheckedOutState::$name);
});
