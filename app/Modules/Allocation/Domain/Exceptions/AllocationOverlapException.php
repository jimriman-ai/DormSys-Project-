<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Domain\Exceptions;

use App\Support\Exceptions\DomainException;

final class AllocationOverlapException extends DomainException {}
