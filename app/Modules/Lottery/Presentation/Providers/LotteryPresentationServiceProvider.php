<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Presentation\Providers;

use Illuminate\Support\ServiceProvider;

class LotteryPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Routes and Livewire components will be registered in later phases.
    }
}
