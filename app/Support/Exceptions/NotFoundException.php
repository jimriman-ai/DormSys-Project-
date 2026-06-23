<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

/**
 * Thrown when a requested aggregate or resource cannot be located.
 */
class NotFoundException extends DomainException {}
