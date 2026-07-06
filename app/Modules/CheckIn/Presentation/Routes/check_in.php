<?php

declare(strict_types=1);

use App\Modules\CheckIn\Presentation\Http\Controllers\CheckInFlowController;
use App\Modules\CheckIn\Presentation\Http\Controllers\CheckInMutationController;
use Illuminate\Support\Facades\Route;

Route::get('/{allocationId}', [CheckInFlowController::class, 'show'])
    ->whereUuid('allocationId')
    ->name('check-in.show');
Route::post('/{allocationId}/check-in', [CheckInMutationController::class, 'checkIn'])
    ->whereUuid('allocationId')
    ->name('check-in.check-in');
Route::post('/{allocationId}/check-out', [CheckInMutationController::class, 'checkOut'])
    ->whereUuid('allocationId')
    ->name('check-in.check-out');
