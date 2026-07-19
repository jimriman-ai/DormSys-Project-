<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Presentation\Providers;

use App\Modules\Dashboard\Presentation\View\Composers\DashboardNavComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

final class DashboardPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('components.layouts.dashboard', DashboardNavComposer::class);
    }
}
