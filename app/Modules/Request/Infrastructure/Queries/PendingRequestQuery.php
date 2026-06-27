<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Queries;

use App\Modules\Request\Application\Contracts\Internal\PendingRequestQueryPort;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;

final class PendingRequestQuery implements PendingRequestQueryPort
{
    public function hasNonTerminalRequest(string $employeeId, ?string $excludingRequestId = null): bool
    {
        return RequestModel::query()
            ->where('employee_id', $employeeId)
            ->when(
                $excludingRequestId !== null,
                static fn ($query) => $query->where('id', '!=', $excludingRequestId),
            )
            ->whereNotIn('status', [
                ApprovedState::$name,
                RejectedState::$name,
                CancelledState::$name,
            ])
            ->exists();
    }
}
