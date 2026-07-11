<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Registry\PendingMutationAuthorizationRegistry;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Request\Application\Services\ApproveRequestStageAction;
use App\Modules\Request\Application\Services\CancelRequestAction;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\RejectRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
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
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createEmployeeForRequestMutationTest(string $nationalCode = '0499370899'): Employee
{
    $user = createIdentityUserThroughMutation(
        'Request Mutation Test User',
        'request.mutation.'.uniqid('', true).'@example.com',
    );

    return createEmployeeThroughMutation(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-RM-'.substr(uniqid('', true), -6),
        firstName: 'Mutation',
        lastName: 'Tester',
        nationalCode: NationalCode::fromString($nationalCode),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );
}

it('denies submit without a mutation principal', function (): void {
    $employee = createEmployeeForRequestMutationTest();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    expect(fn () => app(SubmitRequestAction::class)->execute($draft->requireId()))
        ->toThrow(UnauthorizedMutationException::class);
});

it('denies submit when principal does not own the request', function (): void {
    $owner = createEmployeeForRequestMutationTest();
    $other = createEmployeeForRequestMutationTest('0000000019');

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($owner->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    expect(fn () => asRequestOwner($other, fn () => app(SubmitRequestAction::class)->execute($draft->requireId())))
        ->toThrow(UnauthorizedMutationException::class, 'Mutation actor must own the request.');
});

it('allows submit when principal owns the request', function (): void {
    $employee = createEmployeeForRequestMutationTest();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $submitted = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));

    expect($submitted->status)->toBe(PendingDepartmentManagerState::$name);
});

it('denies approve when approver does not match mutation actor', function (): void {
    $employee = createEmployeeForRequestMutationTest();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $submitted = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));
    $approverId = ApproverReferenceId::fromString(UuidGenerator::uuid7());

    expect(fn () => asRequestOwner($employee, fn () => app(ApproveRequestStageAction::class)->execute(
        $submitted->requireId(),
        $approverId,
    )))->toThrow(UnauthorizedMutationException::class, 'Approver must match the mutation actor.');
});

it('allows approve when approver matches mutation actor', function (): void {
    $employee = createEmployeeForRequestMutationTest();
    $approverIdentityId = UuidGenerator::uuid7();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $submitted = asRequestOwner($employee, fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));

    $approved = asRequestMutationPrincipal($approverIdentityId, fn () => app(ApproveRequestStageAction::class)->execute(
        $submitted->requireId(),
        ApproverReferenceId::fromString($approverIdentityId),
    ));

    expect($approved->status)->not->toBe(PendingDepartmentManagerState::$name);
});

it('denies cancel when principal does not own the request', function (): void {
    $owner = createEmployeeForRequestMutationTest();
    $other = createEmployeeForRequestMutationTest('0000000019');

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($owner->requireId()->value),
        dormitoryId: DormitorySiteId::fromString(createDormitorySiteForRequestTests()),
        checkInDate: new DateTimeImmutable('2026-07-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    expect(fn () => asRequestOwner($other, fn () => app(CancelRequestAction::class)->execute($draft->requireId())))
        ->toThrow(UnauthorizedMutationException::class, 'Mutation actor must own the request.');
});

it('registers request workflow actions as enforced rather than pending', function (): void {
    expect(PendingMutationAuthorizationRegistry::isPending(SubmitRequestAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(ApproveRequestStageAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(CancelRequestAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(RejectRequestAction::class))->toBeFalse();
});

it('registers request workflow capability keys', function (): void {
    expect(MutationCapabilityCatalog::registeredKeys())->toContain(
        MutationCapabilityCatalog::REQUEST_SUBMIT_OWN,
        MutationCapabilityCatalog::REQUEST_CANCEL_OWN,
        MutationCapabilityCatalog::REQUEST_APPROVE,
        MutationCapabilityCatalog::REQUEST_REJECT,
    );
});
