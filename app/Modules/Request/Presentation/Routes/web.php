<?php

declare(strict_types=1);

use App\Modules\Request\Presentation\Livewire\RequestCreatePage;
use App\Modules\Request\Presentation\Livewire\RequestListPage;
use App\Modules\Request\Presentation\Livewire\RequestShowPage;
use Illuminate\Support\Facades\Route;

Route::get('/', RequestListPage::class)->name('requests.index');
Route::get('/create', RequestCreatePage::class)->name('requests.create');
Route::get('/{requestId}', RequestShowPage::class)
    ->whereUuid('requestId')
    ->name('requests.show');
