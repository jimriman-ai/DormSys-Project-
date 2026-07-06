<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Presentation\Providers;

use Illuminate\Support\ServiceProvider;

class CheckInPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public static function checkInRoutePath(): string
    {
        return app_path('Modules/CheckIn/Presentation/Routes/check_in.php');
    }
}
