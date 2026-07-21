<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;
use App\Modules\Request\Application\Services\AssignStage1ApproverSnapshotAction;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Shared\Auth\IdentityRoleGuard;
use App\Support\ValueObjects\Identity\NationalCode;
use Database\Seeders\IdentityRoleSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function uniqueNationalCodeForStage1ConsoleTest(): NationalCode
{
    for ($attempt = 0; $attempt < 100; $attempt++) {
        $nine = str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);

        for ($check = 0; $check <= 9; $check++) {
            $candidate = $nine.(string) $check;

            if (NationalCode::isValid($candidate)) {
                return NationalCode::fromString($candidate);
            }
        }
    }

    throw new RuntimeException('Could not generate a valid national code for Stage-1 console test.');
}

function createEmployeeForStage1ConsoleTest(): Employee
{
    $user = createIdentityUserThroughMutation(
        'Stage1 Console Employee',
        'stage1.emp.'.uniqid('', true).'@example.com',
    );

    return createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-S1-'.substr(uniqid('', true), -6),
        firstName: 'Stage1',
        lastName: 'Employee',
        nationalCode: uniqueNationalCodeForStage1ConsoleTest(),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

/**
 * @return array{model: UserModel, approverId: ApproverReferenceId}
 */
function createDormitoryManagerApproverForStage1Console(): array
{
    $user = createIdentityUserThroughMutation(
        'Stage1 Dormitory Manager',
        'stage1.dormmgr.'.uniqid('', true).'@example.com',
    );
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $role = Role::findOrCreate(IdentityRoleGuard::ROLE_DORMITORY_MANAGER, 'identity');
    $model->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return [
        'model' => $model,
        'approverId' => ApproverReferenceId::fromString($model->getKey()),
    ];
}

/**
 * Non-approver identity for Stage-1 gate negative tests (SB-D1=A).
 * Holds `employee` only — must NOT use ROLE_DEPT_MGR (alias of dormitory-manager).
 *
 * @return array{model: UserModel, approverId: ApproverReferenceId}
 */
function createNonApproverIdentityForStage1Console(): array
{
    $user = createIdentityUserThroughMutation(
        'Stage1 Non-Approver',
        'stage1.nonapprover.'.uniqid('', true).'@example.com',
    );
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $role = Role::findOrCreate(IdentityRoleSeeder::ROLE_EMPLOYEE, 'identity');
    $model->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return [
        'model' => $model,
        'approverId' => ApproverReferenceId::fromString($model->getKey()),
    ];
}

/**
 * @param  array{model: UserModel, approverId: ApproverReferenceId}|null  $stage1Manager
 */
function createSubmittedStage1PersonalRequest(?array $stage1Manager = null): App\Modules\Request\Domain\Entities\Request
{
    $stage1Manager ??= createDormitoryManagerApproverForStage1Console();

    app()->instance(
        Stage1ApproverIdentityReadContract::class,
        new class($stage1Manager['approverId']->value) implements Stage1ApproverIdentityReadContract
        {
            public function __construct(private readonly string $identityId) {}

            public function resolveActiveDormitoryManagerIdentityId(): ?string
            {
                return $this->identityId !== '' ? $this->identityId : null;
            }
        },
    );

    app()->forgetInstance(AssignStage1ApproverSnapshotAction::class);
    app()->forgetInstance(CreatePersonalRequestAction::class);

    $employee = createEmployeeForStage1ConsoleTest();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $submitted = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));
    expect($submitted->status)->toBe(PendingDepartmentManagerState::$name);
    expect($submitted->assignedStage1ApproverIdentityId)->toBe($stage1Manager['approverId']->value);

    return $submitted;
}
