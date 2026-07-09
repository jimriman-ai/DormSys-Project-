<?php

declare(strict_types=1);

use App\Modules\Notification\Presentation\Livewire\NotificationInboxPage;
use Illuminate\Support\Facades\Route;

Route::get('/', NotificationInboxPage::class)->name('notifications.index');
