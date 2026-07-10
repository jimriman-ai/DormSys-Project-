<?php

declare(strict_types=1);

namespace App\Modules\Notification\Presentation\Providers;

use App\Modules\Notification\Presentation\View\Composers\LayoutNavUnreadBadgeComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NotificationPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('components.layouts.app', LayoutNavUnreadBadgeComposer::class);
    }

    public static function notificationWebRoutePath(): string
    {
        return app_path('Modules/Notification/Presentation/Routes/web.php');
    }
}
