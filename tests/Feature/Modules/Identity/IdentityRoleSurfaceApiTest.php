<?php

declare(strict_types=1);

use App\Modules\Identity\Domain\PlatformRoles;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

/**
 * @return array{actor: UserModel, actorId: string}
 */
function createIdentityRoleApiActor(bool $withPermission = true): array
{
    $user = createIdentityUserThroughMutation(
        'Role API Actor',
        'role.api.'.uniqid('', true).'@example.com',
    );
    $actorId = $user->requireId()->value;
    $actor = UserModel::query()->findOrFail($actorId);

    if ($withPermission) {
        grantIdentityRolesManagePermission($actorId);
    }

    return ['actor' => $actor, 'actorId' => $actorId];
}

function authenticateIdentityRoleApi(UserModel $actor): void
{
    test()->actingAs($actor, 'api');
    request()->attributes->set('audit_principal_user_id', $actor->id);
}

function webRoleId(string $name): int
{
    return (int) Role::query()
        ->where('name', $name)
        ->where('guard_name', 'web')
        ->value('id');
}

it('lists roles for a permission holder', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);

    $this->getJson('/api/identity/roles')
        ->assertOk()
        ->assertJsonFragment(['name' => PlatformRoles::SYSTEM_ADMINISTRATOR, 'guard_name' => 'web']);
});

it('creates a role with guard_name web', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);

    $this->postJson('/api/identity/roles', ['name' => 'OpsReviewer'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'OpsReviewer')
        ->assertJsonPath('data.guard_name', 'web');

    expect(Role::query()->where('name', 'OpsReviewer')->where('guard_name', 'web')->exists())->toBeTrue();
});

it('renames a non-protected role', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);

    $roleId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->patchJson("/api/identity/roles/{$roleId}", ['name' => 'HrManagerRenamed'])
        ->assertOk()
        ->assertJsonPath('data.name', 'HrManagerRenamed');
});

it('deletes an unused non-protected role', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);

    $created = $this->postJson('/api/identity/roles', ['name' => 'TemporaryRole'])
        ->assertCreated()
        ->json('data.id');

    $this->deleteJson("/api/identity/roles/{$created}")
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(Role::query()->where('id', $created)->exists())->toBeFalse();
});

it('lists users for a role', function (): void {
    ['actor' => $actor, 'actorId' => $actorId] = createIdentityRoleApiActor();
    $actor->assignRole(IdentityRoleSeeder::ROLE_HR_MGR);
    authenticateIdentityRoleApi($actor);

    $roleId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->getJson("/api/identity/roles/{$roleId}/users")
        ->assertOk()
        ->assertJsonFragment(['id' => $actorId]);
});

it('syncs user roles atomically', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);

    $subject = createIdentityUserThroughMutation('Role Sync Subject', 'role.sync.'.uniqid('', true).'@example.com');
    $subjectId = $subject->requireId()->value;
    $hrId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);
    $adminId = webRoleId(IdentityRoleSeeder::ROLE_ADMINISTRATOR);

    $this->putJson("/api/identity/users/{$subjectId}/roles", ['roles' => [$hrId, $adminId]])
        ->assertOk()
        ->assertJsonPath('success', true);

    $model = UserModel::query()->findOrFail($subjectId);
    expect($model->hasRole(IdentityRoleSeeder::ROLE_HR_MGR))->toBeTrue()
        ->and($model->hasRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR))->toBeTrue();
});

it('forbidden lists roles without identity.roles.manage', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor(withPermission: false);
    authenticateIdentityRoleApi($actor);

    $this->getJson('/api/identity/roles')->assertForbidden();
});

it('forbidden creates role without identity.roles.manage', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor(withPermission: false);
    authenticateIdentityRoleApi($actor);

    $this->postJson('/api/identity/roles', ['name' => 'NoPermRole'])->assertForbidden();
});

