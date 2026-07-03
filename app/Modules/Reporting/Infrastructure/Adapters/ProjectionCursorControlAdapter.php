<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Adapters;

use App\Modules\Reporting\Application\Contracts\Ports\ProjectionCursorControlPort;
use App\Modules\Reporting\Application\DTOs\ProjectionCursorDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionCursorStatus;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\RefreshMode;
use App\Modules\Reporting\Domain\Exceptions\ProjectionCursorBusyException;
use App\Modules\Reporting\Infrastructure\Repositories\ProjectionCursorRepository;
use DateTimeImmutable;

final class ProjectionCursorControlAdapter implements ProjectionCursorControlPort
{
    public function __construct(
        private readonly ProjectionCursorRepository $cursorRepository,
    ) {}

    public function resolveCursor(
        ProjectionFamily $projectionFamily,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
        RefreshMode $refreshMode = RefreshMode::Incremental,
    ): ProjectionCursorDto {
        $existing = $this->cursorRepository->findByFamilyAndTier($projectionFamily, $archiveVisibilityTier);

        if ($existing !== null) {
            return $existing;
        }

        return $this->cursorRepository->create(
            $projectionFamily,
            $archiveVisibilityTier,
            $projectionVersion,
            $refreshMode,
        );
    }

    public function markRunning(string $cursorId): ProjectionCursorDto
    {
        $cursor = $this->requireCursor($cursorId);

        if ($cursor->status === ProjectionCursorStatus::Running) {
            throw ProjectionCursorBusyException::forCursor($cursorId);
        }

        return $this->cursorRepository->updateStatus($cursorId, ProjectionCursorStatus::Running);
    }

    public function advanceAfterSuccessfulBatch(
        string $cursorId,
        ?string $lastSourceAuditLogId,
        DateTimeImmutable $lastOccurredAt,
        string $projectionVersion,
    ): ProjectionCursorDto {
        return $this->cursorRepository->advance(
            $cursorId,
            $lastSourceAuditLogId,
            $lastOccurredAt,
            $projectionVersion,
        );
    }

    public function markFailed(string $cursorId, string $error): ProjectionCursorDto
    {
        return $this->cursorRepository->updateStatus(
            $cursorId,
            ProjectionCursorStatus::Failed,
            $error,
        );
    }

    private function requireCursor(string $cursorId): ProjectionCursorDto
    {
        $cursor = $this->cursorRepository->findById($cursorId);

        if ($cursor === null) {
            throw new \InvalidArgumentException("Projection cursor {$cursorId} not found.");
        }

        return $cursor;
    }
}
