<?php

declare(strict_types=1);

namespace App\Modules\Audit\Domain\Exceptions;

use App\Support\Exceptions\DomainException;

final class AuditDuplicateConflictException extends DomainException {}
