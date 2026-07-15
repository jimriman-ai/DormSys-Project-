<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Providers;

use App\Modules\Identity\Presentation\Console\CreateUserCommand;
use App\Modules\Identity\Presentation\Console\DeactivateUserCommand;
use Illuminate\Support\ServiceProvider;

class IdentityPresentationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateUserCommand::class,
                DeactivateUserCommand::class,
            ]);
        }
    }

    public static function identityRoutePath(): string
    {
        return app_path('Modules/Identity/Presentation/Routes/identity.php');
    }
}
