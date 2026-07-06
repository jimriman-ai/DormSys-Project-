<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\Exceptions\UnauthorizedAuditAccessException;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\DTOs\EntityTimelineQuery;
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
function seedReportingAuditEntry(array $overrides = []): AuditLogModel
{
    $entityId = $overrides['entityId'] ?? UuidGenerator::uuid7();
    $correlationId = $overrides['correlationId'] ?? 'reporting:seed:'.UuidGenerator::uuid7();

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

function authenticateReportingReader(string $role = IdentityRoleSeeder::ROLE_ADMINISTRATOR): UserModel
{
    $user = createIdentityUserThroughMutation('Reporting Reader', 'reporting-reader@example.com');
    assignRoleThroughMutation($user->requireId(), $role);
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    return $model;
}

it('returns entity audit timeline with T0 provenance for authorized readers', function (): void {
    authenticateReportingReader();
    $entityId = UuidGenerator::uuid7();

    seedReportingAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'reporting:timeline:001',
        'occurredAt' => '2026-07-02T09:00:00Z',
    ]);
    seedReportingAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'reporting:timeline:002',
        'occurredAt' => '2026-07-02T11:00:00Z',
    ]);

    $result = app(ReportingReadContract::class)->entityTimeline(new EntityTimelineQuery(
        entityType: 'request',
        entityId: $entityId,
    ));

    expect($result->total)->toBe(2);
    expect($result->items)->toHaveCount(2);
    expect($result->provenance->sourceTier)->toBe('T0');
    expect($result->provenance->refreshedAt)->toBeNull();
    expect($result->provenance->projectionVersion)->toBeNull();
    expect($result->eventTypeHistogram)->toHaveKey(AuditEventType::RequestApproved->value);
    expect($result->summary->totalCount)->toBe(2);
    expect($result->summary->pageItemCount)->toBe(2);
    expect($result->items[0]->correlationId)->toBe('reporting:timeline:002');
    expect($result->summary->lastOccurredAt?->format('Y-m-d\TH:i:s'))->toBe('2026-07-02T11:00:00');
});

it('filters entity timeline by event type', function (): void {
    authenticateReportingReader();
    $entityId = UuidGenerator::uuid7();

    seedReportingAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'reporting:filter:event:001',
        'eventType' => AuditEventType::RequestApproved->value,
    ]);
    seedReportingAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'reporting:filter:event:002',
        'eventType' => AuditEventType::RequestRejected->value,
    ]);

    $result = app(ReportingReadContract::class)->entityTimeline(new EntityTimelineQuery(
        entityType: 'request',
        entityId: $entityId,
        eventTypes: [AuditEventType::RequestRejected->value],
    ));

    expect($result->total)->toBe(1);
    expect($result->items[0]->eventType)->toBe(AuditEventType::RequestRejected->value);
});

it('filters entity timeline by occurred date range', function (): void {
    authenticateReportingReader();
    $entityId = UuidGenerator::uuid7();

    seedReportingAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'reporting:filter:range:001',
        'occurredAt' => '2026-07-01T10:00:00Z',
    ]);
    seedReportingAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'reporting:filter:range:002',
        'occurredAt' => '2026-07-03T10:00:00Z',
    ]);

    $result = app(ReportingReadContract::class)->entityTimeline(new EntityTimelineQuery(
        entityType: 'request',
        entityId: $entityId,
        occurredFrom: new DateTimeImmutable('2026-07-02T00:00:00Z'),
        occurredTo: new DateTimeImmutable('2026-07-02T23:59:59Z'),
    ));

    expect($result->total)->toBe(0);
    expect($result->items)->toBe([]);
    expect($result->summary->pageItemCount)->toBe(0);
});

it('paginates entity timeline results', function (): void {
    authenticateReportingReader();
    $entityId = UuidGenerator::uuid7();

    for ($index = 1; $index <= 3; $index++) {
        seedReportingAuditEntry([
            'entityId' => $entityId,
            'correlationId' => 'reporting:page:'.$index,
            'occurredAt' => sprintf('2026-07-02T1%d:00:00Z', $index),
        ]);
    }

    $result = app(ReportingReadContract::class)->entityTimeline(new EntityTimelineQuery(
        entityType: 'request',
        entityId: $entityId,
        page: 1,
        perPage: 2,
    ));

    expect($result->total)->toBe(3);
    expect($result->items)->toHaveCount(2);
    expect($result->lastPage)->toBe(2);
    expect($result->summary->totalCount)->toBe(3);
    expect($result->summary->pageItemCount)->toBe(2);
});

it('denies entity timeline access without audit.read permission', function (): void {
    $entityId = UuidGenerator::uuid7();
    seedReportingAuditEntry(['entityId' => $entityId, 'correlationId' => 'reporting:deny:001']);

    $user = createIdentityUserThroughMutation('No Reporting Access', 'no-reporting@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    expect(fn () => app(ReportingReadContract::class)->entityTimeline(new EntityTimelineQuery(
        entityType: 'request',
        entityId: $entityId,
    )))->toThrow(UnauthorizedAuditAccessException::class);
});

it('denies include archived without audit.read permission', function (): void {
    $user = createIdentityUserThroughMutation('No Archive Access', 'no-archive@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    $entityId = UuidGenerator::uuid7();

    seedReportingAuditEntry(['entityId' => $entityId, 'correlationId' => 'reporting:archived:001']);

    expect(fn () => app(ReportingReadContract::class)->entityTimeline(new EntityTimelineQuery(
        entityType: 'request',
        entityId: $entityId,
        includeArchived: true,
    )))->toThrow(UnauthorizedAuditAccessException::class);
});

it('includes archived rows for audit.read holders when requested', function (): void {
    authenticateReportingReader(IdentityRoleSeeder::ROLE_DORM_MGR);
    $entityId = UuidGenerator::uuid7();

    $active = seedReportingAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'reporting:active:001',
    ]);

    $archived = seedReportingAuditEntry([
        'entityId' => $entityId,
        'correlationId' => 'reporting:archived:002',
    ]);
    $archived->archived_at = now();
    $archived->saveQuietly();

    $result = app(ReportingReadContract::class)->entityTimeline(new EntityTimelineQuery(
        entityType: 'request',
        entityId: $entityId,
        includeArchived: true,
    ));

    expect($result->total)->toBe(2);
    expect($result->provenance->includeArchived)->toBeTrue();
    expect(collect($result->items)->pluck('auditLogId')->all())->toContain((string) $active->id, (string) $archived->id);
});
