<?php

declare(strict_types=1);

namespace App\Modules\Employee\Domain\Exceptions;

use App\Support\Exceptions\NotFoundException;

final class DepartmentNotFoundException extends NotFoundException {}
