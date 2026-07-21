<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Presentation\Livewire;

use App\Modules\Dormitory\Application\Services\ListEmployeeAssignedDormitoriesAction;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * WP-DASH-G03-R1 / WP-DORM-UI-READ — employee dormitory index (assignment-filtered).
 */
#[Layout('components.layouts.dashboard')]
#[Title('خوابگاه‌ها')]
final class DormitoryIndexPage extends Component
{
    public function render(ListEmployeeAssignedDormitoriesAction $listAssignedDormitories): View
    {
        $user = auth('identity')->user();
        assert($user instanceof Authenticatable);

        $dormitories = $listAssignedDormitories->execute($user->getAuthIdentifier());

        return view('livewire.dormitory.dormitory-index-page', [
            'dormitories' => $dormitories,
        ]);
    }
}
