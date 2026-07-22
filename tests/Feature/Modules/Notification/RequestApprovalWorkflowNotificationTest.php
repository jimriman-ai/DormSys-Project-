<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Infrastructure\Persistence\Models\NotificationLogModel;
use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

/**
 * @return array{approver: Employee, requester: Employee, identityId: string}
 */
function createStage1ApproverEmployeePairForNotificationTest(): array
{
    $approverUser = createIdentityUserThroughMutation(
        'Stage1 Notify Approver',
        'stage1.notify.approver.'.uniqid('', true).'@example.com',
    );
    $identityId = $approverUser->requireId()->value;

    app()->instance(
        Stage1ApproverIdentityReadContract::class,
        new class($identityId) implements Stage1ApproverIdentityReadContract
        {
            public function __construct(private readonly string $identityId) {}

            public function resolveActiveDormitoryManagerIdentityId(): ?string
            {
                return $this->identityId !== '' ? $this->identityId : null;
            }
        },
    );

    $approver = createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($identityId),
        employeeCode: 'EMP-NA-'.substr(uniqid('', true), -6),
        firstName: 'Notify',
        lastName: 'Approver',
        nationalCode: NationalCode::fromString('0013542419'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );

    $requesterUser = createIdentityUserThroughMutation(
        'Notify Requester',
        'notify.requester.'.uniqid('', true).'@example.com',
    );
    $requester = createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($requesterUser->requireId()->value),
        employeeCode: 'EMP-NR-'.substr(uniqid('', true), -6),
        firstName: 'Notify',
        lastName: 'Requester',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );

    return [
        'approver' => $approver,
        'requester' => $requester,
        'identityId' => $identityId,
    ];
}

function createSubmittedRequestForNotificationTest(Employee $requester): App\Modules\Request\Domain\Entities\Request
{
    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($requester->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    return asRequestOwner($requester, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));
}

it('delivers request_submitted and request_approval_pending to the stage-1 approver employee on submit', function (): void {
    $pair = createStage1ApproverEmployeePairForNotificationTest();
    $submitted = createSubmittedRequestForNotificationTest($pair['requester']);

    $approverEmployeeId = $pair['approver']->requireId()->value;

    $rows = NotificationLogModel::query()
        ->where('recipient_employee_id', $approverEmployeeId)
        ->orderBy('notification_type')
        ->get();

    expect($rows)->toHaveCount(2);
    expect($rows->pluck('notification_type')->map(fn ($t) => $t->value)->all())->toBe([
        NotificationType::RequestApprovalPending->value,
        NotificationType::RequestSubmitted->value,
    ]);

    expect(NotificationLogModel::query()
        ->where('recipient_employee_id', $pair['requester']->requireId()->value)
        ->count())->toBe(0);

    expect($submitted->assignedStage1ApproverIdentityId)->toBe($pair['identityId']);
});

it('delivers request_approved once despite Request and Workflow terminal dual emission', function (): void {
    $pair = createStage1ApproverEmployeePairForNotificationTest();
    $submitted = createSubmittedRequestForNotificationTest($pair['requester']);

    $afterStage1 = approveRequestStageForTest($submitted);
    expect($afterStage1->status)->toBe(PendingHRState::$name);

    // Advance remaining stages with non-stage-1 principals (S2–S4 permissive role port).
    $request = approveRequestStageForTest($afterStage1);
    $request = approveRequestStageForTest($request);
    $request = approveRequestStageForTest($request);
    expect($request->status)->toBe(ApprovedState::$name);

    $approvedRows = NotificationLogModel::query()
        ->where('recipient_employee_id', $pair['requester']->requireId()->value)
        ->where('notification_type', NotificationType::RequestApproved)
        ->get();

    expect($approvedRows)->toHaveCount(1);
    $approvedRow = $approvedRows->first();
    expect($approvedRow)->not->toBeNull();
    if ($approvedRow === null) {
        throw new RuntimeException('Expected request_approved notification row.');
    }
    expect($approvedRow->correlation_id)->toBe(
        'request:'.$request->requireId()->value.':approved',
    );
});

it('delivers request_rejected once despite dual terminal sources', function (): void {
    $pair = createStage1ApproverEmployeePairForNotificationTest();
    $submitted = createSubmittedRequestForNotificationTest($pair['requester']);

    $approver = [
        'principalId' => $pair['identityId'],
        'approverId' => \App\Modules\Request\Domain\ValueObjects\ApproverReferenceId::fromString($pair['identityId']),
    ];

    $rejected = rejectRequestStageForTest($submitted, 'Documents incomplete.', $approver);
    expect($rejected->status)->toBe(RejectedState::$name);

    $rejectedRows = NotificationLogModel::query()
        ->where('recipient_employee_id', $pair['requester']->requireId()->value)
        ->where('notification_type', NotificationType::RequestRejected)
        ->get();

    expect($rejectedRows)->toHaveCount(1);
    $rejectedRow = $rejectedRows->first();
    expect($rejectedRow)->not->toBeNull();
    if ($rejectedRow === null) {
        throw new RuntimeException('Expected request_rejected notification row.');
    }
    expect($rejectedRow->correlation_id)->toBe(
        'request:'.$rejected->requireId()->value.':rejected',
    );
});

it('does not emit request_approval_pending for non-stage-1 activations (C1)', function (): void {
    $pair = createStage1ApproverEmployeePairForNotificationTest();
    $submitted = createSubmittedRequestForNotificationTest($pair['requester']);

    $pendingBefore = NotificationLogModel::query()
        ->where('notification_type', NotificationType::RequestApprovalPending)
        ->count();

    approveRequestStageForTest($submitted);

    $pendingAfter = NotificationLogModel::query()
        ->where('notification_type', NotificationType::RequestApprovalPending)
        ->count();

    expect($pendingAfter)->toBe($pendingBefore);
});
