<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Presentation\Livewire;

use App\Modules\Dormitory\Application\DTOs\DormitoryDetailData;
use App\Modules\Dormitory\Application\Services\GetEmployeeAssignedDormitoryAction;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * WP-DASH-G03-R1 / WP-DORM-UI-READ — employee dormitory show (assignment-scoped read).
 *
 * Route param `{dormitory}` is the dormitory UUID string (no Eloquent route binding).
 */
#[Layout('components.layouts.dashboard')]
#[Title('جزئیات خوابگاه')]
final class DormitoryShowPage extends Component
{
    public DormitoryDetailData $dormitory;

    public function mount(
        string $dormitory,
        GetEmployeeAssignedDormitoryAction $getAssignedDormitory,
    ): void {
        $user = auth('identity')->user();
        assert($user instanceof Authenticatable);

        $detail = $getAssignedDormitory->execute($user->getAuthIdentifier(), $dormitory);

        if ($detail === null) {
            abort(403);
        }

        $this->dormitory = $detail;
    }

    public function render(): View
    {
        return view('livewire.dormitory.dormitory-show-page');
    }
}
