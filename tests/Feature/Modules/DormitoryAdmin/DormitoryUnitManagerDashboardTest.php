<?php

declare(strict_types=1);

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function createUnitManagerIdentityUser(string $displayName = 'Unit Manager'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'unit.mgr.'.uniqid('', true).'@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

function assignUnitManagerRole(UserModel $user, string $roleName): void
{
    $role = Role::findOrCreate($roleName, 'identity');
    $user->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

it('redirects guests from the unit manager dashboard', function (): void {
    $this->get('/dormitory-admin/unit')->assertRedirect('/login');
});

it('forbids authenticated identity users without dormitory-unit-manager role', function (): void {
    $user = createUnitManagerIdentityUser('No Unit Role');

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertForbidden();
});

it('forbids dormitory-manager-only users from the unit dashboard', function (): void {
    $user = createUnitManagerIdentityUser('Manager Only');
    assignUnitManagerRole($user, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertForbidden();
});

it('allows dormitory-unit-manager with empty assignments', function (): void {
    $user = createUnitManagerIdentityUser('Empty Unit Manager');
    assignUnitManagerRole($user, IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertOk()
        ->assertSee('اتاقی به شما اختصاص داده نشده است.', false)
        ->assertSee('خارج از محدوده — Stage 3', false);
});

it('leaves dormitory manager route accessible only to dormitory-manager', function (): void {
    $manager = createUnitManagerIdentityUser('Still Manager');
    assignUnitManagerRole($manager, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $unitOnly = createUnitManagerIdentityUser('Unit Only On Manager Route');
    assignUnitManagerRole($unitOnly, IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);

    $this->actingAs($manager, 'identity')
        ->get('/dormitory-admin')
        ->assertOk();

    $this->actingAs($unitOnly, 'identity')
        ->get('/dormitory-admin')
        ->assertForbidden();
});
