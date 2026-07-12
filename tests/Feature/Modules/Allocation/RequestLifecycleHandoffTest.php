<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Contracts\RequestLifecycleCommandPort;
use App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\Support\MockeryTest;

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-01 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

/**
 * @return array{0: Employee, 1: Request, 2: string}
 */
function createApprovedRequestForLifecycleHandoffTest(): array
{
    $user = createIdentityUserThroughMutation(
        'Lifecycle Handoff User',
        'lifecycle.handoff.'.uniqid('', true).'@example.com',
    );

    $employee = createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-LH-'.substr(uniqid('', true), -6),
        firstName: 'Lifecycle',
        lastName: 'Handoff',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );

    $dormitoryId = createDormitorySiteForRequestTests();
    $bedId = createAssignableBedForAllocationTests();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        checkInDate: new DateTimeImmutable('2026-08-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $request = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));

    foreach (range(1, 4) as $_) {
        $request = approveRequestStageForTest($request);
    }

    expect($request->status)->toBe(ApprovedState::$name);

    return [$employee, $request, $bedId];
}

it('invokes RequestLifecycleCommandPort on successful allocation from request source', function (): void {
    [$employee, $request, $bedId] = createApprovedRequestForLifecycleHandoffTest();

    $lifecycle = MockeryTest::mock(RequestLifecycleCommandPort::class);
    MockeryTest::expectOnce($lifecycle, 'markAllocated')
        ->with(
            $request->requireId()->value,
            Mockery::type('string'),
        );

    app()->instance(RequestLifecycleCommandPort::class, $lifecycle);
    app()->forgetInstance(CreateAllocationFromRequestAction::class);

    $allocation = runAllocationMutation(fn () => app(CreateAllocationFromRequestAction::class)->execute(
        requestId: $request->requireId()->value,
        bedId: $bedId,
    ));

    expect($allocation->personId->value)->toBe($employee->requireId()->value);
});
