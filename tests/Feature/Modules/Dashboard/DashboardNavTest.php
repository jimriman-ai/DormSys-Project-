<?php

declare(strict_types=1);

use App\Modules\Dashboard\Domain\DashboardIdentityRoles;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Shared\Auth\IdentityRoleGuard;
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

function createDashboardNavIdentityUser(string $displayName = 'Dashboard Nav User'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'dash.nav.'.uniqid('', true).'@dormsys.local',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

function assignDashboardNavIdentityRole(UserModel $user, string $roleName): void
{
    $role = Role::findOrCreate($roleName, 'identity');
    $user->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

it('shows employee nav items and hides Approvals Stage 1', function (): void {
    $user = createDashboardNavIdentityUser('Dev Employee Nav');
    assignDashboardNavIdentityRole($user, DashboardIdentityRoles::EMPLOYEE);

    $this->actingAs($user, 'identity')
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-testid="dashboard-nav"', false)
        ->assertSee('data-testid="dashboard-nav-dashboard"', false)
        ->assertSee('data-testid="dashboard-nav-requests"', false)
        ->assertDontSee('data-testid="dashboard-nav-approvals-stage1"', false);
});

it('shows dormitory-manager nav including Approvals Stage 1', function (): void {
    $user = createDashboardNavIdentityUser('Dev Manager Nav');
    assignDashboardNavIdentityRole($user, IdentityRoleGuard::ROLE_DORMITORY_MANAGER);

    $this->actingAs($user, 'identity')
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-testid="dashboard-nav-dashboard"', false)
        ->assertSee('data-testid="dashboard-nav-requests"', false)
        ->assertSee('data-testid="dashboard-nav-approvals-stage1"', false);
});

it('redirects guests from /dashboard without rendering nav', function (): void {
    $this->get(route('dashboard'))
        ->assertRedirect('/login')
        ->assertDontSee('data-testid="dashboard-nav"', false);
});
