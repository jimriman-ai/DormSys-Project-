<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Providers;

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherIssuanceContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleTransitionRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerRepositoryContract;
use App\Modules\Voucher\Application\Services\AcceptTriggerFactsAction;
use App\Modules\Voucher\Application\Services\EvaluateVoucherEligibilityAction;
use App\Modules\Voucher\Application\Services\IssueVoucherAction;
use App\Modules\Voucher\Application\Services\VoucherLifecycleAction;
use App\Modules\Voucher\Domain\Services\VoucherCodeGenerator;
use App\Modules\Voucher\Domain\Services\VoucherEligibilityEvaluator;
use App\Modules\Voucher\Infrastructure\Adapters\StubAccommodationClassificationReadAdapter;
use App\Modules\Voucher\Infrastructure\Repositories\VoucherEligibilityRepository;
use App\Modules\Voucher\Infrastructure\Repositories\VoucherLifecycleTransitionRepository;
use App\Modules\Voucher\Infrastructure\Repositories\VoucherRepository;
use App\Modules\Voucher\Infrastructure\Repositories\VoucherTriggerRepository;
use Illuminate\Support\ServiceProvider;

class VoucherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VoucherTriggerRepositoryContract::class, VoucherTriggerRepository::class);
        $this->app->singleton(VoucherEligibilityRepositoryContract::class, VoucherEligibilityRepository::class);
        $this->app->singleton(VoucherRepositoryContract::class, VoucherRepository::class);
        $this->app->singleton(VoucherLifecycleTransitionRepositoryContract::class, VoucherLifecycleTransitionRepository::class);
        $this->app->singleton(VoucherEligibilityEvaluator::class);
        $this->app->singleton(VoucherCodeGenerator::class);
        $this->app->singleton(StubAccommodationClassificationReadAdapter::class);
        $this->app->singleton(AccommodationClassificationReadPort::class, StubAccommodationClassificationReadAdapter::class);
        $this->app->singleton(AcceptTriggerFactsAction::class);
        $this->app->singleton(EvaluateVoucherEligibilityAction::class);
        $this->app->singleton(IssueVoucherAction::class);
        $this->app->singleton(VoucherLifecycleAction::class);
        $this->app->singleton(VoucherTriggerIntakeContract::class, AcceptTriggerFactsAction::class);
        $this->app->singleton(VoucherEligibilityEvaluationContract::class, EvaluateVoucherEligibilityAction::class);
        $this->app->singleton(VoucherIssuanceContract::class, IssueVoucherAction::class);
        $this->app->singleton(VoucherLifecycleContract::class, VoucherLifecycleAction::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/voucher'));
    }
}
