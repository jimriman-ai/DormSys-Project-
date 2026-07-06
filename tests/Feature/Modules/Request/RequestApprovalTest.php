<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Request\Application\Contracts\RequestApprovalRepositoryContract;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\RejectRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\ApprovalDecision;
use App\Modules\Request\Domain\Events\RequestApprovalRecorded;
use App\Modules\Request\Domain\Events\RequestApproved;
use App\Modules\Request\Domain\Events\RequestRejected;
use App\Modules\Request\Domain\Exceptions\AppendOnlyViolationException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryUnitState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestApprovalModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createActiveEmployeeForApprovalTest(): Employee
{
    $user = createIdentityUserThroughMutation(
        'Approval Test User',
        'approval.test.'.uniqid('', true).'@example.com',
    );

    return createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-AP-'.substr(uniqid('', true), -6),
        firstName: 'Approval',
        lastName: 'Tester',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

function createSubmittedPersonalRequest(): Request
{
    $employee = createActiveEmployeeForApprovalTest();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    return asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));
}

it('approves through four stages to approved with append-only approval rows (BT-R03)', function (): void {
    Event::fake([RequestApprovalRecorded::class, RequestApproved::class]);

    $submitted = createSubmittedPersonalRequest();
    expect($submitted->status)->toBe(PendingDepartmentManagerState::$name);

    $request = approveRequestStageForTest($submitted);
    expect($request->status)->toBe(PendingHRState::$name);

    $request = approveRequestStageForTest($request);
    expect($request->status)->toBe(PendingDormitoryManagerState::$name);

    $request = approveRequestStageForTest($request);
    expect($request->status)->toBe(PendingDormitoryUnitState::$name);

    $request = approveRequestStageForTest($request);
    expect($request->status)->toBe(ApprovedState::$name);

    expect(app(RequestApprovalRepositoryContract::class)->countForRequest($request->requireId()))->toBe(4);

    $rows = RequestApprovalModel::query()
        ->where('request_id', $request->requireId()->value)
        ->orderBy('decided_at')
        ->get();

    expect($rows)->toHaveCount(4);
    expect($rows->pluck('decision')->unique()->all())->toBe([ApprovalDecision::Approved]);

    Event::assertDispatched(RequestApprovalRecorded::class, 4);
    Event::assertDispatched(RequestApproved::class, function (RequestApproved $event) use ($request): bool {
        return $event->aggregateId === $request->requireId()->value;
    });
});

it('rejects at pending hr with required reason (BT-R04)', function (): void {
    Event::fake([RequestApprovalRecorded::class, RequestRejected::class]);

    $submitted = createSubmittedPersonalRequest();
    $afterDept = approveRequestStageForTest($submitted);
    expect($afterDept->status)->toBe(PendingHRState::$name);

    $reason = 'Insufficient documentation for HR review.';
    $rejected = rejectRequestStageForTest($afterDept, $reason);

    expect($rejected->status)->toBe(RejectedState::$name);
    expect($rejected->rejectionReason)->toBe($reason);

    $rows = RequestApprovalModel::query()
        ->where('request_id', $rejected->requireId()->value)
        ->get();

    expect($rows)->toHaveCount(2);

    $lastApproval = $rows->last();
    if (! $lastApproval instanceof RequestApprovalModel) {
        throw new UnexpectedValueException('Expected rejection approval row.');
    }

    expect($lastApproval->decision)->toBe(ApprovalDecision::Rejected);
    expect($lastApproval->reason)->toBe($reason);

    Event::assertDispatched(RequestRejected::class, function (RequestRejected $event) use ($rejected, $reason): bool {
        return $event->aggregateId === $rejected->requireId()->value
            && ($event->payload['reason'] ?? null) === $reason;
    });
});

it('requires a non-empty rejection reason', function (): void {
    $submitted = createSubmittedPersonalRequest();

    expect(fn () => asMutationApprover(
        fn (ApproverReferenceId $approverId) => app(RejectRequestAction::class)->execute(
            $submitted->requireId(),
            $approverId,
            '   ',
        ),
    ))->toThrow(RequestValidationException::class);
});

it('blocks updates to approval records (R-08)', function (): void {
    $submitted = createSubmittedPersonalRequest();
    approveRequestStageForTest($submitted);

    $approval = RequestApprovalModel::query()->firstOrFail();

    expect(fn () => $approval->update(['reason' => 'tampered']))
        ->toThrow(AppendOnlyViolationException::class);
});
