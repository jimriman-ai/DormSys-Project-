<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\DTOs;

final readonly class PaginatedRequestSummaryListDTO
{
    /**
     * @param  list<RequestSummaryDTO>  $items
     * @param  list<string>  $statusOptions
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $currentPage,
        public int $perPage,
        public int $lastPage,
        public array $statusOptions,
    ) {}
}
