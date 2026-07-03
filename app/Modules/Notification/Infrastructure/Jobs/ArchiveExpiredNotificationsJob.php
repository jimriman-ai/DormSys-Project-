<?php

declare(strict_types=1);

namespace App\Modules\Notification\Infrastructure\Jobs;

use App\Modules\Notification\Application\Contracts\NotificationRepositoryContract;
use App\Modules\Notification\Application\Services\NotificationRetentionSettingsReader;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ArchiveExpiredNotificationsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(
        NotificationRepositoryContract $notifications,
        NotificationRetentionSettingsReader $retentionSettings,
    ): void {
        $months = $retentionSettings->retentionMonths();
        $cutoff = new DateTimeImmutable(sprintf('-%d months', $months), new DateTimeZone('UTC'));

        $notifications->archiveExpiredBefore($cutoff);
    }
}
