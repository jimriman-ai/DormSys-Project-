<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

use App\Modules\Request\Application\DTOs\DormitorySiteSummaryDTO;

interface DormitoryReadContract
{
    public function siteExists(string $dormitorySiteId): bool;

    /**
     * @return list<DormitorySiteSummaryDTO>
     */
    public function listSites(): array;

    /**
     * Active-assignment-scoped dormitory sites for an identity user (WP-REQ-04 / D-G03-FORM).
     *
     * @return list<DormitorySiteSummaryDTO>
     */
    public function listAssignedSitesForUser(string $identityUserId): array;
}
