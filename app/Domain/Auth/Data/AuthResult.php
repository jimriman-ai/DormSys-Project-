<?php

declare(strict_types=1);

namespace App\Domain\Auth\Data;

final readonly class AuthResult
{
    public function __construct(
        public bool $success,
        public ?AuthUserData $user,
        public ?string $failureReason,
    ) {}
}
