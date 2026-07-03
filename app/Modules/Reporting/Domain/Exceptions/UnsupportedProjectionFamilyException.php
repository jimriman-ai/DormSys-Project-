<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Domain\Exceptions;

use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use RuntimeException;

final class UnsupportedProjectionFamilyException extends RuntimeException
{
    public static function forFamily(ProjectionFamily $projectionFamily): self
    {
        return new self("No materializer registered for projection family {$projectionFamily->value}.");
    }
}
