<?php

declare(strict_types=1);

use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryAssignment;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Shared\Auth\IdentityRoleGuard;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function createEmployeeDormitoryUiUser(string $displayName = 'Employee Dorm UI'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'emp.dorm.ui.'.uniqid('', true).'@dormsys.local',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

function assignEmployeeDormitoryUiRole(UserModel $user, string $roleName): void
{
    $role = Role::findOrCreate($roleName, 'identity');
    $user->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

function createEmployeeDormitoryUiSite(string $code, string $name): DormitoryModel
{
    return DormitoryModel::query()->create([
        'code' => $code,
        'name' => $name,
        'status' => ResourceStatus::Available,
    ]);
}

function createEmployeeDormitoryUiAssignment(
    UserModel $user,
    DormitoryModel $dormitory,
    ?Carbon $revokedAt = null,
): DormitoryAssignment {
    return DormitoryAssignment::query()->create([
        'user_id' => $user->getId(),
        'dormitory_id' => $dormitory->getId(),
        'assigned_at' => now(),
        'revoked_at' => $revokedAt,
    ]);
}

it('lists only actively assigned dormitories for an employee', function (): void {
    $user = createEmployeeDormitoryUiUser('Assigned Employee');
    assignEmployeeDormitoryUiRole($user, IdentityRoleGuard::ROLE_EMPLOYEE);

    $mine = createEmployeeDormitoryUiSite('EMP-A', 'My Dormitory');
    $other = createEmployeeDormitoryUiSite('EMP-B', 'Other Dormitory');
    createEmployeeDormitoryUiAssignment($user, $mine);

    $this->actingAs($user, 'identity')
        ->get(route('dormitories.index'))
        ->assertOk()
        ->assertSee('data-testid="dormitory-index-list"', false)
        ->assertSee('data-dormitory-id="'.$mine->getId().'"', false)
        ->assertSee('My Dormitory')
        ->assertDontSee('data-dormitory-id="'.$other->getId().'"', false)
        ->assertDontSee('Other Dormitory');
});

it('shows empty-state when employee has no active assignments', function (): void {
    $user = createEmployeeDormitoryUiUser('Unassigned Employee');
    assignEmployeeDormitoryUiRole($user, IdentityRoleGuard::ROLE_EMPLOYEE);
    createEmployeeDormitoryUiSite('EMP-NONE', 'Unlinked Dormitory');

    $this->actingAs($user, 'identity')
        ->get(route('dormitories.index'))
        ->assertOk()
        ->assertSee('data-testid="dormitory-index-empty"', false)
        ->assertDontSee('data-testid="dormitory-index-list"', false)
        ->assertDontSee('Unlinked Dormitory');
});

it('excludes revoked assignments from the employee dormitory index', function (): void {
    $user = createEmployeeDormitoryUiUser('Revoked Employee');
    assignEmployeeDormitoryUiRole($user, IdentityRoleGuard::ROLE_EMPLOYEE);

    $revoked = createEmployeeDormitoryUiSite('EMP-REV', 'Revoked Dormitory');
    $active = createEmployeeDormitoryUiSite('EMP-ACT', 'Active Dormitory');
    createEmployeeDormitoryUiAssignment($user, $revoked, now());
    createEmployeeDormitoryUiAssignment($user, $active);

    $this->actingAs($user, 'identity')
        ->get(route('dormitories.index'))
        ->assertOk()
        ->assertSee('data-dormitory-id="'.$active->getId().'"', false)
        ->assertSee('Active Dormitory')
        ->assertDontSee('data-dormitory-id="'.$revoked->getId().'"', false)
        ->assertDontSee('Revoked Dormitory');
});

it('forbids show for a dormitory without an active assignment', function (): void {
    $user = createEmployeeDormitoryUiUser('Cross Employee');
    assignEmployeeDormitoryUiRole($user, IdentityRoleGuard::ROLE_EMPLOYEE);

    $assigned = createEmployeeDormitoryUiSite('EMP-OK', 'Assigned Site');
    $foreign = createEmployeeDormitoryUiSite('EMP-NO', 'Foreign Site');
    createEmployeeDormitoryUiAssignment($user, $assigned);

    $this->actingAs($user, 'identity')
        ->get(route('dormitories.show', $foreign->getId()))
        ->assertForbidden();
});

it('forbids dormitory-manager from employee dormitory index', function (): void {
    $user = createEmployeeDormitoryUiUser('Manager Blocked');
    assignEmployeeDormitoryUiRole($user, IdentityRoleGuard::ROLE_DORMITORY_MANAGER);

    $this->actingAs($user, 'identity')
        ->get(route('dormitories.index'))
        ->assertForbidden();
});
