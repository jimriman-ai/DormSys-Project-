<?php

declare(strict_types=1);

use App\Http\Controllers\Web\AuthSessionController;
use App\Modules\Request\Presentation\Providers\RequestPresentationServiceProvider;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:api')->group(function (): void {
    Route::get('/login', [AuthSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthSessionController::class, 'store']);
});

Route::middleware(['auth:api', 'request.mutation.principal', 'audit.principal'])->group(function (): void {
    Route::post('/logout', [AuthSessionController::class, 'destroy'])->name('logout');

    Route::redirect('/', '/requests')->name('home');

    Route::prefix('requests')
        ->group(RequestPresentationServiceProvider::requestWebRoutePath());
});
