<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use DateTimeImmutable;

final readonly class ComplianceExportReadModel
{
    /**
     * @param  array<string, mixed>  $filterManifest
     * @param  list<string>  $lineItemSourceAuditLogIds
     * @param  list<ReportingTimelineItemDto>  $lineItems
     * @param  list<AuditWindowBucketDto>  $summaryBuckets
     */
    public function __construct(
        public string $snapshotId,
        public DateTimeImmutable $generatedAt,
        public array $filterManifest,
        public array $lineItemSourceAuditLogIds,
        public array $lineItems,
        public array $summaryBuckets,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage,
        public ReportingProvenanceDto $provenance,
    ) {}
}
