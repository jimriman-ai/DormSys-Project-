<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

it('allows role assignment when active actor holds identity.roles.manage and subject receives the role', function (): void {
    $actorId = createActiveMutationActorId('Authz Actor Allow');
    grantIdentityRolesManagePermission($actorId);
    $subject = createIdentityUserThroughMutation('Authz Subject Allow', 'authz-subject-allow@example.com');

    mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $subject->requireId(),
        IdentityRoleSeeder::ROLE_HR_MGR,
    ));

    expect(app(IdentityUserReadContract::class)->userHasRole(
        $subject->requireId()->value,
        IdentityRoleSeeder::ROLE_HR_MGR,
    ))->toBeTrue()
        ->and(app(IdentityUserReadContract::class)->userHasRole($actorId, IdentityRoleSeeder::ROLE_HR_MGR))->toBeFalse();
});

it('denies role assignment when active actor lacks identity.roles.manage and does not mutate subject', function (): void {
    $actorId = createActiveMutationActorId('Authz Actor Deny');
    $subject = createIdentityUserThroughMutation('Authz Subject Deny', 'authz-subject-deny@example.com');

    expect(fn () => mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $subject->requireId(),
        IdentityRoleSeeder::ROLE_HR_MGR,
    )))->toThrow(UnauthorizedMutationException::class);

    expect(app(IdentityUserReadContract::class)->userHasRole(
        $subject->requireId()->value,
        IdentityRoleSeeder::ROLE_HR_MGR,
    ))->toBeFalse();
});

it('denies role assign when actor is hrmgr without roles manage', function (): void {
    $actorId = createActiveMutationActorId('Authz Actor HRMgr');
    UserModel::query()->findOrFail($actorId)->assignRole(IdentityRoleSeeder::ROLE_HR_MGR);

    expect(app(IdentityUserReadContract::class)->userHasRole($actorId, IdentityRoleSeeder::ROLE_HR_MGR))->toBeTrue()
        ->and(app(IdentityUserReadContract::class)->userHasPermission(
            $actorId,
            IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE,
        ))->toBeFalse();

    $subject = createIdentityUserThroughMutation('Authz Subject HRMgr Actor', 'authz-subject-hrmgr-actor@example.com');

    expect(fn () => mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $subject->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    )))->toThrow(UnauthorizedMutationException::class);

    expect(app(IdentityUserReadContract::class)->userHasRole(
        $subject->requireId()->value,
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ))->toBeFalse();
});

it('denies role assignment when principal is missing and does not mutate subject', function (): void {
    $subject = createIdentityUserThroughMutation('Authz Subject Missing', 'authz-subject-missing@example.com');

    expect(fn () => app(AssignRoleToUserAction::class)->execute(
        $subject->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ))->toThrow(UnauthorizedMutationException::class);

    expect(app(IdentityUserReadContract::class)->userHasRole(
        $subject->requireId()->value,
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ))->toBeFalse();
});

it('denies role assignment for inactive actor even when identity.roles.manage is present', function (): void {
    $actorId = createActiveMutationActorId('Authz Actor Inactive');
    grantIdentityRolesManagePermission($actorId);
    $subject = createIdentityUserThroughMutation('Authz Subject Inactive', 'authz-subject-inactive@example.com');

    deactivateUserThroughMutation(
        UserId::fromString($actorId),
        createActiveMutationActorId('Second Deactivator'),
    );

    expect(fn () => mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $subject->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    )))->toThrow(UnauthorizedMutationException::class, 'Mutation actor must be an active identity user.');

    expect(app(IdentityUserReadContract::class)->userHasRole(
        $subject->requireId()->value,
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ))->toBeFalse();
});

it('denies subsequent assignment after identity.roles.manage is revoked', function (): void {
    $actorId = createActiveMutationActorId('Authz Actor Revoke Perm');
    grantIdentityRolesManagePermission($actorId);
    $subjectOne = createIdentityUserThroughMutation('Authz Subject One', 'authz-subject-one@example.com');
    $subjectTwo = createIdentityUserThroughMutation('Authz Subject Two', 'authz-subject-two@example.com');

    mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $subjectOne->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ));

    UserModel::query()->findOrFail($actorId)
        ->revokePermissionTo(IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE);

    expect(fn () => mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $subjectTwo->requireId(),
        IdentityRoleSeeder::ROLE_HR_MGR,
    )))->toThrow(UnauthorizedMutationException::class);

    expect(app(IdentityUserReadContract::class)->userHasRole(
        $subjectOne->requireId()->value,
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ))->toBeTrue()
        ->and(app(IdentityUserReadContract::class)->userHasRole(
            $subjectTwo->requireId()->value,
            IdentityRoleSeeder::ROLE_HR_MGR,
        ))->toBeFalse();
});

it('keeps identity.role.assign as capability key while still requiring identity.roles.manage on actor', function (): void {
    expect(MutationCapabilityCatalog::IDENTITY_ROLE_ASSIGN)->toBe('identity.role.assign')
        ->and(IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE)->toBe('identity.roles.manage')
        ->and(MutationCapabilityCatalog::IDENTITY_ROLE_ASSIGN)->not->toBe(IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE);

    $actorId = createActiveMutationActorId('Authz Actor Cap Key');
    $subject = createIdentityUserThroughMutation('Authz Subject Cap Key', 'authz-subject-cap@example.com');

    expect(fn () => mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $subject->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    )))->toThrow(UnauthorizedMutationException::class);
});

it('fails closed when identity.roles.manage permission catalog row is missing', function (): void {
    Permission::query()
        ->where('name', IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE)
        ->where('guard_name', 'web')
        ->delete();

    $actorId = createActiveMutationActorId('Authz Actor Missing Row');
    $subject = createIdentityUserThroughMutation('Authz Subject Missing Row', 'authz-subject-missing-row@example.com');

    // Direct give would recreate the row; simulate prior stale direct grant without catalog:
    expect(app(IdentityUserReadContract::class)->userHasPermission(
        $actorId,
        IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE,
    ))->toBeFalse();

    expect(fn () => mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $subject->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    )))->toThrow(UnauthorizedMutationException::class);

    expect(app(IdentityUserReadContract::class)->userHasRole(
        $subject->requireId()->value,
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    ))->toBeFalse();
});

it('checks authority against the actor and assigns the role only to the subject', function (): void {
    $actorId = createActiveMutationActorId('Authz Distinct Actor');
    grantIdentityRolesManagePermission($actorId);
    $subject = createIdentityUserThroughMutation('Authz Distinct Subject', 'authz-distinct-subject@example.com');

    expect($actorId)->not->toBe($subject->requireId()->value);

    mutationActingAs($actorId, fn () => app(AssignRoleToUserAction::class)->execute(
        $subject->requireId(),
        IdentityRoleSeeder::ROLE_DORM_MGR,
    ));

    expect(app(IdentityUserReadContract::class)->userHasRole(
        $subject->requireId()->value,
        IdentityRoleSeeder::ROLE_DORM_MGR,
    ))->toBeTrue()
        ->and(app(IdentityUserReadContract::class)->userHasRole($actorId, IdentityRoleSeeder::ROLE_DORM_MGR))->toBeFalse()
        ->and(app(IdentityUserReadContract::class)->userHasPermission(
            $actorId,
            IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE,
        ))->toBeTrue()
        ->and(app(IdentityUserReadContract::class)->userHasPermission(
            $subject->requireId()->value,
            IdentityRoleSeeder::PERMISSION_IDENTITY_ROLES_MANAGE,
        ))->toBeFalse();
});
