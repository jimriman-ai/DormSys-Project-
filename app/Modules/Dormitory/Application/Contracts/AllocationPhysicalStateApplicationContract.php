<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Contracts;

use App\Modules\Dormitory\Application\DTOs\ApplyPhysicalStateSignalCommand;
use App\Modules\Dormitory\Application\DTOs\ApplyPhysicalStateSignalResult;

interface AllocationPhysicalStateApplicationContract
{
    public function apply(ApplyPhysicalStateSignalCommand $command): ApplyPhysicalStateSignalResult;
}
