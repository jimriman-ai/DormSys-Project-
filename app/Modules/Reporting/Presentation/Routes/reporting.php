<?php

declare(strict_types=1);

use App\Modules\Reporting\Presentation\Http\Controllers\ReportingController;
use Illuminate\Support\Facades\Route;

Route::get('/entity-timeline', [ReportingController::class, 'entityTimeline']);
Route::get('/correlation-bundle', [ReportingController::class, 'correlationBundle']);
Route::get('/audit-window-summary', [ReportingController::class, 'auditWindowSummary']);
Route::get('/compliance-export', [ReportingController::class, 'complianceExport']);
Route::get('/security-actor-activity', [ReportingController::class, 'securityActorActivity']);
Route::get('/drill-down', [ReportingController::class, 'drillDown']);
