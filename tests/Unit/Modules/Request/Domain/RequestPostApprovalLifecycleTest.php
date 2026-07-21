<?php

declare(strict_types=1);

use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\States\AllocatedState;
use App\Modules\Request\Domain\States\AllocationFailedState;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CheckedInState;
use App\Modules\Request\Domain\States\CheckedOutState;
use App\Modules\Request\Domain\States\WaitingForAllocationState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestCode;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;

function oa0503SampleRequest(string $status): Request
{
    return new Request(
        id: RequestId::fromString(UuidGenerator::uuid7()),
        code: RequestCode::fromString('REQ-20260721-0001'),
        employeeId: EmployeeReferenceId::fromString(UuidGenerator::uuid7()),
        dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
        type: RequestType::Personal,
        checkInDate: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        checkOutDate: new DateTimeImmutable('2026-12-31', new DateTimeZone('UTC')),
        status: $status,
    );
}

it('advances approved through oa-05-03 operational chain', function (): void {
    $approved = oa0503SampleRequest(ApprovedState::$name);

    $waiting = $approved->markWaitingForAllocation();
    expect($waiting->status)->toBe(WaitingForAllocationState::$name);
    expect($waiting->isTerminal())->toBeFalse();

    $allocated = $waiting->markAllocated();
    expect($allocated->status)->toBe(AllocatedState::$name);

    $checkedIn = $allocated->markCheckedIn();
    expect($checkedIn->status)->toBe(CheckedInState::$name);

    $checkedOut = $checkedIn->markCheckedOut();
    expect($checkedOut->status)->toBe(CheckedOutState::$name);
    expect($checkedOut->isTerminal())->toBeTrue();
});

it('allows allocation_failed from waiting or allocated', function (): void {
    $fromWaiting = oa0503SampleRequest(WaitingForAllocationState::$name)
        ->markAllocationFailed('no capacity');
    expect($fromWaiting->status)->toBe(AllocationFailedState::$name);
    expect($fromWaiting->isTerminal())->toBeTrue();
    expect($fromWaiting->rejectionReason)->toBe('no capacity');

    $fromAllocated = oa0503SampleRequest(AllocatedState::$name)
        ->markAllocationFailed('bed withdrawn');
    expect($fromAllocated->status)->toBe(AllocationFailedState::$name);
});

it('rejects invalid oa-05-03 transitions', function (): void {
    $approved = oa0503SampleRequest(ApprovedState::$name);

    expect(fn () => $approved->markAllocated())
        ->toThrow(InvalidRequestTransitionException::class);

    expect(fn () => $approved->markCheckedIn())
        ->toThrow(InvalidRequestTransitionException::class);
});
