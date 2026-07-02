<?php

declare(strict_types=1);

use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Application\Services\RevokeRoleFromUserAction;
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
    $actor = app(CreateUserAction::class)->execute('Identity Audit Actor', 'identity-audit-actor@example.com');
    app(AssignRoleToUserAction::class)->execute($actor->requireId(), IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR);
    $model = UserModel::query()->findOrFail($actor->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    return $model;
}

it('records identity user created audit entry with system actor when no principal is set', function (): void {
    $user = app(CreateUserAction::class)->execute('Created Audit User', 'created-audit@example.com');
    $userId = $user->requireId()->value;

    $entry = AuditLogModel::query()
        ->where('entity_type', 'identity_user')
        ->where('entity_id', $userId)
        ->where('event_type', AuditEventType::IdentityUserCreated)
        ->first();

    expect($entry)->not->toBeNull();
    expect($entry->source_context)->toBe('identity');
    expect($entry->actor_type)->toBe(ActorType::System);
    expect($entry->actor_id)->toBe('system:scheduler');
    expect($entry->correlation_id)->toBe('identity:identity_user:'.$userId.':identity.user_created:created');
    expect($entry->new_values)->toBe(['status' => 'active']);
});

it('records identity role changed audit entry with authenticated actor', function (): void {
    $actor = authenticateIdentityActor();

    $target = app(CreateUserAction::class)->execute('Role Target User', 'role-target@example.com');

    app(AssignRoleToUserAction::class)->execute(
        $target->requireId(),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    );

    $entry = AuditLogModel::query()
        ->where('entity_type', 'identity_user')
        ->where('entity_id', $target->requireId()->value)
        ->where('event_type', AuditEventType::IdentityRoleChanged)
        ->where('correlation_id', 'identity:identity_user:'.$target->requireId()->value.':identity.role_changed:assigned:'.IdentityRoleSeeder::ROLE_ADMINISTRATOR)
        ->first();

    expect($entry)->not->toBeNull();
    expect($entry->actor_type)->toBe(ActorType::User);
    expect($entry->actor_id)->toBe($actor->id);
    expect($entry->metadata)->toMatchArray(['assignmentAction' => 'assigned']);
    expect($entry->new_values)->toBe(['role' => IdentityRoleSeeder::ROLE_ADMINISTRATOR]);
});

it('records identity role revoked and user deactivated audit entries', function (): void {
    authenticateIdentityActor();

    $adminOne = app(CreateUserAction::class)->execute('Admin One', 'admin-one@example.com');
    app(AssignRoleToUserAction::class)->execute($adminOne->requireId(), IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR);

    $adminTwo = app(CreateUserAction::class)->execute('Admin Two', 'admin-two@example.com');
    app(AssignRoleToUserAction::class)->execute($adminTwo->requireId(), IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR);

    $target = app(CreateUserAction::class)->execute('Deactivate Target', 'deactivate-target@example.com');
    app(AssignRoleToUserAction::class)->execute($target->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR);

    app(RevokeRoleFromUserAction::class)->execute($target->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR);

    $revoked = AuditLogModel::query()
        ->where('entity_id', $target->requireId()->value)
        ->where('correlation_id', 'identity:identity_user:'.$target->requireId()->value.':identity.role_changed:revoked:'.IdentityRoleSeeder::ROLE_ADMINISTRATOR)
        ->first();

    expect($revoked)->not->toBeNull();
    expect($revoked->old_values)->toBe(['role' => IdentityRoleSeeder::ROLE_ADMINISTRATOR]);

    app(DeactivateUserAction::class)->execute($target->requireId());

    $deactivated = AuditLogModel::query()
        ->where('entity_id', $target->requireId()->value)
        ->where('event_type', AuditEventType::IdentityUserDeactivated)
        ->first();

    expect($deactivated)->not->toBeNull();
    expect($deactivated->old_values)->toBe(['status' => 'active']);
    expect($deactivated->new_values)->toBe(['status' => 'disabled']);
});
