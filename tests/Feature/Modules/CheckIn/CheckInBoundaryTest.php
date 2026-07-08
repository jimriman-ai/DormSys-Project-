<?php

declare(strict_types=1);

use App\Modules\CheckIn\Application\Contracts\CheckInCommandPort;
use App\Modules\CheckIn\Domain\CheckInOperationRoles;
use App\Modules\CheckIn\Domain\Exceptions\AllocationNotActiveException;
use App\Modules\CheckIn\Domain\Exceptions\OperatorRoleRequiredException;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Spatie\Permission\Models\Role;

function createBoundaryTestOperator(): string
{
    Role::findOrCreate(CheckInOperationRoles::OPERATOR, config('auth.defaults.guard', 'web'));

    $user = createIdentityUserThroughMutation(
        'Boundary Operator',
        'boundary-operator-'.uniqid('', true).'@example.com',
    );

    assignRoleThroughMutation(
        $user->requireId(),
        CheckInOperationRoles::OPERATOR,
    );

    return $user->requireId()->value;
}

it('rejects check-in when no active allocation exists', function (): void {
    $operatorId = createBoundaryTestOperator();
    $missingAllocationId = UuidGenerator::uuid7();

    expect(fn () => asCheckInMutationPrincipal($operatorId, fn () => app(CheckInCommandPort::class)->checkIn($missingAllocationId, $operatorId)))
        ->toThrow(AllocationNotActiveException::class);
});

it('rejects check-in when the user is not an operator', function (): void {
    $user = createIdentityUserThroughMutation(
        'Non Operator',
        'non-operator-'.uniqid('', true).'@example.com',
    );

    $allocationId = UuidGenerator::uuid7();

    $userId = $user->requireId()->value;

    expect(fn () => asCheckInMutationPrincipal($userId, fn () => app(CheckInCommandPort::class)->checkIn($allocationId, $userId)))
        ->toThrow(OperatorRoleRequiredException::class);
});

it('rejects duplicate check-in on the same allocation', function (): void {
    $allocation = runAllocationMutation(fn () => app(App\Modules\Allocation\Application\Services\CreateAllocationAction::class)->execute(
        personId: UuidGenerator::uuid7(),
        bedId: UuidGenerator::uuid7(),
        start: new DateTimeImmutable('2026-08-01', new DateTimeZone('UTC')),
        end: new DateTimeImmutable('2026-08-31', new DateTimeZone('UTC')),
    ));

    $operatorId = createBoundaryTestOperator();
    $allocationId = $allocation->requireId()->value;

    asCheckInOperator($operatorId, fn () => app(CheckInCommandPort::class)->checkIn($allocationId, $operatorId));

    expect(fn () => asCheckInOperator($operatorId, fn () => app(CheckInCommandPort::class)->checkIn($allocationId, $operatorId)))
        ->toThrow(App\Modules\CheckIn\Domain\Exceptions\OpenCheckInRecordExistsException::class);
});

it('rejects check-out when no open check-in record exists', function (): void {
    $operatorId = createBoundaryTestOperator();
    $allocationId = UuidGenerator::uuid7();

    expect(fn () => asCheckInOperator($operatorId, fn () => app(CheckInCommandPort::class)->checkOut($allocationId, $operatorId)))
        ->toThrow(App\Modules\CheckIn\Domain\Exceptions\NoOpenCheckInRecordException::class);
});
