<?php

declare(strict_types=1);

namespace App\Integrations\CheckIn;

use App\Modules\Allocation\Application\Contracts\AllocationReadContract;
use App\Modules\CheckIn\Application\Contracts\RequestStayLifecycleCommandPort;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Events\RequestCheckedIn;
use App\Modules\Request\Domain\Events\RequestCheckedOut;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\States\CheckedInState;
use App\Modules\Request\Domain\States\CheckedOutState;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use Illuminate\Support\Facades\Event;

/**
 * CheckIn → Request stay lifecycle bridge (DEBT-W3-01 / OA-05-03 / CD-010).
 *
 * Uses Request domain mutators + repository save (not Spatie transitionTo).
 */
final class RequestStayLifecycleCommandBridge implements RequestStayLifecycleCommandPort
{
    public function __construct(
        private readonly AllocationReadContract $allocations,
        private readonly RequestRepositoryContract $requests,
    ) {}

    public function markCheckedInForAllocation(string $allocationId): void
    {
        $requestId = $this->sourceRequestId($allocationId);

        if ($requestId === null) {
            return;
        }

        $request = $this->requireRequest($requestId);

        if ($request->status === CheckedInState::$name) {
            return;
        }

        $advanced = $this->requests->save($request->markCheckedIn());
        Event::dispatch(RequestCheckedIn::forRequest($advanced->requireId()->value));
    }

    public function markCheckedOutForAllocation(string $allocationId): void
    {
        $requestId = $this->sourceRequestId($allocationId);

        if ($requestId === null) {
            return;
        }

        $request = $this->requireRequest($requestId);

        if ($request->status === CheckedOutState::$name) {
            return;
        }

        $advanced = $this->requests->save($request->markCheckedOut());
        Event::dispatch(RequestCheckedOut::forRequest($advanced->requireId()->value));
    }

    private function sourceRequestId(string $allocationId): ?string
    {
        $detail = $this->allocations->getAllocationDetail($allocationId);
        $sourceRequestId = $detail['sourceRequestId'] ?? null;

        return is_string($sourceRequestId) && $sourceRequestId !== ''
            ? $sourceRequestId
            : null;
    }

    private function requireRequest(string $requestId): \App\Modules\Request\Domain\Entities\Request
    {
        $request = $this->requests->findById(RequestId::fromString($requestId));

        if ($request === null) {
            throw new RequestNotFoundException('Request not found for stay lifecycle.');
        }

        return $request;
    }
}
