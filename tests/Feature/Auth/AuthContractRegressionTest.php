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

it('establishes session auth and resolvable current user after successful login', function (): void {
    $user = User::factory()->create([
        'email' => 'regression-login@example.com',
        'password' => 'secret-password',
    ]);

    $result = app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'regression-login@example.com',
        password: 'secret-password',
    ));

    $current = app(GetCurrentAuthUserAction::class)->execute();

    expect($result->success)->toBeTrue()
        ->and($result->failureReason)->toBeNull()
        ->and(auth('web')->check())->toBeTrue()
        ->and($current)->not->toBeNull()
        ->and($current?->id)->toBe($user->id)
        ->and($current?->identifier)->toBe('regression-login@example.com');
});

it('keeps guest state and returns invalid credential failure reason after failed login', function (): void {
    User::factory()->create([
        'email' => 'regression-fail@example.com',
        'password' => 'secret-password',
    ]);

    $result = app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'regression-fail@example.com',
        password: 'wrong-password',
    ));

    expect($result->success)->toBeFalse()
        ->and($result->failureReason)->toBe('invalid_credentials')
        ->and($result->user)->toBeNull()
        ->and(auth('web')->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();
});

it('clears auth state and current user resolution after logout following login', function (): void {
    User::factory()->create([
        'email' => 'regression-logout@example.com',
        'password' => 'secret-password',
    ]);

    app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'regression-logout@example.com',
        password: 'secret-password',
    ));

    app(LogoutUserAction::class)->execute();

    expect(auth('web')->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();
});
