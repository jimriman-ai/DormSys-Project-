<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Presentation\Providers;

use Illuminate\Support\ServiceProvider;

class ReportingPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public static function reportingRoutePath(): string
    {
        return app_path('Modules/Reporting/Presentation/Routes/reporting.php');
    }
}
