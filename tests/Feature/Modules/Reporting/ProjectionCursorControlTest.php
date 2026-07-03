<?php

declare(strict_types=1);

use App\Modules\Reporting\Application\Contracts\Ports\ProjectionCursorControlPort;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionCursorStatus;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\RefreshMode;
use App\Modules\Reporting\Domain\Exceptions\ProjectionCursorBusyException;
use App\Modules\Reporting\Infrastructure\Repositories\ProjectionCursorRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves and advances a projection cursor after a successful batch', function (): void {
    $control = app(ProjectionCursorControlPort::class);

    $cursor = $control->resolveCursor(
        ProjectionFamily::Correlation,
        ArchiveVisibilityTier::ActiveOnly,
        '1.0.0',
    );

    expect($cursor->status)->toBe(ProjectionCursorStatus::Idle);
    expect($cursor->lastSourceAuditLogId)->toBeNull();

    $running = $control->markRunning($cursor->id);
    expect($running->status)->toBe(ProjectionCursorStatus::Running);

    $advanced = $control->advanceAfterSuccessfulBatch(
        $cursor->id,
        'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        new DateTimeImmutable('2026-07-01T12:00:00+00:00'),
        '1.0.0',
    );

    expect($advanced->status)->toBe(ProjectionCursorStatus::Idle);
    expect($advanced->lastSourceAuditLogId)->toBe('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');
    expect($advanced->refreshedAt)->not->toBeNull();
});

it('rejects concurrent running state on the same cursor', function (): void {
    $control = app(ProjectionCursorControlPort::class);

    $cursor = $control->resolveCursor(
        ProjectionFamily::WindowAggregate,
        ArchiveVisibilityTier::ActiveOnly,
        '1.0.0',
    );

    $control->markRunning($cursor->id);

    expect(fn () => $control->markRunning($cursor->id))
        ->toThrow(ProjectionCursorBusyException::class);
});

it('marks a cursor as failed with diagnostics', function (): void {
    $control = app(ProjectionCursorControlPort::class);

    $cursor = $control->resolveCursor(
        ProjectionFamily::ActorActivity,
        ArchiveVisibilityTier::ActiveOnly,
        '1.0.0',
    );

    $failed = $control->markFailed($cursor->id, 'ingest timeout');

    expect($failed->status)->toBe(ProjectionCursorStatus::Failed);
    expect($failed->lastError)->toBe('ingest timeout');
});

it('reuses an existing cursor for the same family and visibility tier', function (): void {
    $repository = app(ProjectionCursorRepository::class);

    $first = $repository->create(
        ProjectionFamily::Correlation,
        ArchiveVisibilityTier::IncludeArchived,
        '1.0.0',
        RefreshMode::Incremental,
    );

    $second = app(ProjectionCursorControlPort::class)->resolveCursor(
        ProjectionFamily::Correlation,
        ArchiveVisibilityTier::IncludeArchived,
        '1.0.0',
    );

    expect($second->id)->toBe($first->id);
});
