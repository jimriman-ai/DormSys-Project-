<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Models\User;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;

/**
 * @return array{identity: UserModel, email: string, password: string, principalId: string}
 */
function createAllocationHttpOperator(
    ?string $email = null,
    string $password = 'secret-password',
): array {
    $email ??= 'allocation.http.operator.'.uniqid('', true).'@example.com';

    User::factory()->create([
        'email' => $email,
        'password' => $password,
    ]);

    $principalId = createActiveMutationActorId('Allocation HTTP Operator', $email);
    $identity = UserModel::query()->findOrFail($principalId);

    return [
        'identity' => $identity,
        'email' => $email,
        'password' => $password,
        'principalId' => $principalId,
    ];
}

function authenticateAllocationHttpUser(UserModel $identity): void
{
    test()->actingAs($identity, 'api');
    request()->attributes->set('audit_principal_user_id', $identity->id);
    app(MutationPrincipalContextHolder::class)->clear();
}

function allocationHttpUrl(string $suffix = ''): string
{
    return '/api/allocations'.($suffix !== '' ? '/'.$suffix : '');
}
