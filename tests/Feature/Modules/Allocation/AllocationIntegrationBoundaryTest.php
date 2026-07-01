<?php

declare(strict_types=1);

use App\Modules\Allocation\Application\Contracts\AllocationReadContract;
use App\Modules\Allocation\Application\Contracts\Ports\PhysicalStateSignalPort;
use App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction;
use App\Modules\Allocation\Domain\Events\AllocationAssigned;
use App\Modules\Allocation\Infrastructure\Adapters\AllocationPhysicalStateAdapter;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-01 12:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('round-trips request read assign dormitory signal and read contract', function (): void {
    Event::fake([AllocationAssigned::class]);

    $user = app(CreateUserAction::class)->execute(
        'Integration Boundary User',
        'integration.boundary.'.uniqid('', true).'@example.com',
    );

    $employee = app(CreateEmployeeAction::class)->execute(
        identityId: IdentityUserId::fromString($user->requireId()->value),
        employeeCode: 'EMP-INT-'.substr(uniqid('', true), -6),
        firstName: 'Integration',
        lastName: 'Boundary',
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

    $port = Mockery::mock(PhysicalStateSignalPort::class);
    $port->shouldReceive('reserveBed')->once();
    $port->shouldReceive('occupyBed')->once();

    app()->instance(PhysicalStateSignalPort::class, $port);
    app()->forgetInstance(AllocationPhysicalStateAdapter::class);
    app()->forgetInstance(CreateAllocationFromRequestAction::class);

    $allocation = app(CreateAllocationFromRequestAction::class)->execute(
        requestId: $request->requireId()->value,
        bedId: $bedId,
    );

    Event::assertDispatched(AllocationAssigned::class);

    $read = app(AllocationReadContract::class);

    expect($read->hasActiveAllocation($employee->requireId()->value))->toBeTrue();

    $summary = $read->getAllocationSummary($allocation->requireId()->value);

    expect($summary)->not->toBeNull();
    expect($summary['allocationId'])->toBe($allocation->requireId()->value);
});
