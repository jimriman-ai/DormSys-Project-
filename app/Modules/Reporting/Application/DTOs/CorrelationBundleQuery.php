<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

final readonly class CorrelationBundleQuery
{
    /**
     * @param  list<string>|null  $eventTypes
     */
    public function __construct(
        public string $correlationId,
        public bool $includeArchived = false,
        public ?array $eventTypes = null,
    ) {}
}
