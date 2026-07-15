<?php

declare(strict_types=1);

namespace App\Modules\DormitoryAdmin;

use App\Shared\Auth\IdentityRoleGuard;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.dormitory-admin')]
final class DormitoryManagerDashboard extends Component
{
    public function render(): View
    {
        IdentityRoleGuard::assertIdentityRole(IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

        // Assignment-scoped aggregation deferred (B1 / BL-B1-01): no assignment tables in schema.
        $dormitories = [];

        return view('livewire.dormitory-admin.dormitory-manager-dashboard')
            ->with(['dormitories' => $dormitories]);
    }
}
