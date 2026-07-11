<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Contracts;

use App\Modules\Dormitory\Application\DTOs\BedSummaryData;
use App\Modules\Dormitory\Application\DTOs\BuildingSummaryData;
use App\Modules\Dormitory\Application\DTOs\DormitoryDetailData;
use App\Modules\Dormitory\Application\DTOs\DormitorySummaryData;
use App\Modules\Dormitory\Application\DTOs\FloorSummaryData;
use App\Modules\Dormitory\Application\DTOs\RoomSummaryData;

/**
 * Structure read surface: dormitory/building hierarchy list and detail queries.
 */
interface DormitoryStructureReadContract
{
    /**
     * @return list<DormitorySummaryData>
     */
    public function listDormitories(): array;

    public function getDormitoryDetail(string $dormitoryId): ?DormitoryDetailData;

    /**
     * @return list<BuildingSummaryData>
     */
    public function listDormitoryBuildings(string $dormitoryId): array;

    /**
     * @return list<FloorSummaryData>
     */
    public function listBuildingFloors(string $buildingId): array;

    /**
     * @return list<RoomSummaryData>
     */
    public function listFloorRooms(string $floorId): array;

    /**
     * @return list<BedSummaryData>
     */
    public function listRoomBeds(string $roomId): array;
}
