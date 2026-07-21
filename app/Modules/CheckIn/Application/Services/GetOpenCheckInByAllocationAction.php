<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Services;

use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Domain\Models\CheckInRecord;

/**
 * Read open check-in for an allocation (WP-CHECKIN-01).
 * Presentation depends on this Action, not the repository contract.
 */
final class GetOpenCheckInByAllocationAction
{
    public function __construct(
        private readonly CheckInRecordRepositoryContract $records,
    ) {}

    public function execute(string $allocationId): ?CheckInRecord
    {
        return $this->records->findOpenByAllocationId($allocationId);
    }
}
