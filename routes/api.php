<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;
use App\Modules\Reporting\Presentation\Providers\ReportingPresentationServiceProvider;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::middleware(['auth:api', 'audit.principal'])
    ->prefix('reporting')
    ->group(ReportingPresentationServiceProvider::reportingRoutePath());
