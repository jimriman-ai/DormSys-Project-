<?php

declare(strict_types=1);

use App\Http\Controllers\ApiAuthSessionController;
use App\Http\Controllers\HealthController;
use App\Modules\Allocation\Presentation\Providers\AllocationPresentationServiceProvider;
use App\Modules\CheckIn\Presentation\Providers\CheckInPresentationServiceProvider;
use App\Modules\Identity\Presentation\Providers\IdentityPresentationServiceProvider;
use App\Modules\Lottery\Presentation\Providers\LotteryPresentationServiceProvider;
use App\Modules\Reporting\Presentation\Providers\ReportingPresentationServiceProvider;
use App\Modules\Request\Presentation\Providers\RequestPresentationServiceProvider;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::prefix('auth')->group(function (): void {
    Route::post('/login', [ApiAuthSessionController::class, 'login']);
    Route::post('/logout', [ApiAuthSessionController::class, 'logout']);
});

Route::middleware([
    'auth:api',
    'permission:identity.roles.manage,api',
    'request.mutation.principal',
    'audit.principal',
])
    ->prefix('identity')
    ->group(IdentityPresentationServiceProvider::identityRoutePath());

Route::middleware(['auth:api', 'audit.principal'])
    ->prefix('reporting')
    ->group(ReportingPresentationServiceProvider::reportingRoutePath());

Route::middleware(['auth:api', 'request.mutation.principal', 'audit.principal'])
    ->prefix('requests')
    ->group(RequestPresentationServiceProvider::requestRoutePath());

Route::middleware(['auth:api', 'request.mutation.principal', 'audit.principal'])
    ->prefix('lottery')
    ->group(LotteryPresentationServiceProvider::lotteryRoutePath());

Route::middleware(['auth:api', 'request.mutation.principal', 'audit.principal'])
    ->prefix('allocations')
    ->group(AllocationPresentationServiceProvider::allocationRoutePath());

Route::middleware(['auth:api', 'request.mutation.principal', 'audit.principal'])
    ->prefix('check-in')
    ->group(CheckInPresentationServiceProvider::checkInRoutePath());
