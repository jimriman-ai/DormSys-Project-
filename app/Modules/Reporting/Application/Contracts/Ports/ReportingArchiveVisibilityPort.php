<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

interface ReportingArchiveVisibilityPort
{
    public function canRequestArchivedVisibility(?string $principalId): bool;
}
