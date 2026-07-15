<?php

declare(strict_types=1);

namespace App\Modules\Identity\Domain\Exceptions;

use App\Support\Exceptions\DomainException;

final class ProtectedRoleException extends DomainException {}
