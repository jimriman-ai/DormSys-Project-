<?php

declare(strict_types=1);

namespace App\Domain\Auth\Contracts;

use App\Domain\Auth\Data\AuthCredentialsData;
use App\Domain\Auth\Data\AuthResult;

interface AuthenticatesUsers
{
    public function login(AuthCredentialsData $credentials): AuthResult;

    public function logout(): void;
}
