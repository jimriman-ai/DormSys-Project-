<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Livewire;

use App\Shared\Auth\IdentityRoleGuard;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * [PERMIT-ID: IMPL-PERMIT-01] §2.5 — Stage-1 Approver Console scaffold (IMP-Q-07 A).
 * Approve/reject Application Action wiring follows in subsequent permit slices.
 */
#[Layout('components.layouts.app')]
final class Stage1ApproverConsolePage extends Component
{
    public ?string $requestId = null;

    public function mount(?string $requestId = null): void
    {
        IdentityRoleGuard::assertIdentityRole(IdentityRoleSeeder::ROLE_DEPT_MGR);
        $this->requestId = $requestId;
    }

    public function render(): View
    {
        IdentityRoleGuard::assertIdentityRole(IdentityRoleSeeder::ROLE_DEPT_MGR);

        return view('livewire.request.stage1-approver-console-page', [
            'requestId' => $this->requestId,
        ]);
    }
}
