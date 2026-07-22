<?php

declare(strict_types=1);

use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Workflow\Application\Contracts\RequestApprovalWorkflowRepositoryContract;
use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;
use App\Modules\Workflow\Domain\Enums\WorkflowInstanceStatus;
use App\Modules\Workflow\Domain\Enums\WorkflowStepStatus;
use App\Modules\Workflow\Domain\ValueObjects\RequestReferenceId;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('starts a running workflow instance when a request is submitted', function (): void {
    $user = createIdentityUserThroughMutation(
        'Cutover Submit User',
        'cutover.submit.'.uniqid('', true).'@example.com',
    );
    $employee = createEmployeeThroughMutation(
        identityId: \App\Modules\Employee\Domain\ValueObjects\IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-CO-'.substr(uniqid('', true), -6),
        firstName: 'Cutover',
        lastName: 'Submit',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $submitted = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));
    expect($submitted->status)->toBe(PendingDepartmentManagerState::$name);

    $instance = app(RequestApprovalWorkflowRepositoryContract::class)
        ->findRunningByRequestId(RequestReferenceId::fromString($submitted->requireId()->value));

    expect($instance)->not->toBeNull();
    if ($instance === null) {
        throw new RuntimeException('Expected running workflow after submit.');
    }
    expect($instance->status)->toBe(WorkflowInstanceStatus::Running)
        ->and($instance->currentStage)->toBe(RequestApprovalWorkflowStage::DepartmentManager)
        ->and($instance->stage1ApproverIdentityId?->value)->toBe($submitted->assignedStage1ApproverIdentityId);
});

it('records workflow step audit when approving while keeping RequestApproval canonical', function (): void {
    $submitted = createSubmittedPersonalRequest();
    $approved = approveRequestStageForTest($submitted);

    expect($approved->status)->toBe(PendingHRState::$name);

    $instance = app(RequestApprovalWorkflowRepositoryContract::class)
        ->findRunningByRequestId(RequestReferenceId::fromString($approved->requireId()->value));

    expect($instance)->not->toBeNull();
    if ($instance === null) {
        throw new RuntimeException('Expected running workflow after stage approve.');
    }
    expect($instance->currentStage)->toBe(RequestApprovalWorkflowStage::HR)
        ->and($instance->steps)->toHaveCount(2)
        ->and($instance->steps[0]->status)->toBe(WorkflowStepStatus::Approved)
        ->and($instance->steps[1]->status)->toBe(WorkflowStepStatus::Pending);

    expect(app(\App\Modules\Request\Application\Contracts\RequestApprovalRepositoryContract::class)
        ->countForRequest($approved->requireId()))->toBe(1);
});

it('denies stage-1 approve when actor is not the snapshotted approver', function (): void {
    $submitted = createSubmittedPersonalRequest();
    $other = createMutationApprover();

    expect(fn () => approveRequestStageForTest($submitted, $other))
        ->toThrow(\App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException::class);
});
