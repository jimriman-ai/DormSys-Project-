<?php

declare(strict_types=1);

use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionCursorStatus;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\RefreshMode;
use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use App\Modules\Reporting\Infrastructure\Persistence\Models\ActorActivitySummaryModel;
use App\Modules\Reporting\Infrastructure\Persistence\Models\AuditWindowAggregateModel;
use App\Modules\Reporting\Infrastructure\Persistence\Models\CorrelationProjectionEntryModel;
use App\Modules\Reporting\Infrastructure\Persistence\Models\ProjectionCursorModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('runs reporting T1 projection migrations', function (): void {
    expect(Schema::hasTable('reporting_projection_cursors'))->toBeTrue();
    expect(Schema::hasTable('reporting_correlation_projection_entries'))->toBeTrue();
    expect(Schema::hasTable('reporting_audit_window_aggregates'))->toBeTrue();
    expect(Schema::hasTable('reporting_actor_activity_summaries'))->toBeTrue();
});

it('persists a projection cursor row', function (): void {
    $cursor = ProjectionCursorModel::query()->create([
        'projection_family' => ProjectionFamily::Correlation,
        'archive_visibility_tier' => ArchiveVisibilityTier::ActiveOnly,
        'projection_version' => '1.0.0',
        'refresh_mode' => RefreshMode::Incremental,
        'status' => ProjectionCursorStatus::Idle,
    ]);

    expect($cursor->id)->not->toBeEmpty();
    expect($cursor->projection_family)->toBe(ProjectionFamily::Correlation);
    expect($cursor->status)->toBe(ProjectionCursorStatus::Idle);
});

it('persists a correlation projection entry row', function (): void {
    $auditLogId = UuidGenerator::uuid7();
    $entityId = UuidGenerator::uuid7();
    $now = now()->utc();

    $entry = CorrelationProjectionEntryModel::query()->create([
        'correlation_id' => 'corr-'.UuidGenerator::uuid7(),
        'source_audit_log_id' => $auditLogId,
        'occurred_at' => $now,
        'entity_type' => 'request',
        'entity_id' => $entityId,
        'actor_type' => 'user',
        'actor_id' => UuidGenerator::uuid7(),
        'event_type' => 'request.created',
        'source_context' => 'request',
        'archive_visibility_tier' => ArchiveVisibilityTier::ActiveOnly,
        'ingested_at' => $now,
    ]);

    expect($entry->id)->not->toBeEmpty();
    expect($entry->source_audit_log_id)->toBe($auditLogId);
});

it('persists an audit window aggregate row', function (): void {
    $windowStart = now()->utc()->startOfDay();
    $windowEnd = $windowStart->copy()->addDay();
    $refreshedAt = now()->utc();

    $aggregate = AuditWindowAggregateModel::query()->create([
        'window_start' => $windowStart,
        'window_end' => $windowEnd,
        'granularity' => WindowGranularity::Day,
        'archive_visibility_tier' => ArchiveVisibilityTier::ActiveOnly,
        'event_count' => 5,
        'distinct_entity_count' => 3,
        'distinct_actor_count' => 2,
        'top_event_types' => ['request.created' => 3, 'request.approved' => 2],
        'refreshed_at' => $refreshedAt,
        'projection_version' => '1.0.0',
    ]);

    expect($aggregate->id)->not->toBeEmpty();
    expect($aggregate->event_count)->toBe(5);
    expect($aggregate->top_event_types)->toBe(['request.created' => 3, 'request.approved' => 2]);
});

it('persists an actor activity summary row', function (): void {
    $windowStart = now()->utc()->startOfDay();
    $windowEnd = $windowStart->copy()->addDay();
    $refreshedAt = now()->utc();

    $summary = ActorActivitySummaryModel::query()->create([
        'actor_type' => 'user',
        'actor_id' => UuidGenerator::uuid7(),
        'window_start' => $windowStart,
        'window_end' => $windowEnd,
        'granularity' => WindowGranularity::Day,
        'event_count' => 10,
        'distinct_event_types' => ['request.created', 'request.approved'],
        'distinct_entities_touched' => 4,
        'archive_visibility_tier' => ArchiveVisibilityTier::ActiveOnly,
        'refreshed_at' => $refreshedAt,
        'projection_version' => '1.0.0',
    ]);

    expect($summary->id)->not->toBeEmpty();
    expect($summary->distinct_event_types)->toBe(['request.created', 'request.approved']);
});
