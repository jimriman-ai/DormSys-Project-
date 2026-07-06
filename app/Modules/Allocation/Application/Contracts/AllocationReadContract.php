<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts;

interface AllocationReadContract
{
    public function hasActiveAllocation(string $personId): bool;

    /**
     * @return list<array{
     *     allocationId: string,
     *     personId: string,
     *     bedId: string,
     *     status: string,
     *     dateRangeStart: string,
     *     dateRangeEnd: string
     * }>
     */
    public function getActiveAllocationsForPerson(string $personId): array;

    /**
     * @return array{
     *     allocationId: string,
     *     personId: string,
     *     bedId: string,
     *     status: string,
     *     dateRangeStart: string,
     *     dateRangeEnd: string
     * }|null
     */
    public function getAllocationSummary(string $allocationId): ?array;

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
     * }|null
     */
    public function getAllocationDetail(string $allocationId): ?array;
}
