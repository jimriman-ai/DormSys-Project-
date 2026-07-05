<?php

declare(strict_types=1);

namespace App\Domain\Auth\Data;

final readonly class AuthUserData
{
    public function __construct(
        public int $id,
        public string $identifier,
    ) {}
}
