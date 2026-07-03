<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Domain\Exceptions;

use RuntimeException;

final class ProjectionCursorBusyException extends RuntimeException
{
    public static function forCursor(string $cursorId): self
    {
        return new self("Projection cursor {$cursorId} is already running.");
    }
}
