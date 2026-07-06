<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Presentation\Providers;

use Illuminate\Support\ServiceProvider;

class AllocationPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public static function allocationRoutePath(): string
    {
        return app_path('Modules/Allocation/Presentation/Routes/allocations.php');
    }
}
