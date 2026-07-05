<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Services\AuditEventTypeCatalog;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Reporting\Application\Services\ReportingProjectionEventTypeCatalog;

it('exposes all audit event types for projection refresh ingest', function (): void {
    $catalog = new ReportingProjectionEventTypeCatalog(new AuditEventTypeCatalog());

    expect($catalog->allEventTypes())->toBe(array_map(
        static fn (AuditEventType $eventType): string => $eventType->value,
        AuditEventType::cases(),
    ));
});
