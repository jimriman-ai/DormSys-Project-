<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Policies;

use App\Modules\Dormitory\Domain\Contracts\DormitoryAssignmentReader;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Q-EMP-DORM Option B / WP-DASH-G02-R1 — assignment-based dormitory access.
 *
 * No role-based bypass. Access requires an active dormitory_assignments row
 * (revoked_at IS NULL) for the identity user.
 *
 * Assignment reads go through {@see DormitoryAssignmentReader} (DG-ARCH-01 Option B).
 */
final class DormitoryPolicy
{
    public function __construct(
        private readonly DormitoryAssignmentReader $assignments,
    ) {}

    /**
     * Whether the identity user may list dormitories (has ≥1 active assignment).
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $this->assignments->hasActiveAssignment($user->getAuthIdentifier());
    }

    /**
     * Whether the identity user may view this dormitory (active assignment for it).
     */
    public function view(Authenticatable $user, DormitoryModel $dormitory): bool
    {
        return $this->assignments->hasActiveAssignmentForDormitory(
            $user->getAuthIdentifier(),
            $dormitory->getId(),
        );
    }
}
