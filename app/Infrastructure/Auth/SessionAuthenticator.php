<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Contracts\AuthenticatesUsers;
use App\Domain\Auth\Data\AuthCredentialsData;
use App\Domain\Auth\Data\AuthResult;
use App\Domain\Auth\Data\AuthUserData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Auth Foundation credential login (LoginUserAction).
 *
 * WAVE1-AUTH-ALLOWLIST (HD-W1-Q1 Option B / Wave 0 findings A1–A3):
 * Must use default/web Auth facade + {@see User} — identity guard's UserModel
 * rejects password auth ({@see \App\Modules\Identity\Infrastructure\Persistence\Models\UserModel::getAuthPassword}).
 * Do not replace with auth('identity') until a Lead HD unifies credential login onto identity.
 * Allowlist paths: Auth::attempt, Auth::user, Auth::logout in this class only.
 */
class SessionAuthenticator implements AuthenticatesUsers
{
    public function login(AuthCredentialsData $credentials): AuthResult
    {
        // WAVE1-AUTH-ALLOWLIST: default/web credential attempt (see class docblock).
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

        // WAVE1-AUTH-ALLOWLIST: paired with Auth::attempt on the same default guard.
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
        // WAVE1-AUTH-ALLOWLIST: logout the same default/web session established by login().
        Auth::logout();
    }
}
