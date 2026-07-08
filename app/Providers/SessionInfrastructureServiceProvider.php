<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infrastructure\Session\CredentialUserDatabaseSessionHandler;
use Illuminate\Support\ServiceProvider;

final class SessionInfrastructureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('session', function ($manager): void {
            $manager->extend('database', function ($app) {
                $connection = $app['db']->connection($app['config']->get('session.connection'));

                return new CredentialUserDatabaseSessionHandler(
                    $connection,
                    $app['config']->get('session.table'),
                    $app['config']->get('session.lifetime'),
                    $app,
                );
            });
        });
    }
}
