<?php

declare(strict_types=1);

namespace App\Integrations\Reporting;

use App\Modules\Audit\Application\Contracts\AuditPermissionReadPort;
use App\Modules\Reporting\Application\Contracts\Ports\ReportingArchiveVisibilityPort;

/**
 * Reporting archive-visibility bridge via Audit permission port.
 *
 * Lives in app/Integrations per integration-layer-policy — not in Reporting Infrastructure.
 */
final class ReportingArchiveVisibilityBridge implements ReportingArchiveVisibilityPort
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
