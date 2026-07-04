<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Services;

use App\Modules\Audit\Application\Contracts\AuditEventTypeCatalogPort;
use App\Modules\Audit\Domain\Enums\AuditEventType;

final class AuditEventTypeCatalog implements AuditEventTypeCatalogPort
{
    /**
     * @return list<string>
     */
    public function allEventTypeValues(): array
    {
        return array_map(
            static fn (AuditEventType $eventType): string => $eventType->value,
            AuditEventType::cases(),
        );
    }
}
