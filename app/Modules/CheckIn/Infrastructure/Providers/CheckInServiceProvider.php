<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Infrastructure\Providers;

use App\Modules\CheckIn\Application\Contracts\CheckInCommandPort;
use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Modules\CheckIn\Application\Services\CheckInAction;
use App\Modules\CheckIn\Application\Services\CheckInMutationAuthorizationGate;
use App\Modules\CheckIn\Application\Services\CheckInService;
use App\Modules\CheckIn\Application\Services\CheckOutAction;
use App\Modules\CheckIn\Application\Services\GetOpenCheckInByAllocationAction;
use App\Modules\CheckIn\Application\Services\OperatorRoleGate;
use App\Modules\CheckIn\Infrastructure\Repositories\CheckInRecordRepository;
use Illuminate\Support\ServiceProvider;

class CheckInServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CheckInRecordRepositoryContract::class, CheckInRecordRepository::class);
        $this->app->singleton(OperatorRoleGate::class);
        $this->app->singleton(CheckInMutationAuthorizationGate::class);
        $this->app->singleton(CheckInAction::class);
        $this->app->singleton(CheckOutAction::class);
        $this->app->singleton(GetOpenCheckInByAllocationAction::class);
        $this->app->singleton(CheckInService::class);
        $this->app->singleton(CheckInCommandPort::class, CheckInService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/check_in'));
    }
}
