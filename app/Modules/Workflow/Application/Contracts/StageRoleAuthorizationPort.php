<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\Contracts;

/** Identity role check port — Spatie/Identity adapter in later WP; policy role strings from OD-2. */
interface StageRoleAuthorizationPort
{
    public function identityHasPolicyRole(string $identityUserId, string $policyRole): bool;
}
