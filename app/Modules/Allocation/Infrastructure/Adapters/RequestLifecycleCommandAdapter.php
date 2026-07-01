<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Adapters;

use App\Modules\Allocation\Application\Contracts\RequestLifecycleCommandPort;

final class RequestLifecycleCommandAdapter implements RequestLifecycleCommandPort
{
    public function markWaitingForAllocation(string $requestId, array $context = []): void {}

    public function markAllocated(string $requestId, string $allocationId, array $context = []): void {}

    public function markAllocationFailed(string $requestId, string $reason, array $context = []): void {}
}
