<?php

declare(strict_types=1);

namespace App\Modules\DormitoryAdmin;

use App\Shared\Auth\IdentityRoleGuard;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.dormitory-admin')]
final class DormitoryUnitManagerDashboard extends Component
{
    public function render(): View
    {
        IdentityRoleGuard::assertIdentityRole(IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);

        // Assignment-scoped aggregation deferred (B1 / BL-B1-01): no assignment tables in schema.
        $rooms = [];

        return view('livewire.dormitory-admin.dormitory-unit-manager-dashboard')
            ->with(['rooms' => $rooms]);
    }
}
