<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Presentation\Livewire;

use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryAssignment;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * WP-DASH-G03-R1 / Q-G03-SCOPE — employee dormitory index (assignment-filtered).
 */
#[Layout('components.layouts.dashboard')]
#[Title('خوابگاه‌ها')]
final class DormitoryIndexPage extends Component
{
    public function render(): View
    {
        $user = auth('identity')->user();
        assert($user instanceof Authenticatable);

        /** @var Collection<int, DormitoryModel> $dormitories */
        $dormitories = DormitoryModel::query()
            ->whereIn(
                'id',
                DormitoryAssignment::query()
                    ->active()
                    ->where('user_id', $user->getId())
                    ->select('dormitory_id'),
            )
            ->orderBy('code')
            ->get();

        return view('livewire.dormitory.dormitory-index-page', [
            'dormitories' => $dormitories,
        ]);
    }
}
