<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Contracts\AuthenticatesUsers;
use App\Domain\Auth\Data\AuthCredentialsData;
use App\Domain\Auth\Data\AuthResult;
use App\Domain\Auth\Data\AuthUserData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SessionAuthenticator implements AuthenticatesUsers
{
    public function login(AuthCredentialsData $credentials): AuthResult
    {
        $attempted = Auth::attempt([
            'email' => $credentials->identifier,
            'password' => $credentials->password,
        ]);

        if (! $attempted) {
            return new AuthResult(
                success: false,
                user: null,
                failureReason: 'invalid_credentials',
            );
        }

        $user = Auth::user();
        assert($user instanceof User);

        return new AuthResult(
            success: true,
            user: new AuthUserData(
                id: $user->id,
                identifier: $user->email,
            ),
            failureReason: null,
        );
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
