<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Auth\GetCurrentAuthUserAction;
use App\Application\Auth\LoginUserAction;
use App\Application\Auth\LogoutUserAction;
use App\Domain\Auth\Contracts\AuthenticatesUsers;
use App\Domain\Auth\Contracts\ResolvesAuthUser;
use App\Infrastructure\Auth\SessionAuthenticator;
use App\Infrastructure\Auth\SessionAuthUserResolver;
use Illuminate\Support\ServiceProvider;

class AuthFoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuthenticatesUsers::class, SessionAuthenticator::class);
        $this->app->singleton(ResolvesAuthUser::class, SessionAuthUserResolver::class);
        $this->app->singleton(LoginUserAction::class);
        $this->app->singleton(LogoutUserAction::class);
        $this->app->singleton(GetCurrentAuthUserAction::class);
    }
}