it('forbidden patches role without identity.roles.manage', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor(withPermission: false);
    authenticateIdentityRoleApi($actor);
    $roleId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->patchJson("/api/identity/roles/{$roleId}", ['name' => 'X'])->assertForbidden();
});

it('forbidden deletes role without identity.roles.manage', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor(withPermission: false);
    authenticateIdentityRoleApi($actor);
    $roleId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->deleteJson("/api/identity/roles/{$roleId}")->assertForbidden();
});

it('forbidden lists role users without identity.roles.manage', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor(withPermission: false);
    authenticateIdentityRoleApi($actor);
    $roleId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->getJson("/api/identity/roles/{$roleId}/users")->assertForbidden();
});

it('forbidden syncs user roles without identity.roles.manage', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor(withPermission: false);
    authenticateIdentityRoleApi($actor);
    $subject = createIdentityUserThroughMutation('NoPerm Subject', 'noperm.subject.'.uniqid('', true).'@example.com');
    $hrId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->putJson('/api/identity/users/'.$subject->requireId()->value.'/roles', ['roles' => [$hrId]])
        ->assertForbidden();
});

it('creates new roles with guard_name web only (I1)', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);

    $this->postJson('/api/identity/roles', ['name' => 'GuardWebOnly'])
        ->assertCreated()
        ->assertJsonPath('data.guard_name', 'web');

    expect(Role::query()->where('name', 'GuardWebOnly')->where('guard_name', 'api')->exists())->toBeFalse();
});

it('conflicts when renaming SystemAdministrator (I2)', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);
    $roleId = webRoleId(PlatformRoles::SYSTEM_ADMINISTRATOR);

    $this->patchJson("/api/identity/roles/{$roleId}", ['name' => 'RenamedAdmin'])
        ->assertStatus(409);
});

it('conflicts when deleting SystemAdministrator (I2)', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);
    $roleId = webRoleId(PlatformRoles::SYSTEM_ADMINISTRATOR);

    $this->deleteJson("/api/identity/roles/{$roleId}")
        ->assertStatus(409);
});

it('conflicts when actor removes SystemAdministrator from self (I3)', function (): void {
    ['actor' => $actor, 'actorId' => $actorId] = createIdentityRoleApiActor();
    $actor->assignRole(PlatformRoles::SYSTEM_ADMINISTRATOR);
    authenticateIdentityRoleApi($actor);

    $hrId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->putJson("/api/identity/users/{$actorId}/roles", ['roles' => [$hrId]])
        ->assertStatus(409);

    expect(UserModel::query()->findOrFail($actorId)->hasRole(PlatformRoles::SYSTEM_ADMINISTRATOR))->toBeTrue();
});

it('conflicts when renaming SystemAdministrator with case-variant payload (I2)', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);
    $roleId = webRoleId(PlatformRoles::SYSTEM_ADMINISTRATOR);

    $this->patchJson("/api/identity/roles/{$roleId}", ['name' => 'systemadministrator'])
        ->assertStatus(409);

    expect(Role::query()->whereKey($roleId)->value('name'))->toBe(PlatformRoles::SYSTEM_ADMINISTRATOR);
});

it('conflicts when renaming SystemAdministrator with whitespace payload (I2)', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);
    $roleId = webRoleId(PlatformRoles::SYSTEM_ADMINISTRATOR);

    $this->patchJson("/api/identity/roles/{$roleId}", ['name' => ' SystemAdministrator '])
        ->assertStatus(409);

    expect(Role::query()->whereKey($roleId)->value('name'))->toBe(PlatformRoles::SYSTEM_ADMINISTRATOR);
});

