<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Services;

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
    ) {}

    public function execute(string $requestId, ?string $bedId = null): Allocation
    {
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
