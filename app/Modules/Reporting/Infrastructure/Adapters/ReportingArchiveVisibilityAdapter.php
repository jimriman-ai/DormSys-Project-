<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Adapters;

use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Reporting\Application\Contracts\Ports\ReportingArchiveVisibilityPort;
use Database\Seeders\IdentityRoleSeeder;

final class ReportingArchiveVisibilityAdapter implements ReportingArchiveVisibilityPort
{
    public function canRequestArchivedVisibility(?string $principalId): bool
    {
        if ($principalId === null || $principalId === '') {
            return false;
        }

        $user = UserModel::query()->find($principalId);

        if ($user === null) {
            return false;
        }

        return $user->hasRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    }
}
