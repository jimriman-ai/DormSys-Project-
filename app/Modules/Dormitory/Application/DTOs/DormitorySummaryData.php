<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Read-only dormitory list projection for Phase 3A.
 */
final readonly class DormitorySummaryData
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public string $status,
    ) {}
}
