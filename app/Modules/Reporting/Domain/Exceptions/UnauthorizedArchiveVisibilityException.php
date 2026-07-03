<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Domain\Exceptions;

use App\Support\Exceptions\DomainException;

final class UnauthorizedArchiveVisibilityException extends DomainException {}
