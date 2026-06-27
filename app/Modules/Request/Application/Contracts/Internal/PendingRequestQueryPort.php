<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts\Internal;

interface PendingRequestQueryPort
{
    public function hasNonTerminalRequest(string $employeeId, ?string $excludingRequestId = null): bool;
}
