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

function createDormitoryAdminIdentityUser(string $displayName = 'Dorm Manager'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'dorm.mgr.'.uniqid('', true).'@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

function assignIdentityGuardRole(UserModel $user, string $roleName): void
{
    $role = Role::findOrCreate($roleName, 'identity');
    $user->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

it('redirects guests from the dormitory manager dashboard', function (): void {
    $this->get('/dormitory-admin')->assertRedirect('/login');
});

it('forbids authenticated identity users without dormitory-manager role', function (): void {
    $user = createDormitoryAdminIdentityUser('No Role User');

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin')
        ->assertForbidden();
});

it('shows empty state for dormitory-manager with no assignments', function (): void {
    $user = createDormitoryAdminIdentityUser('Empty Manager');
    assignIdentityGuardRole($user, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin')
        ->assertOk()
        ->assertSee('خوابگاهی به شما اختصاص داده نشده است.', false)
        ->assertSee('خارج از محدوده — Stage 3', false);
});
