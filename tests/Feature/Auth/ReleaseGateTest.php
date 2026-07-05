<?php

declare(strict_types=1);

use App\Application\Auth\GetCurrentAuthUserAction;
use App\Application\Auth\LoginUserAction;
use App\Application\Auth\LogoutUserAction;
use App\Domain\Auth\Contracts\AuthenticatesUsers;
use App\Domain\Auth\Contracts\ResolvesAuthUser;
use App\Domain\Auth\Data\AuthCredentialsData;
use App\Models\User;
use App\Providers\AuthFoundationServiceProvider;

/**
 * @return 'STABLE'|'STABLE_WITH_GAPS'|'UNSTABLE'
 */
function evaluateAuthReleaseGate(): string
{
    if (! app()->bound(LoginUserAction::class)
        || ! app()->bound(LogoutUserAction::class)
        || ! app()->bound(GetCurrentAuthUserAction::class)
        || ! app()->bound(AuthenticatesUsers::class)
        || ! app()->bound(ResolvesAuthUser::class)) {
        return 'STABLE_WITH_GAPS';
    }

    $loadedProviders = app()->getLoadedProviders();

    if (! ($loadedProviders[AuthFoundationServiceProvider::class] ?? false)) {
        return 'STABLE_WITH_GAPS';
    }

    $user = User::factory()->create([
        'email' => 'release-gate@example.com',
        'password' => 'secret-password',
    ]);

    $loginResult = app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'release-gate@example.com',
        password: 'secret-password',
    ));

    if (! $loginResult->success
        || $loginResult->failureReason !== null
        || $loginResult->user === null
        || $loginResult->user->id !== $user->id
        || $loginResult->user->identifier !== 'release-gate@example.com'
        || ! auth()->check()) {
        return 'UNSTABLE';
    }

    $current = app(GetCurrentAuthUserAction::class)->execute();

    if ($current === null
        || $current->id !== $user->id
        || $current->identifier !== 'release-gate@example.com') {
        return 'UNSTABLE';
    }

    app(LogoutUserAction::class)->execute();

    if (auth()->check() || app(GetCurrentAuthUserAction::class)->execute() !== null) {
        return 'UNSTABLE';
    }

    $failedLogin = app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'release-gate@example.com',
        password: 'wrong-password',
    ));

    if ($failedLogin->success
        || $failedLogin->failureReason !== 'invalid_credentials'
        || $failedLogin->user !== null
        || auth()->check()) {
        return 'UNSTABLE';
    }

    return 'STABLE';
}

it('loads auth foundation provider in application runtime', function (): void {
    $loadedProviders = app()->getLoadedProviders();

    expect($loadedProviders[AuthFoundationServiceProvider::class] ?? false)->toBeTrue();
});

it('resolves auth actions and contracts from container without manual provider registration', function (): void {
    expect(app()->bound(AuthenticatesUsers::class))->toBeTrue()
        ->and(app()->bound(ResolvesAuthUser::class))->toBeTrue()
        ->and(app(LoginUserAction::class))->toBeInstanceOf(LoginUserAction::class)
        ->and(app(LogoutUserAction::class))->toBeInstanceOf(LogoutUserAction::class)
        ->and(app(GetCurrentAuthUserAction::class))->toBeInstanceOf(GetCurrentAuthUserAction::class);
});

it('classifies auth runtime release gate as stable', function (): void {
    expect(evaluateAuthReleaseGate())->toBe('STABLE');
});
