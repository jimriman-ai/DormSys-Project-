<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;

final readonly class ProjectionRefreshBatchDto
{
    /**
     * @param  list<AuditHistoryItemDto>  $items
     */
    public function __construct(
        public ProjectionCursorDto $cursor,
        public array $items,
        public int $page,
        public int $lastPage,
        public int $total,
        public bool $hasMorePages,
    ) {}
}
