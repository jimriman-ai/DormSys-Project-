<?php

declare(strict_types=1);

/**
 * [PERMIT-ID: IMPL-PERMIT-01] §2.2 — Employee self-service routes (IMP-Q-01 A).
 * Mounts successor Livewire pages under /employee/requests/*; does not edit frozen contracts.
 */

use App\Modules\Request\Presentation\Livewire\RequestCreatePage;
use App\Modules\Request\Presentation\Livewire\RequestListPage;
use App\Modules\Request\Presentation\Livewire\RequestShowPage;
use Illuminate\Support\Facades\Route;

Route::get('/', RequestListPage::class)->name('index');
Route::get('/create', RequestCreatePage::class)->name('create');
Route::get('/{requestId}', RequestShowPage::class)
    ->whereUuid('requestId')
    ->name('show');
