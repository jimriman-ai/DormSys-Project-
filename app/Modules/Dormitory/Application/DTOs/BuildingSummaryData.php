<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Read-only building list projection for Phase 3A.
 */
final readonly class BuildingSummaryData
{
    public function __construct(
        public string $id,
        public string $dormitoryId,
        public string $code,
        public string $name,
        public string $status,
    ) {}
}
