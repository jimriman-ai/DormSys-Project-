<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditEventTypeCatalogPort;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Reporting\Application\Services\ReportingProjectionEventTypeCatalog;

it('exposes all audit event types for projection refresh ingest', function (): void {
    $expected = array_map(
        static fn (AuditEventType $eventType): string => $eventType->value,
        AuditEventType::cases(),
    );

    $eventTypeCatalog = Mockery::mock(AuditEventTypeCatalogPort::class);
    $eventTypeCatalog->shouldReceive('allEventTypeValues')->once()->andReturn($expected);

    $catalog = new ReportingProjectionEventTypeCatalog($eventTypeCatalog);

    expect($catalog->allEventTypes())->toBe($expected);
});
