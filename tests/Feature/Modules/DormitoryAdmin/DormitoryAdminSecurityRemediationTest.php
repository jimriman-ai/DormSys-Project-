<?php

declare(strict_types=1);

use App\Modules\DormitoryAdmin\DormitoryManagerDashboard;
use App\Modules\DormitoryAdmin\DormitoryUnitManagerDashboard;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function createSecurityRemediationUser(string $displayName): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'sec.rem.'.uniqid('', true).'@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

function assignSecurityRemediationIdentityRole(UserModel $user, string $roleName): void
{
    $role = Role::findOrCreate($roleName, 'identity');
    $user->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

it('forbids web-guard dormitory-manager role from the manager dashboard (SEC-G-01)', function (): void {
    $user = createSecurityRemediationUser('Web Guard Confusion Manager');
    $webRole = Role::findOrCreate(IdentityRoleSeeder::ROLE_DORMITORY_MANAGER, 'web');
    $user->assignRole($webRole);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect($user->roles->contains(
        static fn (\Illuminate\Database\Eloquent\Model $role): bool => $role instanceof Role
            && $role->guard_name === 'identity',
    ))->toBeFalse();

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin')
        ->assertForbidden();
});

it('forbids web-guard dormitory-unit-manager role from the unit dashboard (SEC-G-01)', function (): void {
    $user = createSecurityRemediationUser('Web Guard Confusion Unit');
    $webRole = Role::findOrCreate(IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER, 'web');
    $user->assignRole($webRole);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect($user->roles->contains(
        static fn (\Illuminate\Database\Eloquent\Model $role): bool => $role instanceof Role
            && $role->guard_name === 'identity',
    ))->toBeFalse();

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertForbidden();
});

it('allows identity-guard roles on both dashboards (SEC-G-01 happy path)', function (): void {
    $manager = createSecurityRemediationUser('Identity Manager Happy');
    assignSecurityRemediationIdentityRole($manager, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $unit = createSecurityRemediationUser('Identity Unit Happy');
    assignSecurityRemediationIdentityRole($unit, IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);

    $this->actingAs($manager, 'identity')
        ->get('/dormitory-admin')
        ->assertOk();

    $this->actingAs($unit, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertOk();
});

it('re-asserts identity role on Livewire refresh after revocation (SEC-G-02 manager)', function (): void {
    $user = createSecurityRemediationUser('Stale Manager Privilege');
    assignSecurityRemediationIdentityRole($user, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);
    $role = Role::findByName(IdentityRoleSeeder::ROLE_DORMITORY_MANAGER, 'identity');

    $component = Livewire::actingAs($user, 'identity')
        ->test(DormitoryManagerDashboard::class);

    $component->assertSuccessful();

    $user->roles()->detach($role->getKey());
    $user->unsetRelation('roles');
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $fresh = UserModel::query()->findOrFail($user->id);
    auth('identity')->setUser($fresh);

    expect(App\Shared\Auth\IdentityRoleGuard::userHasIdentityRole(
        $fresh,
        IdentityRoleSeeder::ROLE_DORMITORY_MANAGER,
    ))->toBeFalse();

    $component->call('$refresh');
    $component->assertStatus(403);
});

it('re-asserts identity role on Livewire refresh after revocation (SEC-G-02 unit)', function (): void {
    $user = createSecurityRemediationUser('Stale Unit Privilege');
    assignSecurityRemediationIdentityRole($user, IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);
    $role = Role::findByName(IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER, 'identity');

    $component = Livewire::actingAs($user, 'identity')
        ->test(DormitoryUnitManagerDashboard::class);

    $component->assertSuccessful();

    $user->roles()->detach($role->getKey());
    $user->unsetRelation('roles');
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $fresh = UserModel::query()->findOrFail($user->id);
    auth('identity')->setUser($fresh);

    expect(App\Shared\Auth\IdentityRoleGuard::userHasIdentityRole(
        $fresh,
        IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER,
    ))->toBeFalse();

    $component->call('$refresh');
    $component->assertStatus(403);
});

it('exposes no public query-derived dormitory or room collections (SEC-G-03)', function (): void {
    $managerPublic = collect((new ReflectionClass(DormitoryManagerDashboard::class))->getProperties(ReflectionProperty::IS_PUBLIC))
        ->reject(static fn (ReflectionProperty $property): bool => $property->isStatic())
        ->filter(static fn (ReflectionProperty $property): bool => $property->getDeclaringClass()->getName() === DormitoryManagerDashboard::class)
        ->map(static fn (ReflectionProperty $property): string => $property->getName())
        ->values()
        ->all();

    $unitPublic = collect((new ReflectionClass(DormitoryUnitManagerDashboard::class))->getProperties(ReflectionProperty::IS_PUBLIC))
        ->reject(static fn (ReflectionProperty $property): bool => $property->isStatic())
        ->filter(static fn (ReflectionProperty $property): bool => $property->getDeclaringClass()->getName() === DormitoryUnitManagerDashboard::class)
        ->map(static fn (ReflectionProperty $property): string => $property->getName())
        ->values()
        ->all();

    expect($managerPublic)->not->toContain('dormitories')
        ->and($unitPublic)->not->toContain('rooms')
        ->and($managerPublic)->toBe([])
        ->and($unitPublic)->toBe([]);
});
