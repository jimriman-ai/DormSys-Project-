<?php

declare(strict_types=1);

namespace App\Modules\Audit\Presentation\Providers;

use App\Modules\Audit\Presentation\View\Composers\LayoutNavAuditLinkComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AuditPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('components.layouts.app', LayoutNavAuditLinkComposer::class);
    }

    public static function auditWebRoutePath(): string
    {
        return app_path('Modules/Audit/Presentation/Routes/web.php');
    }
}
