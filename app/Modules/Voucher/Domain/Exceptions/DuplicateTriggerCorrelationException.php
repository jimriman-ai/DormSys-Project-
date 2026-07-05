<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Domain\Exceptions;

use RuntimeException;

final class DuplicateTriggerCorrelationException extends RuntimeException {}
