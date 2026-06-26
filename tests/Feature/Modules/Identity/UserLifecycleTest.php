<?php

declare(strict_types=1);

use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Domain\Enums\UserStatus;

it('creates and disables a user through the application layer', function (): void {
    $created = app(CreateUserAction::class)->execute('Lifecycle User', 'lifecycle@example.com');

    expect($created->isActive())->toBeTrue();

    $deactivated = app(DeactivateUserAction::class)->execute($created->id);

    expect($deactivated->status)->toBe(UserStatus::Disabled);

    $loaded = app(UserRepositoryContract::class)->findById($created->id);

    expect($loaded)->not->toBeNull()
        ->and($loaded->isActive())->toBeFalse();
});
