<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\Exceptions\UnauthorizedAuditAccessException;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function seedAuditEntry(array $overrides = []): AuditLogModel
{
    $entityId = $overrides['entityId'] ?? UuidGenerator::uuid7();
    $correlationId = $overrides['correlationId'] ?? 'audit:seed:'.UuidGenerator::uuid7();

    $payload = array_merge([
        'correlationId' => $correlationId,
        'eventType' => AuditEventType::RequestApproved->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'oldValues' => ['status' => 'pending'],
        'newValues' => ['status' => 'approved'],
        'occurredAt' => '2026-07-02T10:00:00Z',
    ], $overrides);

    $result = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($payload));

    return AuditLogModel::query()->findOrFail($result->auditLogId);
}

function authenticateAuditReader(string $role = IdentityRoleSeeder::ROLE_ADMINISTRATOR): UserModel
{
    $user = createIdentityUserThroughMutation('Audit Reader', 'audit-reader@example.com');
    assignRoleThroughMutation($user->requireId(), $role);
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    return $model;
}

it('returns entity audit history for authorized readers', function (): void {
    authenticateAuditReader();
    $entityId = UuidGenerator::uuid7();

    seedAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'entity:history:001',
        'occurredAt' => '2026-07-02T09:00:00Z',
    ]);
    seedAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'entity:history:002',
        'occurredAt' => '2026-07-02T11:00:00Z',
    ]);

    $result = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: $entityId,
    ));

    expect($result->total)->toBe(2);
    expect($result->items)->toHaveCount(2);
    expect($result->items[0]->correlationId)->toBe('entity:history:002');
    expect($result->items[1]->correlationId)->toBe('entity:history:001');
});

it('filters audit history by actor', function (): void {
    authenticateAuditReader();
    $actorId = UuidGenerator::uuid7();

    seedAuditEntry([
        'correlationId' => 'actor:filter:001',
        'actorId' => $actorId,
    ]);
    seedAuditEntry([
        'correlationId' => 'actor:filter:002',
        'actorId' => UuidGenerator::uuid7(),
    ]);

    $result = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        actorType: ActorType::User->value,
        actorId: $actorId,
    ));

    expect($result->total)->toBe(1);
    expect($result->items[0]->actorId)->toBe($actorId);
});

it('paginates audit history results', function (): void {
    authenticateAuditReader();
    $entityId = UuidGenerator::uuid7();

    for ($index = 1; $index <= 3; $index++) {
        seedAuditEntry([
            'entityId' => $entityId,
            'correlationId' => 'page:'.$index,
            'occurredAt' => sprintf('2026-07-02T1%d:00:00Z', $index),
        ]);
    }

    $result = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: $entityId,
        page: 1,
        perPage: 2,
    ));

    expect($result->total)->toBe(3);
    expect($result->items)->toHaveCount(2);
    expect($result->lastPage)->toBe(2);
});

it('returns an empty result set when no audit rows match', function (): void {
    authenticateAuditReader();

    $result = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: UuidGenerator::uuid7(),
    ));

    expect($result->total)->toBe(0);
    expect($result->items)->toBe([]);
});

it('excludes archived rows from default queries', function (): void {
    authenticateAuditReader();
    $entityId = UuidGenerator::uuid7();

    $active = seedAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'active:001',
    ]);

    $archived = seedAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'archived:001',
    ]);
    $archived->archived_at = now();
    $archived->saveQuietly();

    $result = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: $entityId,
    ));

    expect($result->total)->toBe(1);
    expect($result->items[0]->auditLogId)->toBe((string) $active->id);
});

it('denies audit history access to users without audit.read permission', function (): void {
    $entityId = UuidGenerator::uuid7();
    seedAuditEntry(['entityId' => $entityId, 'correlationId' => 'deny:001']);

    $user = createIdentityUserThroughMutation('No Audit Access', 'no-audit@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    expect(fn () => app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: $entityId,
    )))->toThrow(UnauthorizedAuditAccessException::class);
});

it('grants audit history access to dorm manager role', function (): void {
    authenticateAuditReader(IdentityRoleSeeder::ROLE_DORM_MGR);
    $entityId = UuidGenerator::uuid7();
    seedAuditEntry(['entityId' => $entityId, 'correlationId' => 'dormmgr:001']);

    $result = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: $entityId,
    ));

    expect($result->total)->toBe(1);
});
