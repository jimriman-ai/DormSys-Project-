<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContext;
use App\Modules\Identity\Application\Authorization\DormitoryStructurePermissionCatalog;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Facades\Artisan;

function seedDormitoryStructurePermissionCatalog(): void
{
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
}

/**
 * Test-only grant for DormitoryStructureAuthorizationGate::assertStructureView consumers
 * (e.g. DormitoryReadBridge::siteExists → getDormitoryDetail).
 */
function grantDormitoryStructureViewPermission(string $userId): void
{
    seedDormitoryStructurePermissionCatalog();

    $model = UserModel::query()->find($userId);

    if ($model === null) {
        // Synthetic principal UUIDs (no identity row) — skip grant; callers must
        // not rely on DormitoryStructureAuthorizationGate for those principals.
        return;
    }

    if (! $model->checkPermissionTo(DormitoryStructurePermissionCatalog::VIEW)) {
        $model->givePermissionTo(DormitoryStructurePermissionCatalog::VIEW);
    }
}

function prepareDormitoryStructureViewPrincipalId(): string
{
    seedDormitoryStructurePermissionCatalog();
    $user = createIdentityUserThroughMutation(
        'Dormitory Structure Viewer',
        'dorm.structure.view.'.uniqid('', true).'@example.com',
    );
    grantDormitoryStructureViewPermission($user->requireId()->value);

    return $user->requireId()->value;
}

function prepareDormitoryStructureManagePrincipalId(): string
{
    seedDormitoryStructurePermissionCatalog();
    $user = createIdentityUserThroughMutation(
        'Dormitory Structure Manager',
        'dorm.structure.manage.'.uniqid('', true).'@example.com',
    );
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->givePermissionTo(DormitoryStructurePermissionCatalog::MANAGE);

    return $user->requireId()->value;
}

/**
 * Direct principal permission grant for PEP tests only — not role-mapping seed work.
 *
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function withDormitoryStructureManageActor(callable $callback): mixed
{
    return MutationPrincipalContext::runAs(prepareDormitoryStructureManagePrincipalId(), $callback);
}

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function withDormitoryStructureViewActor(callable $callback): mixed
{
    return MutationPrincipalContext::runAs(prepareDormitoryStructureViewPrincipalId(), $callback);
}

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function withDormitoryStructureUnauthorizedActor(callable $callback): mixed
{
    seedDormitoryStructurePermissionCatalog();
    $user = createIdentityUserThroughMutation(
        'Dormitory Structure Unauthorized',
        'dorm.structure.unauthorized.'.uniqid('', true).'@example.com',
    );

    return MutationPrincipalContext::runAs($user->requireId()->value, $callback);
}
