<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Infrastructure\Repositories;

use App\Modules\Reporting\Application\Contracts\Ports\ProjectionIngestReceiptRepositoryPort;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Infrastructure\Persistence\Models\ProjectionIngestReceiptModel;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Carbon;

final class ProjectionIngestReceiptRepository implements ProjectionIngestReceiptRepositoryPort
{
    public function claim(
        ProjectionFamily $projectionFamily,
        string $sourceAuditLogId,
        ArchiveVisibilityTier $archiveVisibilityTier,
    ): bool {
        $exists = ProjectionIngestReceiptModel::query()
            ->where('projection_family', $projectionFamily->value)
            ->where('source_audit_log_id', $sourceAuditLogId)
            ->where('archive_visibility_tier', $archiveVisibilityTier->value)
            ->exists();

        if ($exists) {
            return false;
        }

        ProjectionIngestReceiptModel::query()->create([
            'projection_family' => $projectionFamily,
            'source_audit_log_id' => $sourceAuditLogId,
            'archive_visibility_tier' => $archiveVisibilityTier,
            'ingested_at' => Carbon::instance(new DateTimeImmutable('now', new DateTimeZone('UTC'))),
        ]);

        return true;
    }
}
