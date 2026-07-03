<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Repositories;

use App\Modules\Reporting\Application\DTOs\ProjectionCursorDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionCursorStatus;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\RefreshMode;
use App\Modules\Reporting\Infrastructure\Persistence\Models\ProjectionCursorModel;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Carbon;

final class ProjectionCursorRepository
{
    public function findById(string $cursorId): ?ProjectionCursorDto
    {
        $model = ProjectionCursorModel::query()->find($cursorId);

        return $model === null ? null : $this->toDto($model);
    }

    public function findByFamilyAndTier(
        ProjectionFamily $projectionFamily,
        ArchiveVisibilityTier $archiveVisibilityTier,
    ): ?ProjectionCursorDto {
        $model = ProjectionCursorModel::query()
            ->where('projection_family', $projectionFamily->value)
            ->where('archive_visibility_tier', $archiveVisibilityTier->value)
            ->first();

        return $model === null ? null : $this->toDto($model);
    }

    public function create(
        ProjectionFamily $projectionFamily,
        ArchiveVisibilityTier $archiveVisibilityTier,
        string $projectionVersion,
        RefreshMode $refreshMode,
    ): ProjectionCursorDto {
        $model = ProjectionCursorModel::query()->create([
            'projection_family' => $projectionFamily,
            'archive_visibility_tier' => $archiveVisibilityTier,
            'projection_version' => $projectionVersion,
            'refresh_mode' => $refreshMode,
            'status' => ProjectionCursorStatus::Idle,
        ]);

        return $this->toDto($model);
    }

    public function updateStatus(
        string $cursorId,
        ProjectionCursorStatus $status,
        ?string $lastError = null,
    ): ProjectionCursorDto {
        $model = $this->findModelOrFail($cursorId);
        $model->status = $status;
        $model->last_error = $lastError;
        $model->save();

        return $this->toDto($model);
    }

    public function advance(
        string $cursorId,
        ?string $lastSourceAuditLogId,
        DateTimeImmutable $lastOccurredAt,
        string $projectionVersion,
    ): ProjectionCursorDto {
        $model = $this->findModelOrFail($cursorId);
        $model->last_source_audit_log_id = $lastSourceAuditLogId;
        $model->last_occurred_at = Carbon::instance($lastOccurredAt);
        $model->projection_version = $projectionVersion;
        $model->refreshed_at = Carbon::instance(new DateTimeImmutable('now', new DateTimeZone('UTC')));
        $model->status = ProjectionCursorStatus::Idle;
        $model->last_error = null;
        $model->save();

        return $this->toDto($model);
    }

    private function findModelOrFail(string $cursorId): ProjectionCursorModel
    {
        $model = ProjectionCursorModel::query()->find($cursorId);

        if ($model === null) {
            throw new \InvalidArgumentException("Projection cursor {$cursorId} not found.");
        }

        return $model;
    }

    private function toDto(ProjectionCursorModel $model): ProjectionCursorDto
    {
        return new ProjectionCursorDto(
            id: (string) $model->id,
            projectionFamily: $model->projection_family,
            archiveVisibilityTier: $model->archive_visibility_tier,
            lastSourceAuditLogId: $model->last_source_audit_log_id,
            lastOccurredAt: $model->last_occurred_at?->toDateTimeImmutable(),
            projectionVersion: $model->projection_version,
            refreshedAt: $model->refreshed_at?->toDateTimeImmutable(),
            refreshMode: $model->refresh_mode,
            status: $model->status,
            lastError: $model->last_error,
        );
    }
}
