<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\Auth\Contracts\AuthenticatesUsers;
use App\Domain\Auth\Data\AuthCredentialsData;
use App\Domain\Auth\Data\AuthResult;

class LoginUserAction
{
    public function __construct(
        private readonly AuthenticatesUsers $authenticator,
    ) {}

    public function execute(AuthCredentialsData $credentials): AuthResult
    {
        return $this->authenticator->login($credentials);
    }
}
