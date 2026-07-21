<?php

declare(strict_types=1);

use App\Application\Auth\GetCurrentAuthUserAction;
use App\Application\Auth\LoginUserAction;
use App\Application\Auth\LogoutUserAction;
use App\Domain\Auth\Data\AuthCredentialsData;
use App\Models\User;
use App\Providers\AuthFoundationServiceProvider;

beforeEach(function (): void {
    $this->app->register(AuthFoundationServiceProvider::class);
});

it('clears current auth state', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    app(LogoutUserAction::class)->execute();

    expect(auth('web')->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();
});

it('clears auth state after login without removing the user record', function (): void {
    $user = User::factory()->create([
        'email' => 'logout-flow@example.com',
        'password' => 'secret-password',
    ]);

    app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'logout-flow@example.com',
        password: 'secret-password',
    ));

    app(LogoutUserAction::class)->execute();

    expect(auth('web')->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull()
        ->and(User::query()->find($user->id))->not->toBeNull();
});
