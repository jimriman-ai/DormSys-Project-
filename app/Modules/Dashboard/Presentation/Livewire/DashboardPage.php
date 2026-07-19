<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Presentation\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * WP-UI-C-DASH-01 — shared dashboard shell page (UI skeleton only).
 */
#[Layout('components.layouts.dashboard')]
#[Title('Dashboard')]
final class DashboardPage extends Component
{
    public function render(): View
    {
        return view('livewire.dashboard.dashboard-page');
    }
}
