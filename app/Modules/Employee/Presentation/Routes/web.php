<?php

declare(strict_types=1);

use App\Modules\Employee\Presentation\Livewire\EmployeeHubPage;
use Illuminate\Support\Facades\Route;

Route::get('/', EmployeeHubPage::class)->name('employees.hub');
