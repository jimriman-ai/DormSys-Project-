<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Adapters;

use App\Modules\Allocation\Application\Contracts\Ports\DormitoryReadPort;
use Ramsey\Uuid\Uuid;

final class NullDormitoryReadAdapter implements DormitoryReadPort
{
    public function bedExists(string $bedId): bool
    {
        return Uuid::isValid($bedId);
    }

    public function isBedAssignable(string $bedId): bool
    {
        return $this->bedExists($bedId);
    }
}
