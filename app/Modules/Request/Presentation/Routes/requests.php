<?php

declare(strict_types=1);

use App\Modules\Request\Presentation\Http\Controllers\RequestFlowController;
use App\Modules\Request\Presentation\Http\Controllers\RequestMutationController;
use Illuminate\Support\Facades\Route;

Route::post('/personal', [RequestFlowController::class, 'storePersonal'])
    ->name('requests.flow.create-personal');
Route::get('/mine', [RequestFlowController::class, 'indexMine'])
    ->name('requests.flow.mine');
Route::get('/{requestId}', [RequestFlowController::class, 'show'])
    ->whereUuid('requestId')
    ->name('requests.flow.show');
Route::post('/{requestId}/submit', [RequestMutationController::class, 'submit'])
    ->whereUuid('requestId')
    ->name('requests.mutations.submit');
Route::post('/{requestId}/cancel', [RequestMutationController::class, 'cancel'])
    ->whereUuid('requestId')
    ->name('requests.mutations.cancel');
Route::post('/{requestId}/approve', [RequestMutationController::class, 'approve'])
    ->whereUuid('requestId')
    ->name('requests.mutations.approve');
Route::post('/{requestId}/reject', [RequestMutationController::class, 'reject'])
    ->whereUuid('requestId')
    ->name('requests.mutations.reject');
