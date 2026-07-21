<?php

declare(strict_types=1);

use App\Application\Auth\GetCurrentAuthUserAction;
use App\Application\Auth\LoginUserAction;
use App\Domain\Auth\Data\AuthCredentialsData;
use App\Models\User;
use App\Providers\AuthFoundationServiceProvider;

beforeEach(function (): void {
    $this->app->register(AuthFoundationServiceProvider::class);
});

it('authenticates a user with valid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'auth-test@example.com',
        'password' => 'secret-password',
    ]);

    $result = app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'auth-test@example.com',
        password: 'secret-password',
    ));

    expect($result->success)->toBeTrue()
        ->and($result->failureReason)->toBeNull()
        ->and($result->user)->not->toBeNull()
        ->and($result->user?->id)->toBe($user->id)
        ->and($result->user?->identifier)->toBe('auth-test@example.com')
        ->and(auth('web')->check())->toBeTrue();
});

it('fails login with invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'auth-test@example.com',
        'password' => 'secret-password',
    ]);

    $result = app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'auth-test@example.com',
        password: 'wrong-password',
    ));

    expect($result->success)->toBeFalse()
        ->and($result->failureReason)->toBe('invalid_credentials')
        ->and($result->user)->toBeNull();
});

it('does not authenticate the user when login fails', function (): void {
    User::factory()->create([
        'email' => 'auth-test@example.com',
        'password' => 'secret-password',
    ]);

    app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'auth-test@example.com',
        password: 'wrong-password',
    ));

    expect(auth('web')->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();
});
