<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditEventTypeCatalogPort;

final class ReportingProjectionEventTypeCatalog
{
    public function __construct(
        private readonly AuditEventTypeCatalogPort $eventTypeCatalog,
    ) {}

    /**
     * @return list<string>
     */
    public function allEventTypes(): array
    {
        return $this->eventTypeCatalog->allEventTypeValues();
    }
}
