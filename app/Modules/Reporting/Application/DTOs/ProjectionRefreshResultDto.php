<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use App\Modules\Reporting\Domain\Enums\ProjectionCursorStatus;

final readonly class ProjectionRefreshResultDto
{
    public function __construct(
        public ProjectionCursorDto $cursor,
        public int $itemsFetched,
        public int $itemsMaterialized,
        public bool $hasMorePages,
        public ProjectionCursorStatus $status,
    ) {}
}
