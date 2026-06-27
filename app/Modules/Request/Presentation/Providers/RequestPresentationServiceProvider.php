<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Providers;

use Illuminate\Support\ServiceProvider;

class RequestPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Artisan commands registered in spec05 T025.
            ]);
        }
    }
}
