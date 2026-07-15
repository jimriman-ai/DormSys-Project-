<?php

declare(strict_types=1);

use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

function authenticateIdentityActor(): UserModel
{
    $actor = createIdentityUserThroughMutation('Identity Audit Actor', 'identity-audit-actor@example.com');
    assignRoleThroughMutation(
        $actor->requireId(),
        IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR,
        $actor->requireId()->value,
    );
    $model = UserModel::query()->findOrFail($actor->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    return $model;
}

it('records identity user created audit entry with mutation actor when helper establishes principal', function (): void {
    $user = createIdentityUserThroughMutation('Created Audit User', 'created-audit@example.com');
    $userId = $user->requireId()->value;

    $entry = AuditLogModel::query()
        ->where('entity_type', 'identity_user')
        ->where('entity_id', $userId)
        ->where('event_type', AuditEventType::IdentityUserCreated)
        ->first();

    expect($entry)->not->toBeNull();
    $entry = $entry ?? throw new RuntimeException('Identity user created audit entry not found');
    expect($entry->source_context)->toBe('identity');
    expect($entry->actor_type)->toBe(ActorType::User);
    expect($entry->actor_id)->not->toBe('system:scheduler');
    expect($entry->correlation_id)->toBe('identity:identity_user:'.$userId.':identity.user_created:created');
    expect($entry->new_values)->toBe(['status' => 'active']);
});

it('records identity role changed audit entry with authenticated actor', function (): void {
    $actor = authenticateIdentityActor();

    $target = createIdentityUserThroughMutation('Role Target User', 'role-target@example.com');

    mutationActingAs($actor->id, fn () => assignRoleThroughMutation(
        $target->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
        $actor->id,
    ));

    $entry = AuditLogModel::query()
        ->where('entity_type', 'identity_user')
        ->where('entity_id', $target->requireId()->value)
        ->where('event_type', AuditEventType::IdentityRoleChanged)
        ->where('correlation_id', 'identity:identity_user:'.$target->requireId()->value.':identity.role_changed:assigned:'.IdentityRoleSeeder::ROLE_ADMINISTRATOR)
        ->first();

    expect($entry)->not->toBeNull();
    $entry = $entry ?? throw new RuntimeException('Identity role changed audit entry not found');
    expect($entry->actor_type)->toBe(ActorType::User);
    expect($entry->actor_id)->toBe($actor->id);
    expect($entry->metadata)->toMatchArray(['assignmentAction' => 'assigned']);
    expect($entry->new_values)->toBe(['role' => IdentityRoleSeeder::ROLE_ADMINISTRATOR]);
});

it('records identity role revoked and user deactivated audit entries', function (): void {
    $actor = authenticateIdentityActor();

    $adminOne = createIdentityUserThroughMutation('Admin One', 'admin-one@example.com');
    assignRoleThroughMutation($adminOne->requireId(), IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR, $actor->id);

    $adminTwo = createIdentityUserThroughMutation('Admin Two', 'admin-two@example.com');
    assignRoleThroughMutation($adminTwo->requireId(), IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR, $actor->id);

    $target = createIdentityUserThroughMutation('Deactivate Target', 'deactivate-target@example.com');
    assignRoleThroughMutation($target->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR, $actor->id);

    revokeRoleFromUserThroughMutation($target->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR, $actor->id);

    $revoked = AuditLogModel::query()
        ->where('entity_id', $target->requireId()->value)
        ->where('correlation_id', 'identity:identity_user:'.$target->requireId()->value.':identity.role_changed:revoked:'.IdentityRoleSeeder::ROLE_ADMINISTRATOR)
        ->first();

    expect($revoked)->not->toBeNull();
    $revoked = $revoked ?? throw new RuntimeException('Identity role revoked audit entry not found');
    expect($revoked->old_values)->toBe(['role' => IdentityRoleSeeder::ROLE_ADMINISTRATOR]);

    deactivateUserThroughMutation($target->requireId(), $actor->id);

    $deactivated = AuditLogModel::query()
        ->where('entity_id', $target->requireId()->value)
        ->where('event_type', AuditEventType::IdentityUserDeactivated)
        ->first();

    expect($deactivated)->not->toBeNull();
    $deactivated = $deactivated ?? throw new RuntimeException('Identity user deactivated audit entry not found');
    expect($deactivated->old_values)->toBe(['status' => 'active']);
    expect($deactivated->new_values)->toBe(['status' => 'disabled']);
});

it('records role created updated deleted and user roles synced audit entries', function (): void {
    $actor = authenticateIdentityActor();
    grantIdentityRolesManagePermission($actor->id);

    $created = mutationActingAs(
        $actor->id,
        fn () => app(App\Modules\Identity\Application\Services\CreateRoleAction::class)->execute('AuditSurfaceRole'),
    );

    $createdEntry = AuditLogModel::query()
        ->where('event_type', AuditEventType::RoleCreated)
        ->where('entity_type', 'identity_role')
        ->where('actor_id', $actor->id)
        ->first();

    expect($createdEntry)->not->toBeNull();
    expect($createdEntry?->new_values)->toMatchArray(['name' => 'AuditSurfaceRole', 'roleId' => $created->id]);

    mutationActingAs(
        $actor->id,
        fn () => app(App\Modules\Identity\Application\Services\RenameRoleAction::class)->execute($created->id, 'AuditSurfaceRoleRenamed'),
    );

    $updatedEntry = AuditLogModel::query()
        ->where('event_type', AuditEventType::RoleUpdated)
        ->where('actor_id', $actor->id)
        ->first();

    expect($updatedEntry)->not->toBeNull();
    expect($updatedEntry?->old_values)->toBe(['name' => 'AuditSurfaceRole']);
    expect($updatedEntry?->new_values)->toMatchArray(['name' => 'AuditSurfaceRoleRenamed']);

    mutationActingAs(
        $actor->id,
        fn () => app(App\Modules\Identity\Application\Services\DeleteRoleAction::class)->execute($created->id),
    );

    $deletedEntry = AuditLogModel::query()
        ->where('event_type', AuditEventType::RoleDeleted)
        ->where('actor_id', $actor->id)
        ->first();

    expect($deletedEntry)->not->toBeNull();
    expect($deletedEntry?->old_values)->toMatchArray(['name' => 'AuditSurfaceRoleRenamed', 'roleId' => $created->id]);

    $target = createIdentityUserThroughMutation('Sync Audit Target', 'sync-audit-target@example.com');
    $hrId = (int) Spatie\Permission\Models\Role::query()
        ->where('name', IdentityRoleSeeder::ROLE_HR_MGR)
        ->where('guard_name', 'web')
        ->value('id');

    mutationActingAs(
        $actor->id,
        fn () => app(App\Modules\Identity\Application\Services\SyncUserRolesAction::class)->execute(
            $target->requireId()->value,
            [$hrId],
        ),
    );

    $synced = AuditLogModel::query()
        ->where('event_type', AuditEventType::UserRolesSynced)
        ->where('entity_id', $target->requireId()->value)
        ->where('actor_id', $actor->id)
        ->first();

    expect($synced)->not->toBeNull();
    expect($synced?->new_values)->toBe(['roles' => [IdentityRoleSeeder::ROLE_HR_MGR]]);
});
