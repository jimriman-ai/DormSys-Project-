<?php

declare(strict_types=1);

namespace App\Modules\Notification\Infrastructure\Providers;

use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\MarkNotificationReadContract;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\Contracts\NotificationRepositoryContract;
use App\Modules\Notification\Application\Services\DeliverNotificationAction;
use App\Modules\Notification\Application\Services\MarkNotificationReadAction;
use App\Modules\Notification\Application\Services\NotificationInboxReadService;
use App\Modules\Notification\Application\Services\NotificationRetentionSettingsReader;
use App\Modules\Notification\Infrastructure\Adapters\StubEmployeeExistenceReadAdapter;
use App\Modules\Notification\Infrastructure\Repositories\NotificationRepository;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationRepositoryContract::class, NotificationRepository::class);

        $this->app->singleton(StubEmployeeExistenceReadAdapter::class);
        $this->app->singleton(EmployeeExistenceReadPort::class, StubEmployeeExistenceReadAdapter::class);

        $this->app->singleton(DeliverNotificationAction::class);
        $this->app->singleton(NotificationDeliveryContract::class, DeliverNotificationAction::class);

        $this->app->singleton(NotificationInboxReadService::class);
        $this->app->singleton(NotificationInboxReadContract::class, NotificationInboxReadService::class);

        $this->app->singleton(MarkNotificationReadAction::class);
        $this->app->singleton(MarkNotificationReadContract::class, MarkNotificationReadAction::class);

        $this->app->singleton(NotificationRetentionSettingsReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/notification'));
    }
}
