<?php

declare(strict_types=1);

use App\Modules\Audit\Infrastructure\Jobs\ArchiveExpiredAuditLogsJob;
use App\Modules\Notification\Infrastructure\Jobs\ArchiveExpiredNotificationsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('audit:archive-expired', function () {
    dispatch_sync(new ArchiveExpiredAuditLogsJob);

    $this->info('Expired audit logs archived.');
})->purpose('Archive audit logs past retention period');

Schedule::job(ArchiveExpiredNotificationsJob::class)
    ->daily()
    ->name('notification:archive-expired')
    ->withoutOverlapping();

Schedule::job(ArchiveExpiredAuditLogsJob::class)
    ->daily()
    ->name('audit:archive-expired')
    ->withoutOverlapping();
