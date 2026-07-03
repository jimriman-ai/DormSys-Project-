<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

final readonly class SecurityAuditEventReadModel
{
    /**
     * @param  list<ActorActivitySummaryItemDto>  $summaries
     */
    public function __construct(
        public array $summaries,
        public ReportingProvenanceDto $provenance,
    ) {}
}
