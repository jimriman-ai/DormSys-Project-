<?php

declare(strict_types=1);

/**
 * Dormant module route file — canonical `/requests/*` bindings live in `routes/web.php`
 * (UI-C-01-ROUTE-CANONICALIZATION). Kept for `requestWebRoutePath()` discovery only.
 */

use App\Modules\Request\Presentation\Livewire\PersonalRequestFormPage;
use App\Modules\Request\Presentation\Livewire\RequestListPage;
use App\Modules\Request\Presentation\Livewire\RequestShowPage;
use Illuminate\Support\Facades\Route;

Route::get('/', RequestListPage::class)->name('requests.index');
Route::get('/create', PersonalRequestFormPage::class)->name('requests.create');
Route::get('/{requestId}', RequestShowPage::class)
    ->whereUuid('requestId')
    ->name('requests.show');
