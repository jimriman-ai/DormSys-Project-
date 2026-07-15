<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Models\User;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;

/**
 * @return array{identity: UserModel, email: string, password: string, principalId: string}
 */
function createLotteryHttpOperator(
    ?string $email = null,
    string $password = 'secret-password',
): array {
    $email ??= 'lottery.http.operator.'.uniqid('', true).'@example.com';

    User::factory()->create([
        'email' => $email,
        'password' => $password,
    ]);

    $principalId = createActiveMutationActorId('Lottery HTTP Operator', $email);
    $identity = UserModel::query()->findOrFail($principalId);
    grantDormitoryStructureViewPermission($principalId);

    return [
        'identity' => $identity,
        'email' => $email,
        'password' => $password,
        'principalId' => $principalId,
    ];
}

function authenticateLotteryHttpUser(UserModel $identity): void
{
    grantDormitoryStructureViewPermission($identity->id);
    test()->actingAs($identity, 'api');
    request()->attributes->set('audit_principal_user_id', $identity->id);
    app(MutationPrincipalContextHolder::class)->clear();
}

function lotteryHttpProgramUrl(string $programId, string $action = ''): string
{
    $suffix = $action !== '' ? '/'.$action : '';

    return "/api/lottery/programs/{$programId}{$suffix}";
}

function lotteryHttpCreateProgramUrl(): string
{
    return '/api/lottery/programs';
}
