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
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshRunnerPort;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\DTOs\ComplianceExportQuery;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshRequestDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    $user = app(CreateUserAction::class)->execute('Compliance Export Reader', 'compliance-export@example.com');
    app(AssignRoleToUserAction::class)->execute($user->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function seedComplianceExportAuditEntry(array $overrides = []): AuditLogModel
{
    $payload = array_merge([
        'correlationId' => 'reporting:export:'.UuidGenerator::uuid7(),
        'eventType' => AuditEventType::RequestSubmitted->value,
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => '2026-07-01T10:00:00+00:00',
    ], $overrides);

    $result = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($payload));

    return AuditLogModel::query()->findOrFail($result->auditLogId);
}

function materializeComplianceExportProjections(): void
{
    $runner = app(ProjectionRefreshRunnerPort::class);
    $request = new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    );

    $runner->runBatch($request);

    $runner->runBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::WindowAggregate,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    ));
}

it('reads RU-04 compliance export with mixed provenance and T0 line items', function (): void {
    $auditLog = seedComplianceExportAuditEntry([
        'correlationId' => 'reporting:ru04:export',
    ]);

    materializeComplianceExportProjections();

    $windowStart = new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC'));
    $windowEnd = new DateTimeImmutable('2026-07-02 00:00:00', new DateTimeZone('UTC'));

    $result = app(ReportingReadContract::class)->complianceExport(new ComplianceExportQuery(
        windowStart: $windowStart,
        windowEnd: $windowEnd,
        granularity: WindowGranularity::Day,
        eventType: AuditEventType::RequestSubmitted->value,
        sourceContext: 'request',
        actorType: ActorType::User->value,
        entityType: 'request',
    ));

    expect($result->snapshotId)->not->toBe('');
    expect($result->filterManifest)->toHaveKey('sourceTierMix', 'T1_manifest + T0_line_items');
    expect($result->provenance->sourceTier)->toBe('mixed');
    expect($result->provenance->projectionVersion)->toBe('1.0.0');
    expect($result->lineItemSourceAuditLogIds)->not->toBeEmpty();
    expect($result->lineItems)->not->toBeEmpty();
    expect($result->lineItems[0]->auditLogId)->toBe((string) $auditLog->id);
    expect($result->summaryBuckets)->not->toBeEmpty();
    expect($result->total)->toBeGreaterThan(0);
});

it('resolves RU-04 export line items from T0 authoritative audit history', function (): void {
    $auditLog = seedComplianceExportAuditEntry();

    materializeComplianceExportProjections();

    $windowStart = new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC'));
    $windowEnd = new DateTimeImmutable('2026-07-02 00:00:00', new DateTimeZone('UTC'));

    $result = app(ReportingReadContract::class)->complianceExport(new ComplianceExportQuery(
        windowStart: $windowStart,
        windowEnd: $windowEnd,
    ));

    $lineItemIds = array_map(static fn ($item) => $item->auditLogId, $result->lineItems);

    expect($lineItemIds)->toContain((string) $auditLog->id);
    expect($result->lineItems[0]->eventType)->toBe(AuditEventType::RequestSubmitted->value);
});

it('returns stable empty RU-04 compliance export when no manifest rows exist', function (): void {
    $windowStart = new DateTimeImmutable('2099-01-01 00:00:00', new DateTimeZone('UTC'));
    $windowEnd = new DateTimeImmutable('2099-01-02 00:00:00', new DateTimeZone('UTC'));

    $result = app(ReportingReadContract::class)->complianceExport(new ComplianceExportQuery(
        windowStart: $windowStart,
        windowEnd: $windowEnd,
    ));

    expect($result->lineItemSourceAuditLogIds)->toBe([]);
    expect($result->lineItems)->toBe([]);
    expect($result->summaryBuckets)->toBe([]);
    expect($result->total)->toBe(0);
    expect($result->provenance->sourceTier)->toBe('mixed');
});

it('does not duplicate RU-04 export line items for a single manifest audit log id', function (): void {
    seedComplianceExportAuditEntry();

    materializeComplianceExportProjections();

    $windowStart = new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC'));
    $windowEnd = new DateTimeImmutable('2026-07-02 00:00:00', new DateTimeZone('UTC'));

    $result = app(ReportingReadContract::class)->complianceExport(new ComplianceExportQuery(
        windowStart: $windowStart,
        windowEnd: $windowEnd,
    ));

    $manifestIds = $result->lineItemSourceAuditLogIds;
    $lineItemIds = array_map(static fn ($item) => $item->auditLogId, $result->lineItems);

    expect($manifestIds)->toBe(array_values(array_unique($manifestIds)));
    expect($lineItemIds)->toBe(array_values(array_unique($lineItemIds)));
    expect(count($lineItemIds))->toBeLessThanOrEqual(count($manifestIds));
});
