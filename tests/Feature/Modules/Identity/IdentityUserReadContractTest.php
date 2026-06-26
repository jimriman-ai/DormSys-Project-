<?php

declare(strict_types=1);

use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Identity\Application\DTOs\UserSummaryDTO;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Support\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;

it('answers existence and active status per contract', function (): void {
    $contract = app(IdentityUserReadContract::class);

    $created = app(CreateUserAction::class)->execute('Read Contract User', 'read@example.com');
    $userId = UserId::fromString($created->requireId()->value);

    expect($contract->userExists($userId->value))->toBeTrue()
        ->and($contract->isUserActive($userId->value))->toBeTrue();

    $summary = $contract->findUserSummary($userId->value);

    expect($summary)->toBeInstanceOf(UserSummaryDTO::class);
    assert($summary instanceof UserSummaryDTO);
    expect($summary->id)->toBe($created->requireId()->value)
        ->and($summary->status)->toBe('active')
        ->and($summary->displayName)->toBe('Read Contract User');

    app(DeactivateUserAction::class)->execute($userId);

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

it('rejects malformed identifiers at UserId validation', function (): void {
    expect(fn () => UserId::fromString('not-a-uuid'))
        ->toThrow(ValidationException::class);
});
