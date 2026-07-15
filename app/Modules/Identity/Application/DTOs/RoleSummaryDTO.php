<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\DTOs;

final readonly class RoleSummaryDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $guardName,
        public int $usersCount,
    ) {}
}
