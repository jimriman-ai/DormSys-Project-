<?php

declare(strict_types=1);

use App\Auth\StudentRecordsPolicyEnforcementPoint;
use App\Http\Requests\EditStudentRecordRequest;
use App\Models\User;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

/**
 * Bind a FormRequest with the same user resolver the HTTP kernel uses,
 * so authorize()/user($guard) consult Auth guards without a full route dispatch.
 */
function makeEditStudentRecordRequest(): EditStudentRecordRequest
{
    $request = EditStudentRecordRequest::create('/api/test-student-edit', 'POST');
    $request->setContainer(app());
    $request->setRedirector(app('redirect'));
    $request->setUserResolver(fn (?string $guard = null) => Auth::guard($guard)->user());

    return $request;
}

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

it('denies edit when user is null', function (): void {
    expect(app(StudentRecordsPolicyEnforcementPoint::class)->canEdit(null))->toBeFalse();
});

it('rejects wrong principal type on pep', function (): void {
    $credentialUser = User::factory()->create([
        'email' => 'wrong-type-pep@example.com',
    ]);

    expect(fn () => app(StudentRecordsPolicyEnforcementPoint::class)->canEdit($credentialUser))
        ->toThrow(TypeError::class);
});

it('denies edit authorize when credential web user is authenticated', function (): void {
    $credentialUser = User::factory()->create([
        'email' => 'web-guard-denied@example.com',
    ]);
    $this->actingAs($credentialUser, 'web');

    expect(makeEditStudentRecordRequest()->authorize())->toBeFalse();
});

it('authorize returns false for unauthenticated form request', function (): void {
    // guest denied by FormRequest::authorize(); distinct from auth:api middleware 401
    Auth::guard('api')->logout();
    Auth::guard('web')->logout();

    expect(makeEditStudentRecordRequest()->authorize())->toBeFalse();
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

it('rejects api student edit when only web guard authenticated', function (): void {
    Route::middleware(['api', 'auth:api'])->post('/api/test-student-edit', function (EditStudentRecordRequest $request) {
        return response('ok');
    });

    $credentialUser = User::factory()->create([
        'email' => 'web-only-api-denied@example.com',
    ]);

    $this->actingAs($credentialUser, 'web')
        ->postJson('/api/test-student-edit')
        ->assertUnauthorized();
});
