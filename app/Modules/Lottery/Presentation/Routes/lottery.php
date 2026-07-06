<?php

declare(strict_types=1);

use App\Modules\Lottery\Presentation\Http\Controllers\LotteryFlowController;
use App\Modules\Lottery\Presentation\Http\Controllers\LotteryMutationController;
use Illuminate\Support\Facades\Route;

Route::post('/programs', [LotteryMutationController::class, 'store'])
    ->name('lottery.programs.create');
Route::get('/programs/{programId}', [LotteryFlowController::class, 'show'])
    ->whereUuid('programId')
    ->name('lottery.programs.show');
Route::get('/programs/{programId}/results', [LotteryFlowController::class, 'results'])
    ->whereUuid('programId')
    ->name('lottery.programs.results');
Route::post('/programs/{programId}/enroll', [LotteryFlowController::class, 'enroll'])
    ->whereUuid('programId')
    ->name('lottery.programs.enroll');
Route::post('/programs/{programId}/open-registration', [LotteryMutationController::class, 'openRegistration'])
    ->whereUuid('programId')
    ->name('lottery.programs.open-registration');
Route::post('/programs/{programId}/close-registration', [LotteryMutationController::class, 'closeRegistration'])
    ->whereUuid('programId')
    ->name('lottery.programs.close-registration');
Route::post('/programs/{programId}/lock', [LotteryMutationController::class, 'lock'])
    ->whereUuid('programId')
    ->name('lottery.programs.lock');
Route::post('/programs/{programId}/draw', [LotteryMutationController::class, 'draw'])
    ->whereUuid('programId')
    ->name('lottery.programs.draw');
Route::post('/programs/{programId}/cancel', [LotteryMutationController::class, 'cancel'])
    ->whereUuid('programId')
    ->name('lottery.programs.cancel');
