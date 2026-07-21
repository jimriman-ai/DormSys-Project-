<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Contracts\ResolvesAuthUser;
use App\Domain\Auth\Data\AuthUserData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves the current Auth Foundation session user (GetCurrentAuthUserAction).
 *
 * WAVE1-AUTH-ALLOWLIST (HD-W1-Q1 Option B — Resolver review):
 * Must stay on the same default/web guard as {@see SessionAuthenticator}.
 * Switching to auth('identity') alone would return null after LoginUserAction
 * (web login ≠ identity session) and break GetCurrentAuthUserAction pairing.
 * Do not migrate until SessionAuthenticator credential path is unified onto identity.
 * Allowlist path: Auth::user in this class only.
 */
class SessionAuthUserResolver implements ResolvesAuthUser
{
    public function current(): ?AuthUserData
    {
        // WAVE1-AUTH-ALLOWLIST: read default/web principal (see class docblock).
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        assert($user instanceof User);

        return new AuthUserData(
            id: $user->id,
            identifier: $user->email,
        );
    }
}
