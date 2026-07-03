<?php

declare(strict_types=1);

use App\Modules\Reporting\Application\DTOs\ReportingTimelineItemDto;
use App\Modules\Reporting\Application\Services\EntityTimelineSummaryBuilder;

it('builds empty summary when no items are returned', function (): void {
    $summary = (new EntityTimelineSummaryBuilder)->build([], totalCount: 0);

    expect($summary->totalCount)->toBe(0);
    expect($summary->pageItemCount)->toBe(0);
    expect($summary->firstOccurredAt)->toBeNull();
    expect($summary->lastOccurredAt)->toBeNull();
});

it('builds page span summary from timeline items', function (): void {
    $items = [
        new ReportingTimelineItemDto(
            auditLogId: '1',
            correlationId: 'c-1',
            eventType: 'request.approved',
            entityType: 'request',
            entityId: '01932f4a-7b2c-7000-8000-000000000001',
            actorType: 'user',
            actorId: '01932f4a-7b2c-7000-8000-000000000002',
            sourceContext: 'request',
            occurredAt: new DateTimeImmutable('2026-07-02T09:00:00Z'),
        ),
        new ReportingTimelineItemDto(
            auditLogId: '2',
            correlationId: 'c-2',
            eventType: 'request.approved',
            entityType: 'request',
            entityId: '01932f4a-7b2c-7000-8000-000000000001',
            actorType: 'user',
            actorId: '01932f4a-7b2c-7000-8000-000000000003',
            sourceContext: 'request',
            occurredAt: new DateTimeImmutable('2026-07-02T11:00:00Z'),
        ),
    ];

    $summary = (new EntityTimelineSummaryBuilder)->build($items, totalCount: 5);

    expect($summary->totalCount)->toBe(5);
    expect($summary->pageItemCount)->toBe(2);
    expect($summary->firstOccurredAt?->format(DateTimeImmutable::ATOM))->toBe('2026-07-02T09:00:00+00:00');
    expect($summary->lastOccurredAt?->format(DateTimeImmutable::ATOM))->toBe('2026-07-02T11:00:00+00:00');
});
