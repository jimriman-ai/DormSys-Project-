<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\DTOs;

/**
 * Request-facing dormitory site option for listSites() (WP-UI-C-01-B / DBT-1).
 */
final readonly class DormitorySiteSummaryDTO
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public string $status,
    ) {}
}
