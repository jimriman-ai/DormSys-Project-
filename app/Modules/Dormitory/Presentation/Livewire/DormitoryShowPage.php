<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Presentation\Livewire;

use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * WP-DASH-G03-R1 / Q-G03-SCOPE — employee dormitory show (assignment Policy view).
 *
 * Uses Gate::forUser(identity) because default AUTH_GUARD is web; $this->authorize()
 * would not see the identity principal on auth:identity routes.
 *
 * Route param `{dormitory}` is resolved as DormitoryModel (Livewire page binding).
 */
#[Layout('components.layouts.dashboard')]
#[Title('جزئیات خوابگاه')]
final class DormitoryShowPage extends Component
{
    public DormitoryModel $dormitory;

    public function mount(DormitoryModel $dormitory): void
    {
        $user = auth('identity')->user();
        assert($user instanceof Authenticatable);

        Gate::forUser($user)->authorize('view', $dormitory);
        $this->dormitory = $dormitory;
    }

    public function render(): View
    {
        return view('livewire.dormitory.dormitory-show-page');
    }
}
