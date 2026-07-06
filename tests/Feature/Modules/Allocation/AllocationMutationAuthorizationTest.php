<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Registry\PendingMutationAuthorizationRegistry;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\Allocation\Application\Services\CreateAllocationFromRequestAction;
use App\Modules\Allocation\Application\Services\ReleaseAllocationAction;
use App\Modules\Allocation\Domain\Enums\AllocationStatus;
use App\Modules\Allocation\Domain\ValueObjects\AllocationId;
use App\Modules\Allocation\Domain\ValueObjects\PersonAllocationRef;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

function createAllocationMutationActor(): string
{
    return createActiveMutationActorId('Allocation Mutation Actor');
}

it('denies allocation create without a mutation principal', function (): void {
    $personId = UuidGenerator::uuid7();
    $bedId = UuidGenerator::uuid7();

    expect(fn () => app(CreateAllocationAction::class)->execute(
        personId: $personId,
        bedId: $bedId,
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ))->toThrow(UnauthorizedMutationException::class);

    expect(app(AllocationRepositoryContract::class)->findById(AllocationId::fromString(UuidGenerator::uuid7())))->toBeNull();
});

it('allows allocation create with an authorized actor', function (): void {
    $actorId = createAllocationMutationActor();

    $allocation = withAllocationMutationActor(fn () => app(CreateAllocationAction::class)->execute(
        personId: UuidGenerator::uuid7(),
        bedId: UuidGenerator::uuid7(),
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ), $actorId);

    expect($allocation->isActive())->toBeTrue();
});

it('denies allocation create when principal is inactive', function (): void {
    $actorId = createAllocationMutationActor();
    deactivateUserThroughMutation(UserId::fromString($actorId), createActiveMutationActorId('Deactivator'));

    expect(fn () => mutationActingAs($actorId, fn () => app(CreateAllocationAction::class)->execute(
        personId: UuidGenerator::uuid7(),
        bedId: UuidGenerator::uuid7(),
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    )))->toThrow(UnauthorizedMutationException::class, 'Mutation actor must be an active identity user.');

    expect(app(IdentityUserReadContract::class)->isUserActive($actorId))->toBeFalse();
});

it('does not mutate allocation state when create is unauthorized', function (): void {
    $personId = UuidGenerator::uuid7();
    $bedId = UuidGenerator::uuid7();

    expect(fn () => app(CreateAllocationAction::class)->execute(
        personId: $personId,
        bedId: $bedId,
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ))->toThrow(UnauthorizedMutationException::class);

    expect(app(AllocationRepositoryContract::class)->findActiveByPersonId(PersonAllocationRef::fromString($personId)))->toBeEmpty();
});

it('denies allocation release without a mutation principal', function (): void {
    $actorId = createAllocationMutationActor();
    $allocation = withAllocationMutationActor(fn () => app(CreateAllocationAction::class)->execute(
        personId: UuidGenerator::uuid7(),
        bedId: UuidGenerator::uuid7(),
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ), $actorId);

    expect(fn () => app(ReleaseAllocationAction::class)->execute($allocation->requireId()->value, 'test'))
        ->toThrow(UnauthorizedMutationException::class);

    expect(app(AllocationRepositoryContract::class)->findById($allocation->requireId())?->isActive())->toBeTrue();
});

it('allows allocation release with an authorized actor', function (): void {
    $actorId = createAllocationMutationActor();
    $allocation = withAllocationMutationActor(fn () => app(CreateAllocationAction::class)->execute(
        personId: UuidGenerator::uuid7(),
        bedId: UuidGenerator::uuid7(),
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ), $actorId);

    $released = withAllocationMutationActor(
        fn () => app(ReleaseAllocationAction::class)->execute($allocation->requireId()->value, 'authorized release'),
        $actorId,
    );

    expect($released->status)->toBe(AllocationStatus::Released);
});

it('denies allocation from request without a mutation principal', function (): void {
    expect(fn () => app(CreateAllocationFromRequestAction::class)->execute(UuidGenerator::uuid7()))
        ->toThrow(UnauthorizedMutationException::class);
});

it('registers allocation actions as enforced rather than pending', function (): void {
    expect(PendingMutationAuthorizationRegistry::isPending(CreateAllocationAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(CreateAllocationFromRequestAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(ReleaseAllocationAction::class))->toBeFalse();
});

it('registers allocation mutation capability keys', function (): void {
    expect(MutationCapabilityCatalog::registeredKeys())->toContain(
        MutationCapabilityCatalog::ALLOCATION_CREATE,
        MutationCapabilityCatalog::ALLOCATION_CREATE_FROM_REQUEST,
        MutationCapabilityCatalog::ALLOCATION_RELEASE,
    );
});
