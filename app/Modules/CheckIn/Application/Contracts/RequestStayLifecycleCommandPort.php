<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Contracts;

/**
 * CheckIn → Request OA-05-03 stay lifecycle (DEBT-W3-01).
 *
 * Advances Request product-visible status when allocation is request-sourced.
 * No-op when allocation has no source_request_id (manual / non-request paths).
 */
interface RequestStayLifecycleCommandPort
{
    public function markCheckedInForAllocation(string $allocationId): void;

    public function markCheckedOutForAllocation(string $allocationId): void;
}
