<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Providers;

use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerRepositoryContract;
use App\Modules\Voucher\Application\Services\AcceptTriggerFactsAction;
use App\Modules\Voucher\Infrastructure\Repositories\VoucherTriggerRepository;
use Illuminate\Support\ServiceProvider;

class VoucherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VoucherTriggerRepositoryContract::class, VoucherTriggerRepository::class);
        $this->app->singleton(AcceptTriggerFactsAction::class);
        $this->app->singleton(VoucherTriggerIntakeContract::class, AcceptTriggerFactsAction::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/voucher'));
    }
}
