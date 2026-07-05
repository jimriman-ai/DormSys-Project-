<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Providers;

use App\Modules\Voucher\Application\Contracts\AccommodationClassificationReadPort;
use App\Modules\Voucher\Application\Contracts\ExternalLotteryWinnerPathContract;
use App\Modules\Voucher\Application\Contracts\ReservePromotionPathContract;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityEvaluationContract;
use App\Modules\Voucher\Application\Contracts\VoucherEligibilityRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherIssuanceContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleContract;
use App\Modules\Voucher\Application\Contracts\VoucherLifecycleTransitionRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherReadContract;
use App\Modules\Voucher\Application\Contracts\VoucherReadRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherRepositoryContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerIntakeContract;
use App\Modules\Voucher\Application\Contracts\VoucherTriggerRepositoryContract;
use App\Modules\Voucher\Application\Services\AcceptTriggerFactsAction;
use App\Modules\Voucher\Application\Services\EvaluateVoucherEligibilityAction;
use App\Modules\Voucher\Application\Services\IssueVoucherAction;
use App\Modules\Voucher\Application\Services\ProcessExternalLotteryWinnerAction;
use App\Modules\Voucher\Application\Services\ProcessReservePromotionAction;
use App\Modules\Voucher\Application\Services\VoucherAuditRecordingAdapter;
use App\Modules\Voucher\Application\Services\VoucherLifecycleAction;
use App\Modules\Voucher\Application\Services\VoucherReadService;
use App\Modules\Voucher\Domain\Services\ExternalLotteryWinnerFactsSanitizer;
use App\Modules\Voucher\Domain\Services\VoucherCodeGenerator;
use App\Modules\Voucher\Domain\Services\VoucherEligibilityEvaluator;
use App\Modules\Voucher\Infrastructure\Adapters\AuditingVoucherLifecycleTransitionRepository;
use App\Modules\Voucher\Infrastructure\Adapters\StubAccommodationClassificationReadAdapter;
use App\Modules\Voucher\Infrastructure\Repositories\VoucherEligibilityRepository;
use App\Modules\Voucher\Infrastructure\Repositories\VoucherLifecycleTransitionRepository;
use App\Modules\Voucher\Infrastructure\Repositories\VoucherReadRepository;
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
        $this->app->singleton(VoucherReadRepositoryContract::class, VoucherReadRepository::class);
        $this->app->singleton(VoucherLifecycleTransitionRepository::class);
        $this->app->singleton(VoucherAuditRecordingAdapter::class);
        $this->app->singleton(VoucherLifecycleTransitionRepositoryContract::class, function ($app): AuditingVoucherLifecycleTransitionRepository {
            return new AuditingVoucherLifecycleTransitionRepository(
                $app->make(VoucherLifecycleTransitionRepository::class),
                $app->make(VoucherAuditRecordingAdapter::class),
            );
        });
        $this->app->singleton(VoucherEligibilityEvaluator::class);
        $this->app->singleton(ExternalLotteryWinnerFactsSanitizer::class);
        $this->app->singleton(VoucherCodeGenerator::class);
        $this->app->singleton(StubAccommodationClassificationReadAdapter::class);
        $this->app->singleton(AccommodationClassificationReadPort::class, StubAccommodationClassificationReadAdapter::class);
        $this->app->singleton(AcceptTriggerFactsAction::class);
        $this->app->singleton(EvaluateVoucherEligibilityAction::class);
        $this->app->singleton(IssueVoucherAction::class);
        $this->app->singleton(ProcessExternalLotteryWinnerAction::class);
        $this->app->singleton(ProcessReservePromotionAction::class);
        $this->app->singleton(VoucherLifecycleAction::class);
        $this->app->singleton(VoucherReadService::class);
        $this->app->singleton(VoucherTriggerIntakeContract::class, AcceptTriggerFactsAction::class);
        $this->app->singleton(VoucherEligibilityEvaluationContract::class, EvaluateVoucherEligibilityAction::class);
        $this->app->singleton(VoucherIssuanceContract::class, IssueVoucherAction::class);
        $this->app->singleton(ExternalLotteryWinnerPathContract::class, ProcessExternalLotteryWinnerAction::class);
        $this->app->singleton(ReservePromotionPathContract::class, ProcessReservePromotionAction::class);
        $this->app->singleton(VoucherLifecycleContract::class, VoucherLifecycleAction::class);
        $this->app->singleton(VoucherReadContract::class, VoucherReadService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/voucher'));
    }
}
