<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionCursorControlPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshInputPort;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshRequestDto;
use App\Modules\Reporting\Application\Services\ReportingProjectionEventTypeCatalog;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    $user = UserModel::query()->create([
        'display_name' => 'Projection Ingest Reader',
        'email' => 'projection-ingest-reader@example.com',
        'status' => UserStatus::Active,
    ]);
    $user->assignRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    request()->attributes->set('audit_principal_user_id', $user->id);
});

function recordProjectionIngestAuditEntry(DateTimeImmutable $occurredAt, string $suffix = ''): string
{
    $entityId = UuidGenerator::uuid7();

    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray([
        'correlationId' => 'reporting:ingest:'.$suffix.UuidGenerator::uuid7(),
        'eventType' => AuditEventType::RequestSubmitted->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => $occurredAt->format(DateTimeInterface::ATOM),
    ]));

    return $entityId;
}

it('does not starve perPage=1 projection refresh after cursor advancement', function (): void {
    recordProjectionIngestAuditEntry(new DateTimeImmutable('2026-07-01T09:00:00+00:00'), 'a');
    recordProjectionIngestAuditEntry(new DateTimeImmutable('2026-07-01T11:00:00+00:00'), 'b');

    $refresh = app(ProjectionRefreshInputPort::class);
    $control = app(ProjectionCursorControlPort::class);

    $firstBatch = $refresh->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
        perPage: 1,
    ));

    expect($firstBatch->items)->toHaveCount(1);
    expect($firstBatch->items[0]->occurredAt->format(DateTimeInterface::ATOM))->toBe('2026-07-01T09:00:00+00:00');

    $firstItem = $firstBatch->items[0];
    $control->advanceAfterSuccessfulBatch(
        $firstBatch->cursor->id,
        $firstItem->auditLogId,
        $firstItem->occurredAt,
        '1.0.0',
    );

    $secondBatch = $refresh->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
        perPage: 1,
    ));

    expect($secondBatch->items)->toHaveCount(1);
    expect($secondBatch->items[0]->occurredAt->format(DateTimeInterface::ATOM))->toBe('2026-07-01T11:00:00+00:00');
    expect($secondBatch->items[0]->auditLogId)->not->toBe($firstItem->auditLogId);
});

it('handles identical occurred_at values with deterministic id tie-breaker during projection refresh', function (): void {
    $sharedOccurredAt = new DateTimeImmutable('2026-07-01T10:00:00+00:00');
    recordProjectionIngestAuditEntry($sharedOccurredAt, 'first');
    recordProjectionIngestAuditEntry($sharedOccurredAt, 'second');

    $refresh = app(ProjectionRefreshInputPort::class);
    $control = app(ProjectionCursorControlPort::class);

    $firstBatch = $refresh->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
        perPage: 1,
    ));

    expect($firstBatch->items)->toHaveCount(1);

    $firstItem = $firstBatch->items[0];
    $control->advanceAfterSuccessfulBatch(
        $firstBatch->cursor->id,
        $firstItem->auditLogId,
        $firstItem->occurredAt,
        '1.0.0',
    );

    $secondBatch = $refresh->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
        perPage: 1,
    ));

    expect($secondBatch->items)->toHaveCount(1);
    expect($secondBatch->items[0]->auditLogId)->not->toBe($firstItem->auditLogId);
    expect($secondBatch->items[0]->occurredAt->format(DateTimeInterface::ATOM))->toBe('2026-07-01T10:00:00+00:00');
});

it('handles identical occurred_at values with descending id tie-breaker cursor pagination', function (): void {
    $entityId = UuidGenerator::uuid7();
    $sharedOccurredAt = new DateTimeImmutable('2026-07-01T10:00:00+00:00');

    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray([
        'correlationId' => 'reporting:desc-cursor:'.UuidGenerator::uuid7(),
        'eventType' => AuditEventType::RequestSubmitted->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => $sharedOccurredAt->format(DateTimeInterface::ATOM),
    ]));
    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray([
        'correlationId' => 'reporting:desc-cursor:'.UuidGenerator::uuid7(),
        'eventType' => AuditEventType::RequestSubmitted->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => $sharedOccurredAt->format(DateTimeInterface::ATOM),
    ]));

    $firstPage = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: $entityId,
        page: 1,
        perPage: 1,
    ));

    expect($firstPage->items)->toHaveCount(1);

    $cursorItem = $firstPage->items[0];

    $secondPage = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: $entityId,
        occurredFrom: $cursorItem->occurredAt,
        occurredFromExclusiveAuditLogId: $cursorItem->auditLogId,
        page: 1,
        perPage: 1,
    ));

    expect($secondPage->items)->toHaveCount(1);
    expect($secondPage->items[0]->auditLogId)->not->toBe($cursorItem->auditLogId);
    expect($secondPage->items[0]->occurredAt->format(DateTimeInterface::ATOM))->toBe('2026-07-01T10:00:00+00:00');
});

it('keeps default audit history reads in descending occurred_at order', function (): void {
    $entityId = UuidGenerator::uuid7();

    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray([
        'correlationId' => 'reporting:history:'.UuidGenerator::uuid7(),
        'eventType' => AuditEventType::RequestSubmitted->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => '2026-07-01T09:00:00+00:00',
    ]));
    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray([
        'correlationId' => 'reporting:history:'.UuidGenerator::uuid7(),
        'eventType' => AuditEventType::RequestSubmitted->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => '2026-07-01T11:00:00+00:00',
    ]));

    $result = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: $entityId,
    ));

    expect($result->items[0]->occurredAt->format(DateTimeInterface::ATOM))->toBe('2026-07-01T11:00:00+00:00');
    expect($result->items[1]->occurredAt->format(DateTimeInterface::ATOM))->toBe('2026-07-01T09:00:00+00:00');
});

it('uses ascending ordering only for projection refresh queries', function (): void {
    recordProjectionIngestAuditEntry(new DateTimeImmutable('2026-07-01T09:00:00+00:00'), 'asc-check-a');
    recordProjectionIngestAuditEntry(new DateTimeImmutable('2026-07-01T11:00:00+00:00'), 'asc-check-b');

    $projectionBatch = app(ProjectionRefreshInputPort::class)->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
        perPage: 1,
    ));

    $catalog = app(ReportingProjectionEventTypeCatalog::class)->allEventTypes();
    $timeline = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        eventTypes: $catalog,
        page: 1,
        perPage: 1,
    ));

    expect($projectionBatch->items[0]->occurredAt->format(DateTimeInterface::ATOM))->toBe('2026-07-01T09:00:00+00:00');
    expect($timeline->items[0]->occurredAt->format(DateTimeInterface::ATOM))->toBe('2026-07-01T11:00:00+00:00');
});
