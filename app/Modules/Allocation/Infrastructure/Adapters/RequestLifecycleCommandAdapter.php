<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Adapters;

use App\Modules\Allocation\Application\Contracts\RequestLifecycleCommandPort;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Events\RequestAllocated;
use App\Modules\Request\Domain\Events\RequestAllocationFailed;
use App\Modules\Request\Domain\Events\RequestWaitingForAllocation;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\WaitingForAllocationState;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

/**
 * Allocation → Request OA-05-03 lifecycle adapter (W3-B).
 *
 * Persists Request status via RequestRepositoryContract (CD-010: Request owns state).
 * Does not use Spatie transitionTo — matches Request Application string+save pattern.
 */
final class RequestLifecycleCommandAdapter implements RequestLifecycleCommandPort
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
    ) {}

    public function markWaitingForAllocation(string $requestId, array $context = []): void
    {
        DB::transaction(function () use ($requestId): void {
            $request = $this->requireRequest($requestId);

            if ($request->status === WaitingForAllocationState::$name) {
                return;
            }

            $advanced = $this->requests->save($request->markWaitingForAllocation());
            Event::dispatch(RequestWaitingForAllocation::forRequest($advanced->requireId()->value));
        });
    }

    public function markAllocated(string $requestId, string $allocationId, array $context = []): void
    {
        DB::transaction(function () use ($requestId, $allocationId): void {
            $request = $this->requireRequest($requestId);

            if ($request->status === ApprovedState::$name) {
                $request = $this->requests->save($request->markWaitingForAllocation());
                Event::dispatch(RequestWaitingForAllocation::forRequest($request->requireId()->value));
            }

            $allocated = $this->requests->save($request->markAllocated());
            Event::dispatch(RequestAllocated::forRequest(
                $allocated->requireId()->value,
                $allocationId,
            ));
        });
    }

    public function markAllocationFailed(string $requestId, string $reason, array $context = []): void
    {
        DB::transaction(function () use ($requestId, $reason): void {
            $request = $this->requireRequest($requestId);
            $failed = $this->requests->save($request->markAllocationFailed($reason));
            Event::dispatch(RequestAllocationFailed::forRequest(
                $failed->requireId()->value,
                $reason,
            ));
        });
    }

    private function requireRequest(string $requestId): Request
    {
        $request = $this->requests->findById(RequestId::fromString($requestId));

        if ($request === null) {
            throw new RequestNotFoundException('Request not found.');
        }

        return $request;
    }
}
