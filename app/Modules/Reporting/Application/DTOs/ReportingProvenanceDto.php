<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use DateTimeImmutable;

final readonly class ReportingProvenanceDto
{
    public function __construct(
        public string $sourceTier,
        public ?DateTimeImmutable $refreshedAt,
        public ?string $projectionVersion,
        public bool $includeArchived,
        public string $filterHash,
    ) {}
}
