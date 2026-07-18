<?php

declare(strict_types=1);

/**
 * [PERMIT-ID: IMPL-PERMIT-01] §2.2 / §2.5 — Stage-1 Approver Console routes (IMP-Q-01 A / IMP-Q-07 A).
 */

use App\Modules\Request\Presentation\Livewire\Stage1ApproverConsolePage;
use Illuminate\Support\Facades\Route;

Route::get('/', Stage1ApproverConsolePage::class)->name('index');
Route::get('/{requestId}', Stage1ApproverConsolePage::class)
    ->whereUuid('requestId')
    ->name('show');
