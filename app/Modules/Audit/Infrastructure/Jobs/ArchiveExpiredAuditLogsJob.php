<?php

declare(strict_types=1);

namespace App\Modules\Audit\Infrastructure\Jobs;

use App\Modules\Audit\Application\Contracts\AuditLogRepositoryContract;
use App\Modules\Audit\Application\Services\AuditRetentionSettingsReader;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ArchiveExpiredAuditLogsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(
        AuditLogRepositoryContract $auditLogs,
        AuditRetentionSettingsReader $retentionSettings,
    ): void {
        $months = $retentionSettings->retentionMonths();
        $cutoff = new DateTimeImmutable(sprintf('-%d months', $months), new DateTimeZone('UTC'));

        $auditLogs->archiveExpiredBefore($cutoff);
    }
}
