<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Domain\Contracts;

/**
 * Minimal read port for active employee↔dormitory assignments (DG-ARCH-01 / Q-EMP-DORM Option B).
 */
interface DormitoryAssignmentReader
{
    /**
     * Whether the identity user has at least one active assignment (revoked_at IS NULL).
     */
    public function hasActiveAssignment(string $userId): bool;

    /**
     * Whether the identity user has an active assignment for the given dormitory.
     */
    public function hasActiveAssignmentForDormitory(string $userId, string $dormitoryId): bool;
}
