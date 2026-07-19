<?php

declare(strict_types=1);

use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Shared\Auth\IdentityRoleGuard;
use Database\Seeders\DevelopmentUserSeeder;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

it('provisions development users idempotently', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    expect(App\Models\User::query()->where('email', DevelopmentUserSeeder::EMPLOYEE_EMAIL)->exists())->toBeTrue()
        ->and(App\Models\User::query()->where('email', DevelopmentUserSeeder::APPROVER_EMAIL)->exists())->toBeTrue()
        ->and(App\Models\User::query()->where('email', DevelopmentUserSeeder::ADMIN_EMAIL)->exists())->toBeTrue()
        ->and(App\Models\User::query()->where('email', DevelopmentUserSeeder::MANAGER_EMAIL)->exists())->toBeTrue();
});

it('grants identity employee role to the employee development user', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $identity = UserModel::query()->where('email', DevelopmentUserSeeder::EMPLOYEE_EMAIL)->firstOrFail();

    expect(IdentityRoleGuard::userHasIdentityRole($identity, IdentityRoleSeeder::ROLE_EMPLOYEE))->toBeTrue()
        ->and(Role::query()->where('name', IdentityRoleSeeder::ROLE_EMPLOYEE)->where('guard_name', 'identity')->count())->toBe(1);
});

it('provisions a dormitory-manager development user on the identity guard', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $identity = UserModel::query()->where('email', DevelopmentUserSeeder::MANAGER_EMAIL)->firstOrFail();

    expect(IdentityRoleGuard::userHasIdentityRole($identity, IdentityRoleGuard::ROLE_DORMITORY_MANAGER))->toBeTrue()
        ->and(IdentityRoleGuard::userHasIdentityRole($identity, IdentityRoleSeeder::ROLE_EMPLOYEE))->toBeFalse()
        ->and(Role::query()->where('name', IdentityRoleSeeder::ROLE_DORMITORY_MANAGER)->where('guard_name', 'identity')->count())->toBe(1)
        ->and(App\Models\User::query()->where('email', DevelopmentUserSeeder::MANAGER_EMAIL)->count())->toBe(1);
});

it('leaves existing admin and approver web roles unchanged after manager seed', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $admin = UserModel::query()->where('email', DevelopmentUserSeeder::ADMIN_EMAIL)->firstOrFail();
    $approver = UserModel::query()->where('email', DevelopmentUserSeeder::APPROVER_EMAIL)->firstOrFail();

    expect($admin->hasRole(IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR))->toBeTrue()
        ->and($approver->hasRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR))->toBeTrue()
        ->and(IdentityRoleGuard::userHasIdentityRole($admin, IdentityRoleGuard::ROLE_DORMITORY_MANAGER))->toBeFalse()
        ->and(IdentityRoleGuard::userHasIdentityRole($approver, IdentityRoleGuard::ROLE_DORMITORY_MANAGER))->toBeFalse();
});

it('allows the employee development user to log in and access requests', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $this->post('/login', [
        'identifier' => DevelopmentUserSeeder::EMPLOYEE_EMAIL,
        'password' => DevelopmentUserSeeder::EMPLOYEE_PASSWORD,
    ])->assertRedirect(route('requests.index'));

    $this->get('/requests')->assertOk()->assertSee('درخواست‌های من');
});

it('allows the manager development user to log in', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $this->post('/login', [
        'identifier' => DevelopmentUserSeeder::MANAGER_EMAIL,
        'password' => DevelopmentUserSeeder::MANAGER_PASSWORD,
    ])->assertRedirect(route('requests.index'));
});

it('allows the approver development user to log in', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $this->post('/login', [
        'identifier' => DevelopmentUserSeeder::APPROVER_EMAIL,
        'password' => DevelopmentUserSeeder::APPROVER_PASSWORD,
    ])->assertRedirect(route('requests.index'));
});

it('allows the admin development user to log in', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $this->post('/login', [
        'identifier' => DevelopmentUserSeeder::ADMIN_EMAIL,
        'password' => DevelopmentUserSeeder::ADMIN_PASSWORD,
    ])->assertRedirect(route('requests.index'));
});
