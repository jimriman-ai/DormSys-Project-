<?php

declare(strict_types=1);

namespace App\Modules\DormitoryAdmin;

use App\Support\Auth\IdentityRoleGuard;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.dormitory-admin')]
final class DormitoryManagerDashboard extends Component
{
    public function render(): View
    {
        IdentityRoleGuard::assertIdentityRole(IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

        $user = Auth::guard('identity')->user();
        assert($user !== null);

        $dormitories = $this->aggregateAssignedDormitories((string) $user->getAuthIdentifier());

        return view('livewire.dormitory-admin.dormitory-manager-dashboard')
            ->with(['dormitories' => $dormitories]);
    }

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     unit_count: int,
     *     bed_total: int,
     *     bed_occupied: int,
     *     bed_available: int
     * }>
     */
    private function aggregateAssignedDormitories(string $userId): array
    {
        $rows = DB::table('dormitories as d')
            ->join('dormitory_manager_assignments as dma', function ($join) use ($userId): void {
                $join->on('dma.dormitory_id', '=', 'd.id')
                    ->where('dma.user_id', '=', $userId);
            })
            ->leftJoin('dormitory_buildings as b', function ($join): void {
                $join->on('b.dormitory_id', '=', 'd.id')
                    ->whereNull('b.deleted_at');
            })
            ->leftJoin('dormitory_floors as f', function ($join): void {
                $join->on('f.building_id', '=', 'b.id')
                    ->whereNull('f.deleted_at');
            })
            ->leftJoin('dormitory_rooms as r', function ($join): void {
                $join->on('r.floor_id', '=', 'f.id')
                    ->whereNull('r.deleted_at');
            })
            ->leftJoin('dormitory_beds as bed', function ($join): void {
                $join->on('bed.room_id', '=', 'r.id')
                    ->whereNull('bed.deleted_at');
            })
            ->whereNull('d.deleted_at')
            ->groupBy('d.id', 'd.name')
            ->orderBy('d.name')
            ->select([
                'd.id',
                'd.name',
                DB::raw('count(distinct r.id)::int as unit_count'),
                DB::raw('count(bed.id)::int as bed_total'),
                DB::raw("coalesce(sum(case when bed.physical_occupancy_state = 'occupied' then 1 else 0 end), 0)::int as bed_occupied"),
            ])
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $bedTotal = (int) data_get($row, 'bed_total');
            $bedOccupied = (int) data_get($row, 'bed_occupied');

            $result[] = [
                'id' => (string) data_get($row, 'id'),
                'name' => (string) data_get($row, 'name'),
                'unit_count' => (int) data_get($row, 'unit_count'),
                'bed_total' => $bedTotal,
                'bed_occupied' => $bedOccupied,
                'bed_available' => $bedTotal - $bedOccupied,
            ];
        }

        return $result;
    }
}
