<?php

declare(strict_types=1);

use App\Modules\Audit\Presentation\Http\Middleware\EnsureAuditHistoryReadMiddleware;
use App\Modules\Audit\Presentation\Livewire\AuditHistoryPage;
use Illuminate\Support\Facades\Route;

Route::get('/', AuditHistoryPage::class)
    ->middleware(EnsureAuditHistoryReadMiddleware::class)
    ->name('audit.index');
