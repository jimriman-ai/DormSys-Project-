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

    public static function requestRoutePath(): string
    {
        return app_path('Modules/Request/Presentation/Routes/requests.php');
    }

    public static function requestWebRoutePath(): string
    {
        return app_path('Modules/Request/Presentation/Routes/web.php');
    }

    /** [PERMIT-ID: IMPL-PERMIT-01] §2.2 — employee self-service route file. */
    public static function employeeRequestWebRoutePath(): string
    {
        return app_path('Modules/Request/Presentation/Routes/employee-requests.php');
    }

    /** [PERMIT-ID: IMPL-PERMIT-01] §2.2 — Stage-1 approver console route file. */
    public static function stage1ApprovalWebRoutePath(): string
    {
        return app_path('Modules/Request/Presentation/Routes/stage1-approvals.php');
    }
}
