<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Adapters;

use App\Modules\Allocation\Application\Contracts\Ports\DormitoryReadPort;

final class DormitoryReadAdapter
{
    public function __construct(
        private readonly DormitoryReadPort $dormitory,
    ) {}

    public function bedExists(string $bedId): bool
    {
        return $this->dormitory->bedExists($bedId);
    }

    public function isBedAssignable(string $bedId): bool
    {
        return $this->dormitory->isBedAssignable($bedId);
    }
}
