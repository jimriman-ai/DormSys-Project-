<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Services;

use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Modules\Allocation\Application\Contracts\Ports\ApprovedRequestReadPort;
use App\Modules\Allocation\Application\Contracts\RequestLifecycleCommandPort;
use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Allocation\Domain\Models\Allocation;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;

final class CreateAllocationFromRequestAction
{
    public function __construct(
        private readonly ApprovedRequestReadPort $requests,
        private readonly CreateAllocationAction $createAllocation,
        private readonly RequestLifecycleCommandPort $requestLifecycle,
        private readonly MutationPolicyEnforcementPoint $mutationPolicy,
        private readonly AllocationMutationAuthorizationGate $allocationMutationAuth,
    ) {}

    public function execute(string $requestId, ?string $bedId = null): Allocation
    {
        $this->mutationPolicy->enforce(MutationCapabilityCatalog::ALLOCATION_CREATE_FROM_REQUEST, [
            'requestId' => $requestId,
        ]);
        $this->allocationMutationAuth->assertCreateFromRequest();

        $summary = $this->requests->getApprovedSummary($requestId);

        if ($summary === null) {
            throw new ValidationException('Only approved accommodation requests can be allocated.');
        }

        $allocation = $this->createAllocation->execute(
            personId: $summary->employeeId,
            bedId: $bedId ?? $summary->dormitoryId,
            start: new DateTimeImmutable($summary->checkInDate),
            end: new DateTimeImmutable($summary->checkOutDate),
            method: AllocationMethod::RequestSourced,
            sourceRequestId: $summary->id,
        );

        $this->requestLifecycle->markAllocated(
            $summary->id,
            $allocation->requireId()->value,
        );

        return $allocation;
    }
}
