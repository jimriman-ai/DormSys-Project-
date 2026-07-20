<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Policies;

use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryAssignment;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;

/**
 * Q-EMP-DORM Option B / WP-DASH-G02-R1 — assignment-based dormitory access.
 *
 * No role-based bypass. Access requires an active dormitory_assignments row
 * (revoked_at IS NULL) for the identity user.
 */
final class DormitoryPolicy
{
    /**
     * Whether the identity user may list dormitories (has ≥1 active assignment).
     */
    public function viewAny(UserModel $user): bool
    {
        return DormitoryAssignment::query()
            ->active()
            ->where('user_id', $user->getId())
            ->exists();
    }

    /**
     * Whether the identity user may view this dormitory (active assignment for it).
     */
    public function view(UserModel $user, DormitoryModel $dormitory): bool
    {
        return DormitoryAssignment::query()
            ->active()
            ->where('user_id', $user->getId())
            ->where('dormitory_id', $dormitory->getId())
            ->exists();
    }
}
