<?php

declare(strict_types=1);

use App\Modules\Identity\Application\Contracts\UserRepositoryContract;
use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Identity\Domain\Enums\UserStatus;

it('creates and disables a user through the application layer', function (): void {
    $created = createIdentityUserThroughMutation('Lifecycle User', 'lifecycle@example.com');

    expect($created->isActive())->toBeTrue();

    $deactivated = deactivateUserThroughMutation($created->requireId());

    expect($deactivated->status)->toBe(UserStatus::Disabled);

    $loaded = app(UserRepositoryContract::class)->findById($created->requireId());

    expect($loaded)->not->toBeNull();
    assert($loaded instanceof User);
    expect($loaded->isActive())->toBeFalse();
});
