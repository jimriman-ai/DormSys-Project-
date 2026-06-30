<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Providers;

use App\Modules\Lottery\Application\Contracts\EmployeeLotteryScorePort;
use App\Modules\Lottery\Application\Contracts\LotteryEligibleSnapshotRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRegistrationRepositoryContract;
use App\Modules\Lottery\Application\Contracts\LotteryRequestReadPort;
use App\Modules\Lottery\Application\Contracts\LotteryResultReadContract;
use App\Modules\Lottery\Application\Contracts\LotteryResultRepositoryContract;
use App\Modules\Lottery\Application\Services\CancelLotteryProgramAction;
use App\Modules\Lottery\Application\Services\CloseRegistrationAction;
use App\Modules\Lottery\Application\Services\CreateLotteryProgramAction;
use App\Modules\Lottery\Application\Services\EnrollRegistrationAction;
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;
use App\Modules\Lottery\Application\Services\ExecuteDrawAction;
use App\Modules\Lottery\Application\Services\LockLotteryProgramAction;
use App\Modules\Lottery\Application\Services\LotteryScoringConfigReader;
use App\Modules\Lottery\Application\Services\OpenRegistrationAction;
use App\Modules\Lottery\Infrastructure\Adapters\NullEmployeeLotteryScoreAdapter;
use App\Modules\Lottery\Application\Services\LotteryResultReadService;
use App\Modules\Lottery\Application\Adapters\RequestReadAdapter;
use App\Modules\Lottery\Domain\Services\LotteryDrawSelector;
use App\Modules\Lottery\Domain\Services\LotteryScoringEngine;
use App\Modules\Lottery\Infrastructure\Adapters\NullProposedAllocationAdapter;
use App\Modules\Lottery\Infrastructure\Repositories\LotteryEligibleSnapshotRepository;
use App\Modules\Lottery\Infrastructure\Repositories\LotteryProgramRepository;
use App\Modules\Lottery\Infrastructure\Repositories\LotteryRegistrationRepository;
use App\Modules\Lottery\Infrastructure\Repositories\LotteryResultRepository;
use Illuminate\Support\ServiceProvider;

class LotteryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LotteryProgramRepositoryContract::class, LotteryProgramRepository::class);
        $this->app->singleton(LotteryRegistrationRepositoryContract::class, LotteryRegistrationRepository::class);
        $this->app->singleton(LotteryEligibleSnapshotRepositoryContract::class, LotteryEligibleSnapshotRepository::class);
        $this->app->singleton(LotteryResultRepositoryContract::class, LotteryResultRepository::class);
        $this->app->singleton(LotteryRequestReadPort::class, RequestReadAdapter::class);
        $this->app->singleton(EmployeeLotteryScorePort::class, NullEmployeeLotteryScoreAdapter::class);
        $this->app->singleton(LotteryResultReadContract::class, LotteryResultReadService::class);
        $this->app->singleton(ProposedAllocationPort::class, NullProposedAllocationAdapter::class);
        $this->app->singleton(CreateLotteryProgramAction::class);
        $this->app->singleton(OpenRegistrationAction::class);
        $this->app->singleton(CloseRegistrationAction::class);
        $this->app->singleton(CancelLotteryProgramAction::class);
        $this->app->singleton(EnrollRegistrationAction::class);
        $this->app->singleton(LotteryScoringConfigReader::class);
        $this->app->singleton(LotteryScoringEngine::class);
        $this->app->singleton(LockLotteryProgramAction::class);
        $this->app->singleton(LotteryDrawSelector::class);
        $this->app->singleton(ExecuteDrawAction::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/modules/lottery'));
    }
}
