<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

final readonly class AuditWindowSummaryReadModel
{
    /**
     * @param  list<AuditWindowBucketDto>  $buckets
     */
    public function __construct(
        public array $buckets,
        public ReportingProvenanceDto $provenance,
    ) {}
}
