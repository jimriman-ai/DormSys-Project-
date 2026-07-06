<?php

declare(strict_types=1);

use App\Modules\Allocation\Presentation\Http\Controllers\AllocationFlowController;
use App\Modules\Allocation\Presentation\Http\Controllers\AllocationMutationController;
use Illuminate\Support\Facades\Route;

Route::post('/', [AllocationMutationController::class, 'store'])
    ->name('allocations.create');
Route::post('/from-request/{requestId}', [AllocationMutationController::class, 'storeFromRequest'])
    ->whereUuid('requestId')
    ->name('allocations.create-from-request');
Route::get('/active/{personId}', [AllocationFlowController::class, 'activeForPerson'])
    ->whereUuid('personId')
    ->name('allocations.active-for-person');
Route::get('/{allocationId}', [AllocationFlowController::class, 'show'])
    ->whereUuid('allocationId')
    ->name('allocations.show');
Route::post('/{allocationId}/release', [AllocationMutationController::class, 'release'])
    ->whereUuid('allocationId')
    ->name('allocations.release');
