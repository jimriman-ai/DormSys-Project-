<?php

declare(strict_types=1);

namespace App\Integrations\Request;

use App\Modules\Employee\Application\Contracts\Ports\PendingRequestReadPort;
use App\Modules\Request\Application\Contracts\Internal\PendingRequestQueryPort;

final class PendingRequestReadBridge implements PendingRequestReadPort
{
    public function __construct(
        private readonly PendingRequestQueryPort $queries,
    ) {}

    public function hasPendingRequest(string $employeeId, ?string $excludingRequestId = null): bool
    {
        return $this->queries->hasNonTerminalRequest($employeeId, $excludingRequestId);
    }
}
