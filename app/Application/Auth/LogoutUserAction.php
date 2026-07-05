<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Auth\Contracts\AuthenticatesUsers;

class LogoutUserAction
{
    public function __construct(
        private readonly AuthenticatesUsers $authenticator,
    ) {}

    public function execute(): void
    {
        $this->authenticator->logout();
    }
}
