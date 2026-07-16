<?php

declare(strict_types=1);

namespace App\Modules\DormitoryAdmin;

use App\Shared\Auth\IdentityRoleGuard;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.dormitory-admin')]
final class DormitoryUnitManagerDashboard extends Component
{
    public function render(): View
    {
        IdentityRoleGuard::assertIdentityRole(IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);

        $user = auth('identity')->user();
        assert($user !== null);

        $rooms = $this->aggregateAssignedRooms((string) $user->getAuthIdentifier());

        return view('livewire.dormitory-admin.dormitory-unit-manager-dashboard')
            ->with(['rooms' => $rooms]);
    }

    /**
     * @return list<array{
     *     id: string,
     *     room_label: string,
     *     floor_label: string,
     *     building_name: string,
     *     dormitory_name: string,
     *     bed_total: int,
     *     bed_occupied: int,
     *     bed_reserved: int,
     *     bed_vacant: int
     * }>
     */
    private function aggregateAssignedRooms(string $userId): array
    {
        $rows = DB::table('dormitory_rooms as r')
            ->join('dormitory_unit_manager_assignments as uma', function ($join) use ($userId): void {
                $join->on('uma.room_id', '=', 'r.id')
                    ->where('uma.user_id', '=', $userId);
            })
            ->join('dormitory_floors as f', function ($join): void {
                $join->on('f.id', '=', 'r.floor_id')
                    ->whereNull('f.deleted_at');
            })
            ->join('dormitory_buildings as b', function ($join): void {
                $join->on('b.id', '=', 'f.building_id')
                    ->whereNull('b.deleted_at');
            })
            ->join('dormitories as d', function ($join): void {
                $join->on('d.id', '=', 'b.dormitory_id')
                    ->whereNull('d.deleted_at');
            })
            ->leftJoin('dormitory_beds as bed', function ($join): void {
                $join->on('bed.room_id', '=', 'r.id')
                    ->whereNull('bed.deleted_at');
            })
            ->whereNull('r.deleted_at')
            ->groupBy('r.id', 'r.name', 'f.label', 'b.name', 'd.name')
            ->orderBy('d.name')
            ->orderBy('b.name')
            ->orderBy('f.label')
            ->orderBy('r.name')
            ->select([
                'r.id',
                'r.name as room_label',
                'f.label as floor_label',
                'b.name as building_name',
                'd.name as dormitory_name',
                DB::raw('count(bed.id)::int as bed_total'),
                DB::raw("coalesce(sum(case when bed.physical_occupancy_state = 'occupied' then 1 else 0 end), 0)::int as bed_occupied"),
                DB::raw("coalesce(sum(case when bed.physical_occupancy_state = 'reserved' then 1 else 0 end), 0)::int as bed_reserved"),
                DB::raw("coalesce(sum(case when bed.physical_occupancy_state = 'vacant' then 1 else 0 end), 0)::int as bed_vacant"),
            ])
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $result[] = [
                'id' => (string) data_get($row, 'id'),
                'room_label' => (string) data_get($row, 'room_label'),
                'floor_label' => (string) data_get($row, 'floor_label'),
                'building_name' => (string) data_get($row, 'building_name'),
                'dormitory_name' => (string) data_get($row, 'dormitory_name'),
                'bed_total' => (int) data_get($row, 'bed_total'),
                'bed_occupied' => (int) data_get($row, 'bed_occupied'),
                'bed_reserved' => (int) data_get($row, 'bed_reserved'),
                'bed_vacant' => (int) data_get($row, 'bed_vacant'),
            ];
        }

        return $result;
    }
}
