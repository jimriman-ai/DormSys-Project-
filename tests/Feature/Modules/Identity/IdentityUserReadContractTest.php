<?php

declare(strict_types=1);

use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Identity\Application\DTOs\UserSummaryDTO;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Support\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Role;

it('answers existence and active status per contract', function (): void {
    $contract = app(IdentityUserReadContract::class);

    $created = createIdentityUserThroughMutation('Read Contract User', 'read@example.com');
    $userId = UserId::fromString($created->requireId()->value);

    expect($contract->userExists($userId->value))->toBeTrue()
        ->and($contract->isUserActive($userId->value))->toBeTrue();

    $summary = $contract->findUserSummary($userId->value);

    expect($summary)->toBeInstanceOf(UserSummaryDTO::class);
    assert($summary instanceof UserSummaryDTO);
    expect($summary->id)->toBe($created->requireId()->value)
        ->and($summary->status)->toBe('active')
        ->and($summary->displayName)->toBe('Read Contract User');

    deactivateUserThroughMutation($userId);

    expect($contract->userExists($userId->value))->toBeTrue()
        ->and($contract->isUserActive($userId->value))->toBeFalse();

    $disabledSummary = $contract->findUserSummary($userId->value);

    expect($disabledSummary?->status)->toBe('disabled');
});

it('returns false and null for unknown identifiers without leaking errors', function (): void {
    $contract = app(IdentityUserReadContract::class);
    $unknownId = UserId::fromString(Uuid::uuid7()->toString());

    expect($contract->userExists($unknownId->value))->toBeFalse()
        ->and($contract->isUserActive($unknownId->value))->toBeFalse()
        ->and($contract->findUserSummary($unknownId->value))->toBeNull();
});

it('reports role membership via userHasRole', function (): void {
    $contract = app(IdentityUserReadContract::class);

    $created = createIdentityUserThroughMutation(
        'Role Check User',
        'role-check-'.uniqid('', true).'@example.com',
    );
    $userId = $created->requireId()->value;

    expect($contract->userHasRole($userId, 'Operator'))->toBeFalse();

    Role::findOrCreate('Operator', config('auth.defaults.guard', 'web'));
    assignRoleThroughMutation($created->requireId(), 'Operator');

    expect($contract->userHasRole($userId, 'Operator'))->toBeTrue();
});

it('returns false for userHasRole when user does not exist', function (): void {
    $contract = app(IdentityUserReadContract::class);
    $unknownId = UserId::fromString(Uuid::uuid7()->toString());

    expect($contract->userHasRole($unknownId->value, 'operator'))->toBeFalse();
});

it('reports permission membership via userHasPermission without throwing when catalog is empty', function (): void {
    $contract = app(IdentityUserReadContract::class);

    $created = createIdentityUserThroughMutation(
        'Permission Check User',
        'permission-check-'.uniqid('', true).'@example.com',
    );
    $userId = $created->requireId()->value;

    expect($contract->userHasPermission($userId, 'audit.read'))->toBeFalse();
});

it('returns false for userHasPermission when user does not exist', function (): void {
    $contract = app(IdentityUserReadContract::class);
    $unknownId = UserId::fromString(Uuid::uuid7()->toString());

    expect($contract->userHasPermission($unknownId->value, 'audit.read'))->toBeFalse();
});

it('rejects malformed identifiers at UserId validation', function (): void {
    expect(fn () => UserId::fromString('not-a-uuid'))
        ->toThrow(ValidationException::class);
});
