<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Contracts\Ports;

use App\Modules\Reporting\Application\DTOs\ProjectionCursorDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\RefreshMode;
use DateTimeImmutable;

interface ProjectionCursorControlPort
{
    public function resolveCursor(
        ProjectionFamily $projectionFamily,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
        RefreshMode $refreshMode = RefreshMode::Incremental,
    ): ProjectionCursorDto;

    public function markRunning(string $cursorId): ProjectionCursorDto;

    public function advanceAfterSuccessfulBatch(
        string $cursorId,
        ?string $lastSourceAuditLogId,
        DateTimeImmutable $lastOccurredAt,
        string $projectionVersion,
    ): ProjectionCursorDto;

    public function markFailed(string $cursorId, string $error): ProjectionCursorDto;
}
