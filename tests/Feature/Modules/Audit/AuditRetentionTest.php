<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Infrastructure\Jobs\ArchiveExpiredAuditLogsJob;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction as IdentityAssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config([
        'audit.sync_in_tests' => true,
        'audit.retention_months' => 84,
        'audit.activity_bridge_enabled' => false,
    ]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function seedRetentionAuditEntry(array $overrides = []): AuditLogModel
{
    $entityId = $overrides['entityId'] ?? UuidGenerator::uuid7();
    $correlationId = $overrides['correlationId'] ?? 'retention:seed:'.UuidGenerator::uuid7();

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

function authenticateRetentionAuditReader(): void
{
    $user = app(CreateUserAction::class)->execute('Retention Reader', 'retention-reader@example.com');
    app(IdentityAssignRoleToUserAction::class)->execute($user->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);
}

it('archives expired audit logs and excludes them from default history queries', function (): void {
    authenticateRetentionAuditReader();
    $entityId = UuidGenerator::uuid7();

    $expired = seedRetentionAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'retention:expired:001',
    ]);
    $recent = seedRetentionAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'retention:recent:001',
    ]);

    DB::table('audit_logs')
        ->where('id', $expired->id)
        ->update(['occurred_at' => now()->subMonths(85)]);

    Bus::dispatchSync(new ArchiveExpiredAuditLogsJob);

    expect(AuditLogModel::query()->findOrFail($expired->id)->archived_at)->not->toBeNull();
    expect(AuditLogModel::query()->findOrFail($recent->id)->archived_at)->toBeNull();

    $result = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: $entityId,
    ));

    expect($result->total)->toBe(1);
    expect($result->items[0]->correlationId)->toBe('retention:recent:001');
});

it('does not hard delete archived audit logs', function (): void {
    authenticateRetentionAuditReader();

    $auditLog = seedRetentionAuditEntry(['correlationId' => 'retention:persist:001']);

    DB::table('audit_logs')
        ->where('id', $auditLog->id)
        ->update(['occurred_at' => now()->subMonths(90)]);

    Bus::dispatchSync(new ArchiveExpiredAuditLogsJob);

    expect(AuditLogModel::query()->find($auditLog->id))->not->toBeNull();
    expect(DB::table('audit_logs')->where('id', $auditLog->id)->count())->toBe(1);
});

it('archives idempotently when the job runs more than once', function (): void {
    authenticateRetentionAuditReader();

    $auditLog = seedRetentionAuditEntry(['correlationId' => 'retention:idempotent:001']);

    DB::table('audit_logs')
        ->where('id', $auditLog->id)
        ->update(['occurred_at' => now()->subMonths(90)]);

    Bus::dispatchSync(new ArchiveExpiredAuditLogsJob);
    $firstArchivedAt = AuditLogModel::query()->findOrFail($auditLog->id)->archived_at;

    Bus::dispatchSync(new ArchiveExpiredAuditLogsJob);
    $secondArchivedAt = AuditLogModel::query()->findOrFail($auditLog->id)->archived_at;

    expect($firstArchivedAt)->not->toBeNull();
    expect($secondArchivedAt)->not->toBeNull();
    if ($firstArchivedAt === null || $secondArchivedAt === null) {
        throw new UnexpectedValueException('Expected archived timestamps.');
    }

    expect($secondArchivedAt->equalTo($firstArchivedAt))->toBeTrue();
});

it('keeps activity bridge disabled by default', function (): void {
    expect((bool) config('audit.activity_bridge_enabled'))->toBeFalse();
});

it('runs audit archive command idempotently', function (): void {
    authenticateRetentionAuditReader();

    $auditLog = seedRetentionAuditEntry(['correlationId' => 'retention:command:001']);

    DB::table('audit_logs')
        ->where('id', $auditLog->id)
        ->update(['occurred_at' => now()->subMonths(90)]);

    Artisan::call('audit:archive-expired');
    Artisan::call('audit:archive-expired');

    expect(AuditLogModel::query()->findOrFail($auditLog->id)->archived_at)->not->toBeNull();
    expect(Artisan::output())->toContain('Expired audit logs archived.');
});
