<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Exceptions;

use App\Support\Exceptions\DomainException;

final class DuplicateDepartmentCodeException extends DomainException {}
