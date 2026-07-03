<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Reporting\Application\Contracts\Ports\ProjectionCursorControlPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshInputPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshRunnerPort;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshRequestDto;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshResultDto;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ProjectionRefreshRunnerService implements ProjectionRefreshRunnerPort
{
    public function __construct(
        private readonly ProjectionRefreshInputPort $refreshInput,
        private readonly ProjectionCursorControlPort $cursorControl,
        private readonly ProjectionRefreshMaterializerRegistry $materializerRegistry,
        private readonly ProjectionCursorPositionSelector $cursorPositionSelector,
    ) {}

    public function runBatch(ProjectionRefreshRequestDto $request): ProjectionRefreshResultDto
    {
        $batch = $this->refreshInput->fetchNextBatch($request);

        if ($batch->items === []) {
            return new ProjectionRefreshResultDto(
                cursor: $batch->cursor,
                itemsFetched: 0,
                itemsMaterialized: 0,
                hasMorePages: $batch->hasMorePages,
                status: $batch->cursor->status,
            );
        }

        $this->cursorControl->markRunning($batch->cursor->id);

        try {
            $materialized = DB::transaction(function () use ($batch, $request): int {
                $materializer = $this->materializerRegistry->resolve($request->projectionFamily);

                $written = $materializer->materialize(
                    $batch->items,
                    $request->archiveVisibilityTier,
                    $request->projectionVersion,
                );

                $latest = $this->cursorPositionSelector->selectLatest($batch->items);

                if ($latest === null) {
                    return $written;
                }

                $this->cursorControl->advanceAfterSuccessfulBatch(
                    $batch->cursor->id,
                    $latest->auditLogId,
                    $latest->occurredAt,
                    $request->projectionVersion,
                );

                return $written;
            });
        } catch (Throwable $exception) {
            $this->cursorControl->markFailed($batch->cursor->id, $exception->getMessage());

            throw $exception;
        }

        $cursor = $this->cursorControl->resolveCursor(
            $request->projectionFamily,
            $request->archiveVisibilityTier,
            $request->projectionVersion,
            $request->refreshMode,
        );

        return new ProjectionRefreshResultDto(
            cursor: $cursor,
            itemsFetched: count($batch->items),
            itemsMaterialized: $materialized,
            hasMorePages: $batch->hasMorePages,
            status: $cursor->status,
        );
    }
}
