<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Exceptions;

use RuntimeException;

final class PhysicalStateSignalRejectedException extends RuntimeException
{
    public function __construct(
        public readonly string $rejectionCode,
        string $message,
    ) {
        parent::__construct($message);
    }
}
