<?php

declare(strict_types=1);

use App\Application\Auth\GetCurrentAuthUserAction;
use App\Models\User;
use App\Providers\AuthFoundationServiceProvider;

beforeEach(function (): void {
    $this->app->register(AuthFoundationServiceProvider::class);
});

it('returns auth user data when authenticated', function (): void {
    $user = User::factory()->create(['email' => 'current@example.com']);
    $this->actingAs($user);

    $current = app(GetCurrentAuthUserAction::class)->execute();

    expect($current)->not->toBeNull()
        ->and($current?->id)->toBe($user->id)
        ->and($current?->identifier)->toBe('current@example.com');
});

it('returns null for guest', function (): void {
    expect(app(GetCurrentAuthUserAction::class)->execute())->toBeNull();
});
