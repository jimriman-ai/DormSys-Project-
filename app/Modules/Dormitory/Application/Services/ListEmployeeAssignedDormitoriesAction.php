<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Services;

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadRepositoryContract;
use App\Modules\Dormitory\Application\DTOs\DormitorySummaryData;

/**
 * Assignment-scoped dormitory list for employee UI (WP-DORM-UI-READ).
 */
final class ListEmployeeAssignedDormitoriesAction
{
    public function __construct(
        private readonly DormitoryStructureReadRepositoryContract $reads,
    ) {}

    /**
     * @return list<DormitorySummaryData>
     */
    public function execute(string $identityUserId): array
    {
        return $this->reads->listAssignedDormitoriesForUser($identityUserId);
    }
}
