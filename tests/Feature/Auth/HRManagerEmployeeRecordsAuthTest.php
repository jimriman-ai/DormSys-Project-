<?php

declare(strict_types=1);

use App\Auth\EmployeeRecordsPolicyEnforcementPoint;
use App\Http\Requests\EditEmployeeRecordRequest;
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
function makeEditEmployeeRecordRequest(): EditEmployeeRecordRequest
{
    $request = EditEmployeeRecordRequest::create('/api/test-employee-edit', 'POST');
    $request->setContainer(app());
    $request->setRedirector(app('redirect'));
    $request->setUserResolver(fn (?string $guard = null) => Auth::guard($guard)->user());

    return $request;
}

it('allows read for HRMgr role', function (): void {
    $user = createIdentityUserThroughMutation('HR Reader', 'hr-reader@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_HR_MGR);

    expect(app(EmployeeRecordsPolicyEnforcementPoint::class)->canRead($model))->toBeTrue();
});

it('allows edit for HRMgr role', function (): void {
    $user = createIdentityUserThroughMutation('HR Editor', 'hr-editor@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_HR_MGR);

    expect(app(EmployeeRecordsPolicyEnforcementPoint::class)->canEdit($model))->toBeTrue();
});

it('denies read for non-HRMgr role', function (): void {
    $user = createIdentityUserThroughMutation('Employee Reader', 'employee-reader@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR);

    expect(app(EmployeeRecordsPolicyEnforcementPoint::class)->canRead($model))->toBeFalse();
});

it('denies edit for non-HRMgr role', function (): void {
    $user = createIdentityUserThroughMutation('Employee Editor', 'employee-editor@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR);

    expect(app(EmployeeRecordsPolicyEnforcementPoint::class)->canEdit($model))->toBeFalse();
});

it('denies read when unauthenticated', function (): void {
    expect(app(EmployeeRecordsPolicyEnforcementPoint::class)->canRead(null))->toBeFalse();
});

it('denies edit when user is null', function (): void {
    expect(app(EmployeeRecordsPolicyEnforcementPoint::class)->canEdit(null))->toBeFalse();
});

it('rejects wrong principal type on pep', function (): void {
    $credentialUser = User::factory()->create([
        'email' => 'wrong-type-pep@example.com',
    ]);

    // Credential User is intentional: PEP requires Identity UserModel; invoke via Reflection
    // so PHPStan does not treat this call site as a typed canEdit(?UserModel) violation.
    $pep = app(EmployeeRecordsPolicyEnforcementPoint::class);
    $canEdit = new ReflectionMethod(EmployeeRecordsPolicyEnforcementPoint::class, 'canEdit');

    expect(fn () => $canEdit->invoke($pep, $credentialUser))
        ->toThrow(TypeError::class);
});

it('denies edit authorize when credential web user is authenticated', function (): void {
    $credentialUser = User::factory()->create([
        'email' => 'web-guard-denied@example.com',
    ]);
    $this->actingAs($credentialUser, 'web');

    expect(makeEditEmployeeRecordRequest()->authorize())->toBeFalse();
});

it('authorize returns false for unauthenticated form request', function (): void {
    // guest denied by FormRequest::authorize(); distinct from auth:api middleware 401
    Auth::guard('api')->logout();
    Auth::guard('web')->logout();

    expect(makeEditEmployeeRecordRequest()->authorize())->toBeFalse();
});

it('http api guard allows employee edit for HRMgr', function (): void {
    Route::middleware(['api', 'auth:api'])->post('/api/test-employee-edit', function (EditEmployeeRecordRequest $request) {
        return response('ok');
    });

    $user = createIdentityUserThroughMutation('HTTP HR Editor', 'http-hr-editor@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_HR_MGR);

    $this->actingAs($model, 'api')
        ->postJson('/api/test-employee-edit')
        ->assertOk();
});

it('http api guard forbids employee edit without employee_records permission', function (): void {
    Route::middleware(['api', 'auth:api'])->post('/api/test-employee-edit', function (EditEmployeeRecordRequest $request) {
        return response('ok');
    });

    $user = createIdentityUserThroughMutation('HTTP No Perm', 'http-no-perm@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $model->assignRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR);

    $this->actingAs($model, 'api')
        ->postJson('/api/test-employee-edit')
        ->assertForbidden();
});

it('http api guard rejects unauthenticated employee edit', function (): void {
    Route::middleware(['api', 'auth:api'])->post('/api/test-employee-edit', function (EditEmployeeRecordRequest $request) {
        return response('ok');
    });

    $this->postJson('/api/test-employee-edit')
        ->assertUnauthorized();
});

it('rejects api employee edit when only web guard authenticated', function (): void {
    Route::middleware(['api', 'auth:api'])->post('/api/test-employee-edit', function (EditEmployeeRecordRequest $request) {
        return response('ok');
    });

    $credentialUser = User::factory()->create([
        'email' => 'web-only-api-denied@example.com',
    ]);

    $this->actingAs($credentialUser, 'web')
        ->postJson('/api/test-employee-edit')
        ->assertUnauthorized();
});
