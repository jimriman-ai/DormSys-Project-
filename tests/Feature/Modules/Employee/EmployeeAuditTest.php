<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Employee\Infrastructure\Persistence\Models\EmployeeModel;
use App\Support\ValueObjects\Identity\NationalCode;
use Spatie\Activitylog\Models\Activity;

it('records activity when an employee is created', function (): void {
    $user = createIdentityUserThroughMutation('Audit Employee User', 'audit.employee@example.com');
    $identityId = IdentityUserId::fromString($user->requireId()->value);

    $employee = createEmployeeThroughMutation(
        identityId: $identityId,
        employeeCode: 'EMP-AUDIT',
        firstName: 'Sara',
        lastName: 'Karimi',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-06-01'),
    );

    $activity = Activity::query()
        ->where('subject_type', EmployeeModel::class)
        ->where('subject_id', $employee->requireId()->value)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull();
});
