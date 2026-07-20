<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Infrastructure\Persistence;

use App\Modules\Dormitory\Domain\Contracts\DormitoryAssignmentReader;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryAssignment;

/**
 * Eloquent adapter for {@see DormitoryAssignmentReader} (DG-ARCH-01 / WP-DEBT-01).
 */
final class EloquentDormitoryAssignmentReader implements DormitoryAssignmentReader
{
    public function hasActiveAssignment(string $userId): bool
    {
        return DormitoryAssignment::query()
            ->active()
            ->where('user_id', $userId)
            ->exists();
    }

    public function hasActiveAssignmentForDormitory(string $userId, string $dormitoryId): bool
    {
        return DormitoryAssignment::query()
            ->active()
            ->where('user_id', $userId)
            ->where('dormitory_id', $dormitoryId)
            ->exists();
    }
}
