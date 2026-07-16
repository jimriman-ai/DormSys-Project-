<?php

declare(strict_types=1);

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function createUiA1IdentityUser(string $displayName = 'UI-A1 Manager'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'uia1.'.uniqid('', true).'@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

function assignUiA1IdentityRole(UserModel $user, string $roleName): void
{
    $role = Role::findOrCreate($roleName, 'identity');
    $user->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

it('UI-A1: layout renders logout form with csrf for dormitory-manager', function (): void {
    $user = createUiA1IdentityUser('Layout Manager');
    assignUiA1IdentityRole($user, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $this->actingAs($user, 'identity')
        ->get(route('dormitory-admin.manager'))
        ->assertOk()
        ->assertSee('data-testid="dormitory-admin-logout"', false)
        ->assertSee('name="_token"', false)
        ->assertSee('خروج', false)
        ->assertSee('Layout Manager', false);
});

it('UI-A1 L6-R1 Amend: identity-only principal can POST web logout', function (): void {
    $user = createUiA1IdentityUser('Logout Identity');
    assignUiA1IdentityRole($user, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $this->actingAs($user, 'identity')
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest('identity');
    $this->assertGuest('api');
});

it('UI-A1 L6-R1 Amend: absolute guest cannot POST web logout', function (): void {
    Auth::guard('api')->logout();
    Auth::guard('identity')->logout();
    Auth::guard('web')->logout();

    $this->post(route('logout'))
        ->assertRedirect('/login');
});
