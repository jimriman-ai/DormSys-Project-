<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

function employeeRecordsActor(string $displayName, string $email, string $role): UserModel
{
    User::factory()->create([
        'email' => $email,
        'password' => 'secret-password',
    ]);

    $identity = createIdentityUserThroughMutation($displayName, $email);
    $model = UserModel::query()->findOrFail($identity->requireId()->value);
    $model->assignRole($role);

    return $model;
}

it('forbids employee record edit without employee_records.edit on production route', function (): void {
    $actor = employeeRecordsActor(
        'No Edit Actor',
        'no-edit-employee-records@example.com',
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    );

    $this->actingAs($actor, 'api')
        ->putJson('/employee-records/'.UuidGenerator::uuid7(), [
            'first_name' => 'Denied',
        ])
        ->assertForbidden();
});

it('allows employee record edit for HRMgr with employee_records.edit on production route', function (): void {
    $actor = employeeRecordsActor(
        'HR Edit Actor',
        'hr-edit-employee-records@example.com',
        IdentityRoleSeeder::ROLE_HR_MGR,
    );

    $id = UuidGenerator::uuid7();

    $this->actingAs($actor, 'api')
        ->putJson('/employee-records/'.$id, [
            'first_name' => 'Allowed',
            'last_name' => 'Editor',
        ])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('id', $id)
        ->assertJsonPath('data.first_name', 'Allowed');
});

it('redirects unauthenticated employee record edit to login via middleware', function (): void {
    $this->put('/employee-records/'.UuidGenerator::uuid7(), [
        'first_name' => 'Guest',
    ])->assertRedirect('/login');
});

it('forbids employee record read without employee_records.read on production route', function (): void {
    $actor = employeeRecordsActor(
        'No Read Actor',
        'no-read-employee-records@example.com',
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    );

    $this->actingAs($actor, 'api')
        ->getJson('/employee-records/'.UuidGenerator::uuid7())
        ->assertForbidden();
});

it('allows employee record read for HRMgr with employee_records.read on production route', function (): void {
    $actor = employeeRecordsActor(
        'HR Read Actor',
        'hr-read-employee-records@example.com',
        IdentityRoleSeeder::ROLE_HR_MGR,
    );

    $id = UuidGenerator::uuid7();

    $this->actingAs($actor, 'api')
        ->getJson('/employee-records/'.$id)
        ->assertOk()
        ->assertJsonPath('id', $id);
});
