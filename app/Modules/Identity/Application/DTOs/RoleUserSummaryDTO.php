<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\DTOs;

final readonly class RoleUserSummaryDTO
{
    public function __construct(
        public string $id,
        public string $displayName,
        public ?string $email,
        public string $status,
    ) {}
}
