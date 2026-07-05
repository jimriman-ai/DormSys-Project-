<?php

declare(strict_types=1);

use App\Application\Auth\GetCurrentAuthUserAction;
use App\Application\Auth\LoginUserAction;
use App\Application\Auth\LogoutUserAction;
use App\Domain\Auth\Data\AuthCredentialsData;
use App\Models\User;

it('keeps guest state when logout is invoked while already guest', function (): void {
    expect(auth()->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();

    app(LogoutUserAction::class)->execute();

    expect(auth()->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();
});

it('remains deterministic after repeated logout while guest', function (): void {
    $logout = app(LogoutUserAction::class);

    $logout->execute();
    $logout->execute();
    $logout->execute();

    expect(auth()->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();
});

it('remains deterministic after repeated logout following login', function (): void {
    User::factory()->create([
        'email' => 'edge-repeat-logout@example.com',
        'password' => 'secret-password',
    ]);

    app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'edge-repeat-logout@example.com',
        password: 'secret-password',
    ));

    $logout = app(LogoutUserAction::class);

    $logout->execute();
    $logout->execute();

    expect(auth()->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();
});

it('preserves guest state across repeated failed login attempts', function (): void {
    User::factory()->create([
        'email' => 'edge-fail@example.com',
        'password' => 'secret-password',
    ]);

    $login = app(LoginUserAction::class);
    $currentUser = app(GetCurrentAuthUserAction::class);

    foreach (['wrong-1', 'wrong-2', 'wrong-3'] as $password) {
        $result = $login->execute(new AuthCredentialsData(
            identifier: 'edge-fail@example.com',
            password: $password,
        ));

        expect($result->success)->toBeFalse()
            ->and($result->failureReason)->toBe('invalid_credentials')
            ->and($result->user)->toBeNull()
            ->and(auth()->check())->toBeFalse()
            ->and($currentUser->execute())->toBeNull();
    }
});

it('completes guest to authenticated to guest transition deterministically', function (): void {
    $user = User::factory()->create([
        'email' => 'edge-cycle@example.com',
        'password' => 'secret-password',
    ]);

    expect(auth()->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();

    $loginResult = app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'edge-cycle@example.com',
        password: 'secret-password',
    ));

    expect($loginResult->success)->toBeTrue()
        ->and(auth()->check())->toBeTrue();

    $current = app(GetCurrentAuthUserAction::class)->execute();

    expect($current)->not->toBeNull()
        ->and($current?->id)->toBe($user->id)
        ->and($current?->identifier)->toBe('edge-cycle@example.com');

    app(LogoutUserAction::class)->execute();

    expect(auth()->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();
});

it('resolves current user stably after successful login', function (): void {
    $user = User::factory()->create([
        'email' => 'edge-stable@example.com',
        'password' => 'secret-password',
    ]);

    app(LoginUserAction::class)->execute(new AuthCredentialsData(
        identifier: 'edge-stable@example.com',
        password: 'secret-password',
    ));

    $resolver = app(GetCurrentAuthUserAction::class);

    foreach (range(1, 5) as $_) {
        $current = $resolver->execute();

        expect($current)->not->toBeNull()
            ->and($current?->id)->toBe($user->id)
            ->and($current?->identifier)->toBe('edge-stable@example.com');
    }
});

it('does not leak stale identity across repeated auth transitions', function (): void {
    $userA = User::factory()->create([
        'email' => 'edge-user-a@example.com',
        'password' => 'secret-password',
    ]);

    $userB = User::factory()->create([
        'email' => 'edge-user-b@example.com',
        'password' => 'secret-password',
    ]);

    $login = app(LoginUserAction::class);
    $currentUser = app(GetCurrentAuthUserAction::class);
    $logout = app(LogoutUserAction::class);

    $login->execute(new AuthCredentialsData(
        identifier: 'edge-user-a@example.com',
        password: 'secret-password',
    ));

    expect($currentUser->execute()?->id)->toBe($userA->id);

    $logout->execute();

    expect($currentUser->execute())->toBeNull();

    $login->execute(new AuthCredentialsData(
        identifier: 'edge-user-b@example.com',
        password: 'secret-password',
    ));

    $current = $currentUser->execute();

    expect($current)->not->toBeNull()
        ->and($current?->id)->toBe($userB->id)
        ->and($current?->id)->not->toBe($userA->id)
        ->and($current?->identifier)->toBe('edge-user-b@example.com');

    $logout->execute();

    expect(auth()->check())->toBeFalse()
        ->and($currentUser->execute())->toBeNull();
});
