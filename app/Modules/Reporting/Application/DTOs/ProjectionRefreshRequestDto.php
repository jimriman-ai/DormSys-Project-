<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\RefreshMode;

final readonly class ProjectionRefreshRequestDto
{
    public function __construct(
        public ProjectionFamily $projectionFamily,
        public ArchiveVisibilityTier $archiveVisibilityTier,
        public string $projectionVersion,
        public RefreshMode $refreshMode = RefreshMode::Incremental,
        public int $page = 1,
        public int $perPage = 200,
    ) {
        if ($page < 1) {
            throw new \InvalidArgumentException('page must be at least 1.');
        }

        if ($perPage < 1 || $perPage > 200) {
            throw new \InvalidArgumentException('perPage must be between 1 and 200.');
        }
    }
}
