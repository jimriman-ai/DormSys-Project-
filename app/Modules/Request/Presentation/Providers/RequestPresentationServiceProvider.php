<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Providers;

use App\Modules\Request\Presentation\Console\CreatePersonalRequestCommand;
use App\Modules\Request\Presentation\Console\SubmitRequestCommand;
use Illuminate\Support\ServiceProvider;

class RequestPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreatePersonalRequestCommand::class,
                SubmitRequestCommand::class,
            ]);
        }
    }
}
