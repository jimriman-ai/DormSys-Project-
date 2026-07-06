<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\CheckIn\Domain\CheckInOperationRoles;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Spatie\Permission\Models\Role;

/**
 * @return array{identity: UserModel, email: string, password: string, principalId: string}
 */
function createCheckInHttpOperator(
    ?string $email = null,
    string $password = 'secret-password',
): array {
    Role::findOrCreate(CheckInOperationRoles::OPERATOR, config('auth.defaults.guard', 'web'));

    $email ??= 'checkin.http.operator.'.uniqid('', true).'@example.com';

    $user = createIdentityUserThroughMutation('Check-In HTTP Operator', $email);

    $identity = UserModel::query()->findOrFail($user->requireId()->value);
    assignRoleThroughMutation($user->requireId(), CheckInOperationRoles::OPERATOR);

    return [
        'identity' => $identity,
        'email' => $email,
        'password' => $password,
        'principalId' => $user->requireId()->value,
    ];
}

function authenticateCheckInHttpUser(UserModel $identity): void
{
    test()->actingAs($identity, 'api');
    request()->attributes->set('audit_principal_user_id', $identity->id);
    app(MutationPrincipalContextHolder::class)->clear();
}

function checkInHttpUrl(string $suffix = ''): string
{
    return '/api/check-in'.($suffix !== '' ? '/'.$suffix : '');
}
