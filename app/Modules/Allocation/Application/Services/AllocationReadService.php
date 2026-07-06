<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Services;

use App\Modules\Allocation\Application\Contracts\AllocationReadContract;
use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Domain\Models\Allocation;
use App\Modules\Allocation\Domain\ValueObjects\AllocationId;
use App\Modules\Allocation\Domain\ValueObjects\PersonAllocationRef;

final class AllocationReadService implements AllocationReadContract
{
    public function __construct(
        private readonly AllocationRepositoryContract $allocations,
    ) {}

    public function hasActiveAllocation(string $personId): bool
    {
        return $this->allocations->findActiveByPersonId(
            PersonAllocationRef::fromString($personId),
        ) !== [];
    }

    public function getActiveAllocationsForPerson(string $personId): array
    {
        return array_map(
            fn (Allocation $allocation): array => $this->toSummary($allocation),
            $this->allocations->findActiveByPersonId(
                PersonAllocationRef::fromString($personId),
            ),
        );
    }

    public function getAllocationSummary(string $allocationId): ?array
    {
        $allocation = $this->allocations->findById(AllocationId::fromString($allocationId));

        return $allocation === null ? null : $this->toSummary($allocation);
    }

    public function getAllocationDetail(string $allocationId): ?array
    {
        $allocation = $this->allocations->findById(AllocationId::fromString($allocationId));

        return $allocation === null ? null : $this->toDetail($allocation);
    }

    /**
     * @return array{
     *     allocationId: string,
     *     personId: string,
     *     bedId: string,
     *     status: string,
     *     dateRangeStart: string,
     *     dateRangeEnd: string
     * }
     */
    private function toSummary(Allocation $allocation): array
    {
        return [
            'allocationId' => $allocation->requireId()->value,
            'personId' => $allocation->personId->value,
            'bedId' => $allocation->bedId,
            'status' => $allocation->status->value,
            'dateRangeStart' => $allocation->dateRange->start->format('Y-m-d'),
            'dateRangeEnd' => $allocation->dateRange->end->format('Y-m-d'),
        ];
    }

    /**
     * @return array{
     *     allocationId: string,
     *     personId: string,
     *     bedId: string,
     *     status: string,
     *     method: string,
     *     dateRangeStart: string,
     *     dateRangeEnd: string,
     *     sourceRequestId: string|null,
     *     sourceLotteryResultId: string|null,
     *     releasedAt: string|null,
     *     releaseReason: string|null
     * }
     */
    private function toDetail(Allocation $allocation): array
    {
        return [
            'allocationId' => $allocation->requireId()->value,
            'personId' => $allocation->personId->value,
            'bedId' => $allocation->bedId,
            'status' => $allocation->status->value,
            'method' => $allocation->method->value,
            'dateRangeStart' => $allocation->dateRange->start->format('Y-m-d'),
            'dateRangeEnd' => $allocation->dateRange->end->format('Y-m-d'),
            'sourceRequestId' => $allocation->sourceRequestId,
            'sourceLotteryResultId' => $allocation->sourceLotteryResultId,
            'releasedAt' => $allocation->releasedAt?->format(DATE_ATOM),
            'releaseReason' => $allocation->releaseReason,
        ];
    }
}
