<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use DateTimeImmutable;

final readonly class ComplianceExportManifestDto
{
    /**
     * @param  array<string, mixed>  $filterManifest
     * @param  list<string>  $lineItemSourceAuditLogIds
     */
    public function __construct(
        public string $snapshotId,
        public DateTimeImmutable $generatedAt,
        public array $filterManifest,
        public array $lineItemSourceAuditLogIds,
        public ?DateTimeImmutable $refreshedAt,
        public ?string $projectionVersion,
        public int $manifestLineItemCount,
    ) {}
}
