<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Mutation\Contracts\MutationAuthorizationPort;
use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Infrastructure\Adapters\CompositeMutationPrincipalContextAdapter;
use App\Application\Mutation\Infrastructure\Adapters\MutationAuthorizationAdapter;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use Illuminate\Support\ServiceProvider;

final class MutationAuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MutationPrincipalContextHolder::class);
        $this->app->singleton(MutationPrincipalContextPort::class, CompositeMutationPrincipalContextAdapter::class);
        $this->app->singleton(MutationAuthorizationPort::class, MutationAuthorizationAdapter::class);
        $this->app->singleton(MutationPolicyEnforcementPoint::class);
    }
}
