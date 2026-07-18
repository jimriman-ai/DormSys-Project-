<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;
use App\Modules\Request\Application\Services\ApproveStage1RequestAction;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\RejectStage1RequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Shared\Auth\IdentityRoleGuard;
use App\Support\ValueObjects\Identity\NationalCode;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

afterEach(function (): void {
    Carbon::setTestNow();
});

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
        nationalCode: NationalCode::fromString('0499370899'),
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

function createSubmittedStage1PersonalRequest(): App\Modules\Request\Domain\Entities\Request
{
    $employee = createEmployeeForStage1ConsoleTest();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $submitted = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));
    expect($submitted->status)->toBe(PendingDepartmentManagerState::$name);

    return $submitted;
}

it('blocks stage-1 approve when actor lacks dormitory-manager identity role', function (): void {
    $submitted = createSubmittedStage1PersonalRequest();
    $user = createIdentityUserThroughMutation(
        'Unauthorized Approver',
        'stage1.unauth.'.uniqid('', true).'@example.com',
    );
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $this->actingAs($model, 'identity');

    expect(fn () => asRequestMutationPrincipal(
        $model->getKey(),
        fn () => app(ApproveStage1RequestAction::class)->execute(
            $submitted->requireId(),
            ApproverReferenceId::fromString($model->getKey()),
        ),
    ))->toThrow(HttpException::class);
});

it('rejects employee-only identity on stage-1 approve gate (403)', function (): void {
    $submitted = createSubmittedStage1PersonalRequest();
    $nonApprover = createNonApproverIdentityForStage1Console();
    $this->actingAs($nonApprover['model'], 'identity');

    expect(fn () => asRequestMutationPrincipal(
        $nonApprover['approverId']->value,
        fn () => app(ApproveStage1RequestAction::class)->execute(
            $submitted->requireId(),
            $nonApprover['approverId'],
        ),
    ))->toThrow(HttpException::class);
});

it('allows dormitory-manager to approve a stage-1 pending personal request', function (): void {
    $submitted = createSubmittedStage1PersonalRequest();
    $approver = createDormitoryManagerApproverForStage1Console();
    $this->actingAs($approver['model'], 'identity');

    $approved = asRequestMutationPrincipal(
        $approver['approverId']->value,
        fn () => app(ApproveStage1RequestAction::class)->execute(
            $submitted->requireId(),
            $approver['approverId'],
        ),
    );

    expect($approved->status)->toBe(PendingHRState::$name);
});

it('allows dormitory-manager to reject a stage-1 pending personal request', function (): void {
    $submitted = createSubmittedStage1PersonalRequest();
    $approver = createDormitoryManagerApproverForStage1Console();
    $this->actingAs($approver['model'], 'identity');

    $rejected = asRequestMutationPrincipal(
        $approver['approverId']->value,
        fn () => app(RejectStage1RequestAction::class)->execute(
            $submitted->requireId(),
            $approver['approverId'],
            'Incomplete supporting documents.',
        ),
    );

    expect($rejected->status)->toBe(RejectedState::$name)
        ->and($rejected->rejectionReason)->toBe('Incomplete supporting documents.');
});

it('allows the snapshotted stage-1 identity to pass the console approve gate (coherence)', function (): void {
    $manager = createDormitoryManagerApproverForStage1Console();

    app()->instance(
        Stage1ApproverIdentityReadContract::class,
        new class($manager['approverId']->value) implements Stage1ApproverIdentityReadContract
        {
            public function __construct(private readonly string $identityId) {}

            public function resolveActiveDormitoryManagerIdentityId(): ?string
            {
                return $this->identityId;
            }
        },
    );

    $employee = createEmployeeForStage1ConsoleTest();
    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    expect($draft->assignedStage1ApproverIdentityId)->toBe($manager['approverId']->value);

    $submitted = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));

    $this->actingAs($manager['model'], 'identity');

    $approved = asRequestMutationPrincipal(
        $manager['approverId']->value,
        fn () => app(ApproveStage1RequestAction::class)->execute(
            $submitted->requireId(),
            $manager['approverId'],
        ),
    );

    expect($approved->status)->toBe(PendingHRState::$name);
});
