<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Exceptions;

use DomainException;

final class DependentNotFoundException extends DomainException {}
