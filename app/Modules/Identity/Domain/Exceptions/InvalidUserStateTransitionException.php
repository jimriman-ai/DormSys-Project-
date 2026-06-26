<?php

declare(strict_types=1);

namespace App\Modules\Identity\Domain\Exceptions;

use App\Support\Exceptions\DomainException;

class InvalidUserStateTransitionException extends DomainException {}
