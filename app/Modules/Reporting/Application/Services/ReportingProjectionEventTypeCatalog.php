<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Audit\Domain\Enums\AuditEventType;

final class ReportingProjectionEventTypeCatalog
{
    /**
     * @return list<string>
     */
    public function allEventTypes(): array
    {
        return array_map(
            static fn (AuditEventType $eventType): string => $eventType->value,
            AuditEventType::cases(),
        );
    }
}
