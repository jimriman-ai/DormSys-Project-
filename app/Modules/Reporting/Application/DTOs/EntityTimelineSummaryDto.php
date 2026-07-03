<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use DateTimeImmutable;

final readonly class EntityTimelineSummaryDto
{
    public function __construct(
        public int $totalCount,
        public int $pageItemCount,
        public ?DateTimeImmutable $firstOccurredAt,
        public ?DateTimeImmutable $lastOccurredAt,
    ) {}
}
