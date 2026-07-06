<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Services;

use App\Modules\CheckIn\Application\Contracts\CheckInCommandPort;

/**
 * Check-in command port implementation.
 *
 * Propagation: caller must establish an explicit user principal via HTTP
 * audit_principal_user_id or MutationPrincipalContext before invocation.
 * This service does not establish or broaden mutation authority.
 */
final class CheckInService implements CheckInCommandPort
{
    public function __construct(
        private readonly CheckInAction $checkIn,
        private readonly CheckOutAction $checkOut,
    ) {}

    public function checkIn(string $allocationId, string $operatorId): void
    {
        $this->checkIn->execute($allocationId, $operatorId);
    }

    public function checkOut(string $allocationId, string $operatorId): void
    {
        $this->checkOut->execute($allocationId, $operatorId);
    }
}
