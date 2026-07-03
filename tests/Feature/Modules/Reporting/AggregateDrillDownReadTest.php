<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\DTOs\AggregateDrillDownQuery;
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
function seedDrillDownAuditEntry(array $overrides = []): AuditLogModel
{
    $entityId = $overrides['entityId'] ?? UuidGenerator::uuid7();
    $correlationId = $overrides['correlationId'] ?? 'drilldown:seed:'.UuidGenerator::uuid7();

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

function authenticateDrillDownReader(): UserModel
{
    $user = app(CreateUserAction::class)->execute('Drill Down Reader', 'drilldown-reader@example.com');
    app(AssignRoleToUserAction::class)->execute($user->requireId(), IdentityRoleSeeder::ROLE_HR_MGR);
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    return $model;
}

it('returns entity scoped drill down via T0 timeline path', function (): void {
    authenticateDrillDownReader();
    $entityId = UuidGenerator::uuid7();

    seedDrillDownAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'drilldown:001',
        'occurredAt' => '2026-07-02T09:00:00Z',
    ]);
    seedDrillDownAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'drilldown:002',
        'occurredAt' => '2026-07-02T11:00:00Z',
    ]);
    seedDrillDownAuditEntry([
        'entityId' => UuidGenerator::uuid7(),
        'correlationId' => 'drilldown:other',
    ]);

    $result = app(ReportingReadContract::class)->drillDown(new AggregateDrillDownQuery(
        entityType: 'request',
        entityId: $entityId,
    ));

    expect($result->total)->toBe(2);
    expect($result->items)->toHaveCount(2);
    expect($result->provenance->sourceTier)->toBe('T0');
    expect($result->provenance->refreshedAt)->toBeNull();
    expect($result->provenance->projectionVersion)->toBeNull();
});
