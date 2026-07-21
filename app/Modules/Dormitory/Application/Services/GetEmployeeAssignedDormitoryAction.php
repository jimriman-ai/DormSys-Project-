<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Services;

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadRepositoryContract;
use App\Modules\Dormitory\Application\DTOs\DormitoryDetailData;

/**
 * Assignment-scoped dormitory detail for employee UI (WP-DORM-UI-READ).
 */
final class GetEmployeeAssignedDormitoryAction
{
    public function __construct(
        private readonly DormitoryStructureReadRepositoryContract $reads,
    ) {}

    public function execute(string $identityUserId, string $dormitoryId): ?DormitoryDetailData
    {
        return $this->reads->findAssignedDormitoryDetailForUser($identityUserId, $dormitoryId);
    }
}
