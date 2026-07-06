<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Presentation\Providers;

use Illuminate\Support\ServiceProvider;

class LotteryPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public static function lotteryRoutePath(): string
    {
        return app_path('Modules/Lottery/Presentation/Routes/lottery.php');
    }
}
