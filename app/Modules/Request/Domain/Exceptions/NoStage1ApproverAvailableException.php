<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Exceptions;

use DomainException;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Fail-closed when no active Dormitory Manager can be snapshotted.
 */
final class NoStage1ApproverAvailableException extends DomainException
{
    public function __construct(string $message = 'No active Stage-1 Dormitory Manager approver is available.')
    {
        parent::__construct($message);
    }
}
