<?php

declare(strict_types=1);

use App\Modules\CheckIn\Application\Contracts\CheckInCommandPort;
use App\Modules\CheckIn\Domain\CheckInOperationRoles;
use App\Modules\CheckIn\Domain\Exceptions\AllocationNotActiveException;
use App\Modules\CheckIn\Domain\Exceptions\OperatorRoleRequiredException;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Spatie\Permission\Models\Role;

function createBoundaryTestOperator(): string
{
    Role::findOrCreate(CheckInOperationRoles::OPERATOR, config('auth.defaults.guard', 'web'));

    $user = app(CreateUserAction::class)->execute(
        'Boundary Operator',
        'boundary-operator-'.uniqid('', true).'@example.com',
    );

    app(AssignRoleToUserAction::class)->execute(
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
    $user = app(CreateUserAction::class)->execute(
        'Non Operator',
        'non-operator-'.uniqid('', true).'@example.com',
    );

    $allocationId = UuidGenerator::uuid7();

    $userId = $user->requireId()->value;

    expect(fn () => asCheckInMutationPrincipal($userId, fn () => app(CheckInCommandPort::class)->checkIn($allocationId, $userId)))
        ->toThrow(OperatorRoleRequiredException::class);
});
