<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Contracts\Ports;

/**
 * Allocation consumer port for spec04 DormitoryReadContract (UD-07 stub until live supplier).
 */
interface DormitoryReadPort
{
    public function bedExists(string $bedId): bool;

    public function isBedAssignable(string $bedId): bool;
}
