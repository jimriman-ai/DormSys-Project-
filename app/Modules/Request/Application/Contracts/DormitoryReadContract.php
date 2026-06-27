<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

interface DormitoryReadContract
{
    public function siteExists(string $dormitorySiteId): bool;
}
