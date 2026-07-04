<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Contracts;

interface AuditEventTypeCatalogPort
{
    /**
     * @return list<string>
     */
    public function allEventTypeValues(): array;
}
