<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Application-boundary counterpart of Domain WorkflowInstanceNotFoundException.
 * Cross-module consumers must catch this type, not Workflow Domain exceptions.
 */
final class WorkflowInstanceNotFoundException extends RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
