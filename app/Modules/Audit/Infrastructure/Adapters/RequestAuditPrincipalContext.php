<?php

declare(strict_types=1);

namespace App\Modules\Audit\Infrastructure\Adapters;

use App\Modules\Audit\Application\Contracts\AuditPrincipalContextPort;

final class RequestAuditPrincipalContext implements AuditPrincipalContextPort
{
    public function currentPrincipalId(): ?string
    {
        $userId = request()->attributes->get('audit_principal_user_id');

        if (! is_string($userId) || $userId === '') {
            return null;
        }

        return $userId;
    }
}
