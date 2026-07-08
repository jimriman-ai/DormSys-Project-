<?php

declare(strict_types=1);

use Database\Seeders\DevelopmentUserSeeder;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

it('provisions development users idempotently', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    expect(App\Models\User::query()->where('email', DevelopmentUserSeeder::EMPLOYEE_EMAIL)->exists())->toBeTrue()
        ->and(App\Models\User::query()->where('email', DevelopmentUserSeeder::APPROVER_EMAIL)->exists())->toBeTrue()
        ->and(App\Models\User::query()->where('email', DevelopmentUserSeeder::ADMIN_EMAIL)->exists())->toBeTrue();
});

it('allows the employee development user to log in and access requests', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $this->post('/login', [
        'identifier' => DevelopmentUserSeeder::EMPLOYEE_EMAIL,
        'password' => DevelopmentUserSeeder::EMPLOYEE_PASSWORD,
    ])->assertRedirect(route('requests.index'));

    $this->get('/requests')->assertOk()->assertSee('درخواست‌های من');
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
