<?php

declare(strict_types=1);

/**
 * G-REQ-01 — Request transition guardrails (maps T2 cluster A2).
 *
 * Illegal OA-05-03 / lifecycle edges must throw InvalidRequestTransitionException.
 * No Lottery / Reporting / auth:api. Domain-only fixtures (no production changes).
 */

use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\States\AllocatedState;
use App\Modules\Request\Domain\States\AllocationFailedState;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\CheckedInState;
use App\Modules\Request\Domain\States\CheckedOutState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\States\WaitingForAllocationState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestCode;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;

function gReq01SampleRequest(string $status): Request
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

/**
 * @return array<string, array{0: string, 1: callable(Request): mixed, 2: string}>
 */
function gReq01IllegalTransitionCases(): array
{
    return [
        // approved → checked_in without allocation (T2 A2 / OA-05-03)
        'approved→checked_in' => [
            ApprovedState::$name,
            fn (Request $r) => $r->markCheckedIn(),
            'Cannot mark checked_in',
        ],
        'approved→allocated (skip waiting)' => [
            ApprovedState::$name,
            fn (Request $r) => $r->markAllocated(),
            'Cannot mark allocated',
        ],
        'approved→checked_out' => [
            ApprovedState::$name,
            fn (Request $r) => $r->markCheckedOut(),
            'Cannot mark checked_out',
        ],
        'approved→allocation_failed' => [
            ApprovedState::$name,
            fn (Request $r) => $r->markAllocationFailed('n/a'),
            'Cannot mark allocation_failed',
        ],

        // waiting_for_allocation — cannot jump to stay / re-enter waiting
        'waiting→checked_in' => [
            WaitingForAllocationState::$name,
            fn (Request $r) => $r->markCheckedIn(),
            'Cannot mark checked_in',
        ],
        'waiting→checked_out' => [
            WaitingForAllocationState::$name,
            fn (Request $r) => $r->markCheckedOut(),
            'Cannot mark checked_out',
        ],
        'waiting→waiting (double waiting)' => [
            WaitingForAllocationState::$name,
            fn (Request $r) => $r->markWaitingForAllocation(),
            'Cannot mark waiting_for_allocation',
        ],

        // allocated — cannot rewind or skip checkout order
        'allocated→waiting' => [
            AllocatedState::$name,
            fn (Request $r) => $r->markWaitingForAllocation(),
            'Cannot mark waiting_for_allocation',
        ],
        'allocated→allocated (double allocate)' => [
            AllocatedState::$name,
            fn (Request $r) => $r->markAllocated(),
            'Cannot mark allocated',
        ],
        'allocated→checked_out (skip check-in)' => [
            AllocatedState::$name,
            fn (Request $r) => $r->markCheckedOut(),
            'Cannot mark checked_out',
        ],

        // checked_in — terminal stay moves only via checkout
        'checked_in→allocated' => [
            CheckedInState::$name,
            fn (Request $r) => $r->markAllocated(),
            'Cannot mark allocated',
        ],
        'checked_in→checked_in (double check-in)' => [
            CheckedInState::$name,
            fn (Request $r) => $r->markCheckedIn(),
            'Cannot mark checked_in',
        ],
        'checked_in→waiting' => [
            CheckedInState::$name,
            fn (Request $r) => $r->markWaitingForAllocation(),
            'Cannot mark waiting_for_allocation',
        ],
        'checked_in→allocation_failed' => [
            CheckedInState::$name,
            fn (Request $r) => $r->markAllocationFailed('n/a'),
            'Cannot mark allocation_failed',
        ],

        // checked_out / terminals — no operational advance
        'checked_out→checked_in' => [
            CheckedOutState::$name,
            fn (Request $r) => $r->markCheckedIn(),
            'Cannot mark checked_in',
        ],
        'checked_out→checked_out' => [
            CheckedOutState::$name,
            fn (Request $r) => $r->markCheckedOut(),
            'Cannot mark checked_out',
        ],
        'allocation_failed→allocated' => [
            AllocationFailedState::$name,
            fn (Request $r) => $r->markAllocated(),
            'Cannot mark allocated',
        ],
        'rejected→waiting' => [
            RejectedState::$name,
            fn (Request $r) => $r->markWaitingForAllocation(),
            'Cannot mark waiting_for_allocation',
        ],
        'cancelled→checked_in' => [
            CancelledState::$name,
            fn (Request $r) => $r->markCheckedIn(),
            'Cannot mark checked_in',
        ],

        // pre-approval stages cannot enter OA-05-03 chain
        'draft→waiting' => [
            DraftState::$name,
            fn (Request $r) => $r->markWaitingForAllocation(),
            'Cannot mark waiting_for_allocation',
        ],
        'pending_dept→allocated' => [
            PendingDepartmentManagerState::$name,
            fn (Request $r) => $r->markAllocated(),
            'Cannot mark allocated',
        ],
        'pending_hr→checked_in' => [
            PendingHRState::$name,
            fn (Request $r) => $r->markCheckedIn(),
            'Cannot mark checked_in',
        ],
    ];
}

it('throws InvalidRequestTransitionException for each illegal OA-05-03 edge', function (
    string $fromStatus,
    callable $attempt,
    string $messageContains,
): void {
    $request = gReq01SampleRequest($fromStatus);

    expect(fn () => $attempt($request))
        ->toThrow(InvalidRequestTransitionException::class, $messageContains);
})->with(gReq01IllegalTransitionCases());
