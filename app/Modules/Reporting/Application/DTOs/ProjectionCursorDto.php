<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\DTOs;

use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionCursorStatus;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\RefreshMode;
use DateTimeImmutable;

final readonly class ProjectionCursorDto
{
    public function __construct(
        public string $id,
        public ProjectionFamily $projectionFamily,
        public ArchiveVisibilityTier $archiveVisibilityTier,
        public ?string $lastSourceAuditLogId,
        public ?DateTimeImmutable $lastOccurredAt,
        public string $projectionVersion,
        public ?DateTimeImmutable $refreshedAt,
        public RefreshMode $refreshMode,
        public ProjectionCursorStatus $status,
        public ?string $lastError,
    ) {}
}
