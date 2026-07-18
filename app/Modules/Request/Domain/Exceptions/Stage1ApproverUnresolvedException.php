<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Exceptions;

use DomainException;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Fail-closed when Stage-1 approver cannot be snapshotted at create.
 */
final class Stage1ApproverUnresolvedException extends DomainException
{
    public function __construct(string $message = 'Stage-1 approver identity could not be resolved for the employee.')
    {
        parent::__construct($message);
    }
}
