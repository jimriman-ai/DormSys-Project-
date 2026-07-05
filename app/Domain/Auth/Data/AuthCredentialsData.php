<?php

declare(strict_types=1);

namespace App\Domain\Auth\Data;

final readonly class AuthCredentialsData
{
    public function __construct(
        public string $identifier,
        public string $password,
    ) {}
}