it('allows stripping SystemAdministrator when another holder remains', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    $actor->assignRole(PlatformRoles::SYSTEM_ADMINISTRATOR);
    authenticateIdentityRoleApi($actor);

    $otherAdmin = createIdentityUserThroughMutation('Other SA', 'other.sa.'.uniqid('', true).'@example.com');
    $otherAdminId = $otherAdmin->requireId()->value;
    UserModel::query()->findOrFail($otherAdminId)->assignRole(PlatformRoles::SYSTEM_ADMINISTRATOR);

    $hrId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->putJson("/api/identity/users/{$otherAdminId}/roles", ['roles' => [$hrId]])
        ->assertOk();

    expect(UserModel::query()->findOrFail($otherAdminId)->hasRole(PlatformRoles::SYSTEM_ADMINISTRATOR))->toBeFalse()
        ->and(UserModel::query()->role(PlatformRoles::SYSTEM_ADMINISTRATOR)->count())->toBe(1);
});

it('cannot revoke SA from sole remaining admin even by another admin', function (): void {
    ['actor' => $actor, 'actorId' => $actorId] = createIdentityRoleApiActor();
    $actor->assignRole(PlatformRoles::SYSTEM_ADMINISTRATOR);

    $adminB = createIdentityUserThroughMutation('Admin B SA', 'admin.b.'.uniqid('', true).'@example.com');
    $adminBId = $adminB->requireId()->value;
    UserModel::query()->findOrFail($adminBId)->assignRole(PlatformRoles::SYSTEM_ADMINISTRATOR);

    grantIdentityRolesManagePermission($adminBId);
    authenticateIdentityRoleApi(UserModel::query()->findOrFail($adminBId));

    $hrId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->putJson("/api/identity/users/{$actorId}/roles", ['roles' => [$hrId]])
        ->assertOk();

    expect(UserModel::query()->role(PlatformRoles::SYSTEM_ADMINISTRATOR)->count())->toBe(1);

    $this->putJson("/api/identity/users/{$adminBId}/roles", ['roles' => [$hrId]])
        ->assertStatus(409);

    expect(UserModel::query()->findOrFail($adminBId)->hasRole(PlatformRoles::SYSTEM_ADMINISTRATOR))->toBeTrue();
});

it('rejects stripping SystemAdministrator from the sole holder (I3 last-admin)', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);

    $soleAdmin = createIdentityUserThroughMutation('Sole SA', 'sole.sa.'.uniqid('', true).'@example.com');
    $soleAdminId = $soleAdmin->requireId()->value;
    UserModel::query()->findOrFail($soleAdminId)->assignRole(PlatformRoles::SYSTEM_ADMINISTRATOR);

    expect(UserModel::query()->role(PlatformRoles::SYSTEM_ADMINISTRATOR)->count())->toBe(1);

    $hrId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->putJson("/api/identity/users/{$soleAdminId}/roles", ['roles' => [$hrId]])
        ->assertStatus(409);

    expect(UserModel::query()->findOrFail($soleAdminId)->hasRole(PlatformRoles::SYSTEM_ADMINISTRATOR))->toBeTrue();
});

it('rejects sync of roles that exist only under guard_name api', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    authenticateIdentityRoleApi($actor);

    $apiRole = Role::create([
        'name' => 'ApiOnlyRole'.uniqid(),
        'guard_name' => 'api',
    ]);

    $subject = createIdentityUserThroughMutation('Api Role Subject', 'api.role.'.uniqid('', true).'@example.com');

    $this->putJson('/api/identity/users/'.$subject->requireId()->value.'/roles', [
        'roles' => [(int) $apiRole->id],
    ])->assertStatus(422);
});

it('conflicts when deleting a role that still has assigned users (I4)', function (): void {
    ['actor' => $actor] = createIdentityRoleApiActor();
    $actor->assignRole(IdentityRoleSeeder::ROLE_HR_MGR);
    authenticateIdentityRoleApi($actor);
    $roleId = webRoleId(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->deleteJson("/api/identity/roles/{$roleId}")
        ->assertStatus(409);

    expect(Role::query()->where('id', $roleId)->exists())->toBeTrue();
});
