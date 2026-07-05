<?php

declare(strict_types=1);

use App\Integrations\Request\PendingRequestReadBridge;
use App\Modules\Employee\Application\Contracts\EmployeeEligibilityContract;
use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Request\Application\Services\ApproveRequestStageAction;
use App\Modules\Request\Application\Services\CancelRequestAction;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\Exceptions\RequestNotEligibleException;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createEmployeeForPendingPortTest(): Employee
{
    $user = app(CreateUserAction::class)->execute(
        'Pending Port User',
        'pending.port.'.uniqid('', true).'@example.com',
    );

    return app(CreateEmployeeAction::class)->execute(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-PP-'.substr(uniqid('', true), -6),
        firstName: 'Pending',
        lastName: 'Port',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

it('returns true when a non-terminal request exists (BT-R08)', function (): void {
    $employee = createEmployeeForPendingPortTest();
    $port = app(PendingRequestReadPort::class);

    expect($port->hasPendingRequest($employee->requireId()->value))->toBeFalse();

    app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    expect($port->hasPendingRequest($employee->requireId()->value))->toBeTrue();
});

it('returns false for terminal request statuses (BT-R08)', function (): void {
    $employee = createEmployeeForPendingPortTest();
    $employeeId = $employee->requireId()->value;
    $port = app(PendingRequestReadPort::class);

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $submitted = app(SubmitRequestAction::class)->execute($draft->requireId());
    expect($submitted->status)->toBe(PendingDepartmentManagerState::$name);
    expect($port->hasPendingRequest($employeeId))->toBeTrue();

    $request = $submitted;
    foreach (range(1, 4) as $_) {
        $request = app(ApproveRequestStageAction::class)->execute(
            $request->requireId(),
            ApproverReferenceId::fromString(UuidGenerator::uuid7()),
        );
    }

    expect($request->status)->toBe(ApprovedState::$name);
    expect($port->hasPendingRequest($employeeId))->toBeFalse();
});

it('exposes only the read-only port surface (BT-R09 / OA-05-09)', function (): void {
    $adapterReflection = new ReflectionClass(PendingRequestReadBridge::class);
    $publicAdapterMethods = array_filter(
        $adapterReflection->getMethods(ReflectionMethod::IS_PUBLIC),
        static fn (ReflectionMethod $method): bool => ! $method->isConstructor() && ! $method->isStatic(),
    );

    expect(array_values(array_map(static fn (ReflectionMethod $method): string => $method->getName(), $publicAdapterMethods)))
        ->toBe(['hasPendingRequest']);
});

it('blocks submit when eligibility detects an existing pending request (CD-013)', function (): void {
    $employee = createEmployeeForPendingPortTest();

    app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $secondDraft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-08-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    expect(fn () => app(SubmitRequestAction::class)->execute($secondDraft->requireId()))
        ->toThrow(function (Throwable $exception): bool {
            return $exception instanceof RequestNotEligibleException
                && $exception->reasonCodes === ['pending_request_exists'];
        });

    $eligibility = app(EmployeeEligibilityContract::class)->computeRequestEligibility(
        $employee->requireId()->value,
    );

    expect($eligibility->eligible)->toBeFalse();
    expect($eligibility->reasonCodes)->toBe(['pending_request_exists']);
});

it('does not treat cancelled requests as pending (R-04)', function (): void {
    $employee = createEmployeeForPendingPortTest();
    $employeeId = $employee->requireId()->value;

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $cancelled = app(CancelRequestAction::class)->execute($draft->requireId());
    expect($cancelled->status)->toBe(CancelledState::$name);

    expect(app(PendingRequestReadPort::class)->hasPendingRequest($employeeId))->toBeFalse();
});
