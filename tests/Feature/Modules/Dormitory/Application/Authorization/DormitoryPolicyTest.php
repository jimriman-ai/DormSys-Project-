<?php

declare(strict_types=1);

use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Shared\Auth\IdentityRoleGuard;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function createDormitoryPolicyIdentityUser(string $displayName = 'Dormitory Policy User'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'dorm.policy.'.uniqid('', true).'@dormsys.local',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

function assignDormitoryPolicyIdentityRole(UserModel $user, string $roleName): void
{
    $role = Role::findOrCreate($roleName, 'identity');
    $user->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

it('allows viewAny for employee role', function (): void {
    $user = createDormitoryPolicyIdentityUser('Policy Employee');
    assignDormitoryPolicyIdentityRole($user, IdentityRoleGuard::ROLE_EMPLOYEE);

    expect(Gate::forUser($user)->allows('viewAny', DormitoryModel::class))->toBeTrue();
});

it('denies viewAny for dormitory-manager role', function (): void {
    $user = createDormitoryPolicyIdentityUser('Policy Manager');
    assignDormitoryPolicyIdentityRole($user, IdentityRoleGuard::ROLE_DORMITORY_MANAGER);

    expect(Gate::forUser($user)->denies('viewAny', DormitoryModel::class))->toBeTrue();
});

it('denies viewAny for identity user with no Sprint C role', function (): void {
    $user = createDormitoryPolicyIdentityUser('Policy No Role');

    expect(Gate::forUser($user)->denies('viewAny', DormitoryModel::class))->toBeTrue();
});
