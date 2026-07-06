<?php

declare(strict_types=1);

use App\Http\Controllers\ApiAuthSessionController;
use App\Http\Controllers\HealthController;
use App\Modules\Reporting\Presentation\Providers\ReportingPresentationServiceProvider;
use App\Modules\Request\Presentation\Providers\RequestPresentationServiceProvider;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::prefix('auth')->group(function (): void {
    Route::post('/login', [ApiAuthSessionController::class, 'login']);
    Route::post('/logout', [ApiAuthSessionController::class, 'logout']);
});

Route::middleware(['auth:api', 'audit.principal'])
    ->prefix('reporting')
    ->group(ReportingPresentationServiceProvider::reportingRoutePath());

Route::middleware(['auth:api', 'request.mutation.principal', 'audit.principal'])
    ->prefix('requests')
    ->group(RequestPresentationServiceProvider::requestRoutePath());
