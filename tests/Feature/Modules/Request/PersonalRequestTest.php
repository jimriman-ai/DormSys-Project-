<?php

declare(strict_types=1);

use App\Modules\Employee\Application\Contracts\EmployeeEligibilityContract;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Employee\Application\DTOs\EligibilityResultDTO;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\Exceptions\RequestNotEligibleException;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function createActiveEmployeeForRequest(): Employee
{
    $user = createIdentityUserThroughMutation(
        'Request Test User',
        'request.test.'.uniqid('', true).'@example.com',
    );

    return createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-REQ-'.substr(uniqid('', true), -6),
        firstName: 'Request',
        lastName: 'Tester',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('submits an eligible personal request into the approval pipeline', function (): void {
    $employee = createActiveEmployeeForRequest();
    $dormitoryId = createDormitorySiteForRequestTests();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $submitted = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));

    expect($submitted->status)->toBe(PendingDepartmentManagerState::$name);
    expect($submitted->submittedAt)->not->toBeNull();
    expect($submitted->employeeId->value)->toBe($employee->requireId()->value);
    expect($submitted->dormitoryId->value)->toBe($dormitoryId);

    $reloaded = app(RequestRepositoryContract::class)->findById($submitted->requireId());
    expect($reloaded?->status)->toBe(PendingDepartmentManagerState::$name);
});

it('rejects submit for an ineligible employee with stable reason codes', function (): void {
    $employee = createActiveEmployeeForRequest();
    $employee->deactivate();
    app(EmployeeRepositoryContract::class)->save($employee);

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    try {
        asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));
        test()->fail('Expected RequestNotEligibleException to be thrown.');
    } catch (RequestNotEligibleException $exception) {
        expect($exception->reasonCodes)->toContain('employee_inactive');
    }

    $unchanged = app(RequestRepositoryContract::class)->findById($draft->requireId());
    expect($unchanged?->status)->toBe(DraftState::$name);
});

it('rejects submit when eligibility contract reports pending request exists', function (): void {
    $employee = createActiveEmployeeForRequest();

    $this->mock(EmployeeEligibilityContract::class, function ($mock): void {
        $mock->shouldReceive('computeRequestEligibility')
            ->once()
            ->andReturn(new EligibilityResultDTO(
                eligible: false,
                reasonCodes: ['pending_request_exists'],
                evaluatedAt: new DateTimeImmutable('2026-06-23 12:00:00'),
            ));
    });

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    try {
        asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));
        test()->fail('Expected RequestNotEligibleException to be thrown.');
    } catch (RequestNotEligibleException $exception) {
        expect($exception->reasonCodes)->toBe(['pending_request_exists']);
    }
});
