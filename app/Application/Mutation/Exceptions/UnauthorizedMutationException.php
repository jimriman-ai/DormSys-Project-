<?php

declare(strict_types=1);

namespace App\Application\Mutation\Exceptions;

use App\Support\Exceptions\DomainException;

final class UnauthorizedMutationException extends DomainException {}
