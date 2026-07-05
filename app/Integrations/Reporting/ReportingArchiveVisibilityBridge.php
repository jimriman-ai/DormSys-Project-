<?php

declare(strict_types=1);

namespace App\Integrations\Reporting;

use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Reporting\Application\Contracts\Ports\ReportingArchiveVisibilityPort;
use Database\Seeders\IdentityRoleSeeder;

final class ReportingArchiveVisibilityBridge implements ReportingArchiveVisibilityPort
{
    public function __construct(
        private readonly IdentityUserReadContract $identityUsers,
    ) {}

    public function canRequestArchivedVisibility(?string $principalId): bool
    {
        if ($principalId === null || $principalId === '') {
            return false;
        }

        return $this->identityUsers->userHasRole($principalId, IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    }
}
