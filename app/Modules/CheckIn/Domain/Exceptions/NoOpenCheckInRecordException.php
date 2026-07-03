<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Domain\Exceptions;

use App\Support\Exceptions\DomainException;

final class NoOpenCheckInRecordException extends DomainException {}
