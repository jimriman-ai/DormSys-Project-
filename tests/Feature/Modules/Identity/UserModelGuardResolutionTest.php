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

function createUserModelForGuardResolution(string $name = 'Guard Resolution User'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $name,
        'email' => 'guard.res.'.uniqid('', true).'@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

it('assigns a web-guard role via assignRole without GuardDoesNotMatch', function (): void {
    $user = createUserModelForGuardResolution('Web Role Holder');

    $user->assignRole(IdentityRoleSeeder::ROLE_DORM_MGR);

    expect($user->hasRole(IdentityRoleSeeder::ROLE_DORM_MGR))->toBeTrue()
        ->and($user->hasPermissionTo(IdentityRoleSeeder::PERMISSION_AUDIT_READ))->toBeTrue();
});

it('assigns an identity-guard role to the same UserModel via assignRole', function (): void {
    $user = createUserModelForGuardResolution('Dual Guard Holder');

    $user->assignRole(IdentityRoleSeeder::ROLE_DORM_MGR);

    $identityRole = Role::findOrCreate('dormitory-manager', 'identity');
    $user->assignRole($identityRole);

    expect($user->hasRole(IdentityRoleSeeder::ROLE_DORM_MGR))->toBeTrue()
        ->and($user->hasRole('dormitory-manager', 'identity'))->toBeTrue()
        ->and($user->hasPermissionTo(IdentityRoleSeeder::PERMISSION_AUDIT_READ))->toBeTrue();
});
