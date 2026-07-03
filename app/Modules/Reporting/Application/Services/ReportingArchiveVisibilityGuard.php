<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditPrincipalContextPort;
use App\Modules\Reporting\Application\Contracts\Ports\ReportingArchiveVisibilityPort;
use App\Modules\Reporting\Domain\Exceptions\UnauthorizedArchiveVisibilityException;

final class ReportingArchiveVisibilityGuard
{
    public function __construct(
        private readonly AuditPrincipalContextPort $principalContext,
        private readonly ReportingArchiveVisibilityPort $archiveVisibility,
    ) {}

    public function resolveIncludeArchived(bool $requested): bool
    {
        if (! $requested) {
            return false;
        }

        if (! $this->archiveVisibility->canRequestArchivedVisibility($this->principalContext->currentPrincipalId())) {
            throw new UnauthorizedArchiveVisibilityException('Unauthorized archive-inclusive reporting access.');
        }

        return true;
    }
}
