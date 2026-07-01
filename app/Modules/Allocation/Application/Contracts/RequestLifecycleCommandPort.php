<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts;

/**
 * Producer port for Request lifecycle handoff (OA-05-03).
 * Payload fields are provisional — UD-10.
 */
interface RequestLifecycleCommandPort
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function markWaitingForAllocation(string $requestId, array $context = []): void;

    /**
     * @param  array<string, mixed>  $context
     */
    public function markAllocated(string $requestId, string $allocationId, array $context = []): void;

    /**
     * @param  array<string, mixed>  $context
     */
    public function markAllocationFailed(string $requestId, string $reason, array $context = []): void;
}
