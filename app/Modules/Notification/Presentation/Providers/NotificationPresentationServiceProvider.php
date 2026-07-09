<?php

declare(strict_types=1);

namespace App\Modules\Notification\Presentation\Providers;

use Illuminate\Support\ServiceProvider;

class NotificationPresentationServiceProvider extends ServiceProvider
{
    public static function notificationWebRoutePath(): string
    {
        return app_path('Modules/Notification/Presentation/Routes/web.php');
    }
}
