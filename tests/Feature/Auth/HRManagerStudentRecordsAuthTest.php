<?php

declare(strict_types=1);

use App\Auth\StudentRecordsPolicyEnforcementPoint;
use App\Http\Requests\EditStudentRecordRequest;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

it('allows read for HRMgr role', function (): void {
    $user = createIdentityUserThroughMutation('HR Reader', 'hr-reader@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_HR_MGR);

    expect(app(StudentRecordsPolicyEnforcementPoint::class)->canRead($model))->toBeTrue();
});

it('allows edit for HRMgr role', function (): void {
    $user = createIdentityUserThroughMutation('HR Editor', 'hr-editor@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_HR_MGR);

    expect(app(StudentRecordsPolicyEnforcementPoint::class)->canEdit($model))->toBeTrue();
});

it('denies read for non-HRMgr role', function (): void {
    $user = createIdentityUserThroughMutation('Employee Reader', 'employee-reader@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR);

    expect(app(StudentRecordsPolicyEnforcementPoint::class)->canRead($model))->toBeFalse();
});

it('denies edit for non-HRMgr role', function (): void {
    $user = createIdentityUserThroughMutation('Employee Editor', 'employee-editor@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR);

    expect(app(StudentRecordsPolicyEnforcementPoint::class)->canEdit($model))->toBeFalse();
});

it('denies read when unauthenticated', function (): void {
    expect(app(StudentRecordsPolicyEnforcementPoint::class)->canRead(null))->toBeFalse();
});

it('http api guard allows student edit for HRMgr', function (): void {
    Route::middleware(['api', 'auth:api'])->post('/api/test-student-edit', function (EditStudentRecordRequest $request) {
        return response('ok');
    });

    $user = createIdentityUserThroughMutation('HTTP HR Editor', 'http-hr-editor@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->actingAs($model, 'api')
        ->postJson('/api/test-student-edit')
        ->assertOk();
});

it('http api guard forbids student edit without student_records permission', function (): void {
    Route::middleware(['api', 'auth:api'])->post('/api/test-student-edit', function (EditStudentRecordRequest $request) {
        return response('ok');
    });

    $user = createIdentityUserThroughMutation('HTTP No Perm', 'http-no-perm@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR);

    $this->actingAs($model, 'api')
        ->postJson('/api/test-student-edit')
        ->assertForbidden();
});

it('http api guard rejects unauthenticated student edit', function (): void {
    Route::middleware(['api', 'auth:api'])->post('/api/test-student-edit', function (EditStudentRecordRequest $request) {
        return response('ok');
    });

    $this->postJson('/api/test-student-edit')
        ->assertUnauthorized();
});
