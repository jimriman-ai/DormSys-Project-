<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Adapters;

use App\Modules\Audit\Application\Contracts\AuditPermissionReadPort;
use App\Modules\Reporting\Application\Contracts\Ports\ReportingArchiveVisibilityPort;

final class ReportingArchiveVisibilityAdapter implements ReportingArchiveVisibilityPort
{
    public function __construct(
        private readonly AuditPermissionReadPort $permissionRead,
    ) {}

    public function canRequestArchivedVisibility(?string $principalId): bool
    {
        if ($principalId === null || $principalId === '') {
            return false;
        }

        return $this->permissionRead->principalHasAuditReadPermission($principalId);
    }
}
