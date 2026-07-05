<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Contracts\ResolvesAuthUser;
use App\Domain\Auth\Data\AuthUserData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SessionAuthUserResolver implements ResolvesAuthUser
{
    public function current(): ?AuthUserData
    {
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
