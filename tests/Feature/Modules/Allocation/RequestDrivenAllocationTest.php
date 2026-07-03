<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction;
use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Allocation\Domain\Enums\AllocationStatus;
use App\Modules\Employee\Application\Services\CreateEmployeeAction;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Request\Application\Services\ApproveRequestStageAction;
use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-01 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createApprovedPersonalRequestForAllocationTest(): array
{
    $user = app(CreateUserAction::class)->execute(
        'Allocation Request User',
        'allocation.request.'.uniqid('', true).'@example.com',
    );

    $employee = app(CreateEmployeeAction::class)->execute(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-ALLOC-'.substr(uniqid('', true), -6),
        firstName: 'Alloc',
        lastName: 'Request',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );

    $dormitoryId = UuidGenerator::uuid7();
    $bedId = UuidGenerator::uuid7();

    $draft = app(CreatePersonalRequestAction::class)->execute(
        employeeId: EmployeeReferenceId::fromString($employee->requireId()->value),
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        checkInDate: new DateTimeImmutable('2026-08-01'),
        checkOutDate: new DateTimeImmutable('2026-12-31'),
    );

    $request = app(SubmitRequestAction::class)->execute($draft->requireId());

    foreach (range(1, 4) as $_) {
        $request = app(ApproveRequestStageAction::class)->execute(
            $request->requireId(),
            ApproverReferenceId::fromString(UuidGenerator::uuid7()),
        );
    }

    expect($request->status)->toBe(ApprovedState::$name);

    return [$employee, $request, $bedId];
}

it('creates an allocation from an approved request via read contract', function (): void {
    [$employee, $request, $bedId] = createApprovedPersonalRequestForAllocationTest();

    $allocation = app(CreateAllocationFromRequestAction::class)->execute(
        requestId: $request->requireId()->value,
        bedId: $bedId,
    );

    expect($allocation->status)->toBe(AllocationStatus::Active);
    expect($allocation->method)->toBe(AllocationMethod::RequestSourced);
    expect($allocation->personId->value)->toBe($employee->requireId()->value);
    expect($allocation->bedId)->toBe($bedId);
    expect($allocation->sourceRequestId)->toBe($request->requireId()->value);
});
