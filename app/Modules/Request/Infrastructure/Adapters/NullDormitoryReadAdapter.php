<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Adapters;

use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use Ramsey\Uuid\Uuid;

final class NullDormitoryReadAdapter implements DormitoryReadContract
{
    public function siteExists(string $dormitorySiteId): bool
    {
        return Uuid::isValid($dormitorySiteId);
    }
}
