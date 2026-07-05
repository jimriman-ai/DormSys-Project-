<?php

declare(strict_types=1);

use App\Application\Auth\GetCurrentAuthUserAction;
use App\Application\Auth\LogoutUserAction;
use App\Models\User;
use App\Providers\AuthFoundationServiceProvider;

beforeEach(function (): void {
    $this->app->register(AuthFoundationServiceProvider::class);
});

it('clears current auth state', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    app(LogoutUserAction::class)->execute();

    expect(auth()->check())->toBeFalse()
        ->and(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();
});
