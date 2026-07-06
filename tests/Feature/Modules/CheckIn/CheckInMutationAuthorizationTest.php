<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Registry\PendingMutationAuthorizationRegistry;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Application\Services\CheckInAction;
use App\Modules\CheckIn\Application\Services\CheckOutAction;
use App\Modules\CheckIn\Domain\CheckInOperationRoles;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

function createCheckInMutationOperator(): string
{
    Role::findOrCreate(CheckInOperationRoles::OPERATOR, config('auth.defaults.guard', 'web'));

    $user = app(CreateUserAction::class)->execute(
        'Check-In Mutation Operator',
        'checkin.mutation.'.uniqid('', true).'@example.com',
    );

    app(AssignRoleToUserAction::class)->execute(
        $user->requireId(),
        CheckInOperationRoles::OPERATOR,
    );

    return $user->requireId()->value;
}

function createActiveAllocationForCheckInMutation(): string
{
    $allocation = app(CreateAllocationAction::class)->execute(
        personId: UuidGenerator::uuid7(),
        bedId: UuidGenerator::uuid7(),
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    );

    return $allocation->requireId()->value;
}

it('denies check-in create without a mutation principal', function (): void {
    $operatorId = createCheckInMutationOperator();
    $allocationId = createActiveAllocationForCheckInMutation();

    expect(fn () => app(CheckInAction::class)->execute($allocationId, $operatorId))
        ->toThrow(UnauthorizedMutationException::class);

    expect(app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId))->toBeNull();
});

it('denies check-in operate when operator does not match mutation actor', function (): void {
    $operatorId = createCheckInMutationOperator();
    $otherPrincipalId = app(CreateUserAction::class)->execute(
        'Other Check-In Actor',
        'other.checkin.'.uniqid('', true).'@example.com',
    )->requireId()->value;
    $allocationId = createActiveAllocationForCheckInMutation();

    expect(fn () => asCheckInMutationPrincipal($otherPrincipalId, fn () => app(CheckInAction::class)->execute($allocationId, $operatorId)))
        ->toThrow(UnauthorizedMutationException::class, 'Operator must match the mutation actor.');

    expect(app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId))->toBeNull();
});

it('denies check-out close when operator does not match mutation actor', function (): void {
    $operatorId = createCheckInMutationOperator();
    $otherPrincipalId = app(CreateUserAction::class)->execute(
        'Other Check-Out Actor',
        'other.checkout.'.uniqid('', true).'@example.com',
    )->requireId()->value;
    $allocationId = createActiveAllocationForCheckInMutation();

    asCheckInOperator($operatorId, fn () => app(CheckInAction::class)->execute($allocationId, $operatorId));

    expect(fn () => asCheckInMutationPrincipal($otherPrincipalId, fn () => app(CheckOutAction::class)->execute($allocationId, $operatorId)))
        ->toThrow(UnauthorizedMutationException::class, 'Operator must match the mutation actor.');

    expect(app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId))->not->toBeNull();
});

it('does not mutate state on unauthorized check-in attempt', function (): void {
    $operatorId = createCheckInMutationOperator();
    $allocationId = createActiveAllocationForCheckInMutation();

    expect(fn () => app(CheckInAction::class)->execute($allocationId, $operatorId))
        ->toThrow(UnauthorizedMutationException::class);

    expect(app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId))->toBeNull();
});

it('allows authorized operator to create check-in', function (): void {
    $operatorId = createCheckInMutationOperator();
    $allocationId = createActiveAllocationForCheckInMutation();

    $record = asCheckInOperator($operatorId, fn () => app(CheckInAction::class)->execute($allocationId, $operatorId));

    expect($record->operatorId)->toBe($operatorId)
        ->and($record->isCheckedOut())->toBeFalse();
});

it('allows authorized operator to operate check-in', function (): void {
    $operatorId = createCheckInMutationOperator();
    $allocationId = createActiveAllocationForCheckInMutation();

    $record = asCheckInOperator($operatorId, fn () => app(CheckInAction::class)->execute($allocationId, $operatorId));

    expect(app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId)?->operatorId)
        ->toBe($operatorId)
        ->and($record->allocationId)->toBe($allocationId);
});

it('allows authorized operator to close check-in when domain rules allow', function (): void {
    $operatorId = createCheckInMutationOperator();
    $allocationId = createActiveAllocationForCheckInMutation();

    asCheckInOperator($operatorId, fn () => app(CheckInAction::class)->execute($allocationId, $operatorId));

    $closed = asCheckInOperator($operatorId, fn () => app(CheckOutAction::class)->execute($allocationId, $operatorId));

    expect($closed->isCheckedOut())->toBeTrue()
        ->and(app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId))->toBeNull();
});

it('fails closed when principal context is missing for check-out', function (): void {
    $operatorId = createCheckInMutationOperator();
    $allocationId = createActiveAllocationForCheckInMutation();

    asCheckInOperator($operatorId, fn () => app(CheckInAction::class)->execute($allocationId, $operatorId));

    expect(fn () => app(CheckOutAction::class)->execute($allocationId, $operatorId))
        ->toThrow(UnauthorizedMutationException::class, 'Mutation requires an authorized principal.');
});

it('registers check-in actions as enforced rather than pending', function (): void {
    expect(PendingMutationAuthorizationRegistry::isPending(CheckInAction::class))->toBeFalse()
        ->and(PendingMutationAuthorizationRegistry::isPending(CheckOutAction::class))->toBeFalse();
});

it('registers check-in mutation capability keys', function (): void {
    expect(MutationCapabilityCatalog::registeredKeys())->toContain(
        MutationCapabilityCatalog::CHECKIN_CREATE,
        MutationCapabilityCatalog::CHECKIN_OPERATE,
        MutationCapabilityCatalog::CHECKIN_CLOSE,
    );
});
