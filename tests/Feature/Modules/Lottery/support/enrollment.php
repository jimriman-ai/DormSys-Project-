<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Request\Application\Services\CreateLotteryRegistrationRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId as RequestDormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Support\ValueObjects\Identity\NationalCode;

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
